<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Formatter\LineFormatter;
use App\Services\ServiceInventoryService;
use App\Services\SalesforceService;
use App\Contact;
use App\Company;
use App\Snapshot;
use App\SnapshotExperience;
use App\Alert;

class ProcessAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const CASE_SINGLE_TO_SINGLE_IN_SFDC     = 1;
    const CASE_NO_LONGER_WITH_COMPANY       = 2;
    const CASE_JOB_TITLE_CHANGE             = 3;
    const CASE_SINGLE_TO_SINGLE_NOT_IN_SFDC = 4;
    const CASE_MULTIPLE_NEW_COMPANIES       = 5;
    const CASE_COMPANY_WITH_NO_SPEND        = 6;
    const CASE_NLWC_TO_SINGLE_NOT_IN_SFDC   = 7;
    const CASE_NLWC_TO_SINGLE_IN_SFDC       = 8;
    const CASE_MULTI_TO_SINGLE_NOT_IN_SFDC  = 9;
    const CASE_MULTI_TO_SINGLE_IN_SFDC      = 10;
    const CASE_NOT_DETECTED                 = 11;
    const CASE_WORKS_AT_QM_CLIENT           = 12;
    const CASE_COMPANY_NOT_IDENTIFIED       = 13;

    protected $log = false;

    protected $newPositionData;

    protected $alert;

    protected $_sf_nlwc_account;

    protected $_sf_cwns_account;

    protected $_gracePeriodToCreateTask;

    protected $_minimumSpendRequeired;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Alert $alert)
    {
        $this->_sf_nlwc_account = env('ALERTS_NLWC_SAFEID');
        $this->_sf_cwns_account = env('ALERTS_CWNS_SAFEID');

        $this->_gracePeriodToCreateTask = env('ALERTS_TASKS_GRACE_PERIOD');
        $this->_minimumSpendRequeired    = env('ALERTS_MINIMUM_SPEND');
        $this->newPositionData = NULL;

        $this->alert = $alert;

        $this->log = new Logger('AlertsSolver');
        $formatter = new LineFormatter(null, null, false, true);
        $streamHandler = new StreamHandler(storage_path() . '/logs/alerts_solver.log', Logger::DEBUG);
        $streamHandler->setFormatter($formatter);
        $this->log->pushHandler($streamHandler);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->log->debug('---------> '.$this->alert->id.' : BEGIN <---------');

        if ($this->alert->status == Alert::STATUS_DONE){
            $this->log->debug('Alert already done');
            $this->log->debug('---------> '.$this->alert->id.' : END <---------');
            $this->alert->checked_by_solver = 1;
            $this->alert->save();
            return true;
        }

        if (empty($this->alert->snapshot->contact->salesforce_id)){
            $contact = false;
            // $contact = ServiceInventoryService::getInstance()->getContactFromLinkedInID($this->alert->snapshot->contact->external_id);
            //
            // if (!empty($contact)){
            //     $this->alert->snapshot->contact->salesforce_id = $contact->SafeId;
            //     $this->alert->snapshot->contact->save();
            // }
        }else{
            $contact = ServiceInventoryService::getInstance()->getContactFromSafeID($this->alert->snapshot->contact->salesforce_id);
        }

        if (empty($contact)){
            $this->log->debug('Contact not found');
            $this->log->debug('---------> '.$this->alert->id.' : END <---------');
            $this->alert->checked_by_solver = 1;
            $this->alert->save();
            return true;
        }

        $beforeExperience = $this->alert->before_experience()->where('to', null)->get();
        $afterExperience = $this->alert->after_experience()->where('to', null)->get();

        $case = $this->detectCase($beforeExperience, $afterExperience, $contact);
        switch ($case) {
            case self::CASE_SINGLE_TO_SINGLE_IN_SFDC:
            case self::CASE_NLWC_TO_SINGLE_IN_SFDC:
            case self::CASE_MULTI_TO_SINGLE_IN_SFDC:

                $profile = $this->getSFContactObjetc( [ 'title' => $this->newPositionData['title'], 'account' => $this->newPositionData['account'], 'owner' => $this->newPositionData['owner'], 'new_company_date' => $this->newPositionData['from'], 'new_company_lid' => $this->newPositionData['company_id'] ] );

                SalesforceService::getInstance()->pushContact($profile, $contact->SafeId);
                $this->log->debug('Push contact: ', (array) $profile);
                $this->alert->status = Alert::STATUS_DONE;

                if ( $this->involvesTaskCreation() ){
                    SalesforceService::getInstance()->generateNewCompanyTask( $this->newPositionData['owner'], $contact->SafeId, $this->newPositionData['account'] );
                    $this->log->debug('New company task created');
                }

                break;

            case self::CASE_JOB_TITLE_CHANGE:

                $profile = $this->getSFContactObjetc( [ 'title' => $this->newPositionData['title'], 'new_position_date' => $this->newPositionData['from'], 'new_company_lid' => $this->newPositionData['company_id'] ] );

                SalesforceService::getInstance()->pushContact($profile, $contact->SafeId);
                $this->log->debug('Push contact: ', (array) $profile);
                $this->alert->status = Alert::STATUS_DONE;

                if ( $this->involvesTaskCreation() ){
                    SalesforceService::getInstance()->generateNewPositionTaks( $this->newPositionData['owner'], $contact->SafeId, $this->newPositionData['account'] );
                    $this->log->debug('New position task created');
                }

                break;

            case self::CASE_NO_LONGER_WITH_COMPANY:
                $profile = $this->getSFContactObjetc( [ 'account' => $this->_sf_nlwc_account, 'new_company_date' => $this->newPositionData['from'] ] );

                SalesforceService::getInstance()->pushContact($profile, $contact->SafeId);
                $this->log->debug('Push contact: ', (array) $profile);
                $this->alert->status = Alert::STATUS_DONE;

                break;

            case self::CASE_SINGLE_TO_SINGLE_NOT_IN_SFDC:
            case self::CASE_NLWC_TO_SINGLE_NOT_IN_SFDC:
            case self::CASE_MULTI_TO_SINGLE_NOT_IN_SFDC:
                $profile = $this->getSFContactObjetc(  [ 'account' => $this->_sf_cwns_account, 'new_company_date' => $this->newPositionData['from'], 'new_company_lid' => $this->newPositionData['company_id'] ] );

                SalesforceService::getInstance()->pushContact($profile, $contact->SafeId);
                $this->log->debug('Push contact: ', (array) $profile);
                $this->alert->status = Alert::STATUS_DONE;

                break;

            case self::CASE_WORKS_AT_QM_CLIENT:
                if ( $contact->Account_stage__c == 'Active client' ){
                    $this->alert->snapshot->contact->has_worked_at_client = TRUE;
                    $this->alert->snapshot->contact->save();
                }

                $this->log->debug('CASE: CONTACT WORKS AT QM COSTUMER');
                break;

            case self::CASE_COMPANY_NOT_IDENTIFIED:
                $this->log->debug('CASE: COMPANY NOT IDENTIFIED');
                break;

            default:
                $this->log->debug("NO CASE DETECTED -> $case ");
                break;
        }

        $this->alert->case = $case;
        $this->alert->checked_by_solver = 1;
        $this->alert->save();
        $this->log->debug('---------> '.$this->alert->id.' : END <---------');
        return true;
    }

    protected function detectCase($beforeExperience, $afterExperience, $contact)
    {
        if (!empty($contact->Account_stage__c) && in_array($contact->Account_stage__c,['Active opportunity','Meeting Booked','Active client'])){
            return self::CASE_WORKS_AT_QM_CLIENT;
        }

        // <::====== TO NO LONGER WITH COMPANY ======::> //
        if (count($afterExperience) == 0){
            return self::CASE_NO_LONGER_WITH_COMPANY;
        }

        $afterExperience = $this->findUnknownAccountsByName($afterExperience);
        // buscar hacer match por nombre/dominio de todas las empresas y volver a preguntar hasNotIdentifiedCompanies()

        if ($this->hasNotIdentifiedCompanies($afterExperience)){
            return self::CASE_COMPANY_NOT_IDENTIFIED;
        }





        // new case: all the new companies are the same
        // company consolidation, if every current position regards to the same company we'll take the last title
        $afterExperience = $this->consolidateExperience($afterExperience);

        // <::====== TO SINGLE COMPANY ======::> //
        if (count($afterExperience) == 1) {
            $afterAccount = $this->getAccountSafeIdFromCompany($afterExperience->first()->company, true);
            if (!empty($afterAccount)) $this->newPositionData['account'] = $afterAccount;

            $this->newPositionData['title'] = $afterExperience->first()->jobTitle;
            $this->newPositionData['from'] = (!empty($afterExperience->first()->from))? $afterExperience->first()->from : NULL;
            $this->newPositionData['company_id'] = (!empty($afterExperience->first()->company->linkedin_id))? $afterExperience->first()->company->linkedin_id : NULL;

            if ($contact->Account__c == $afterAccount)
                return self::CASE_JOB_TITLE_CHANGE;

            // <::====== FROM NO LONGER WITH COMPANY ======::> //
            if (count($beforeExperience) == 0){
                if (empty($afterAccount))
                    return self::CASE_NLWC_TO_SINGLE_NOT_IN_SFDC;

                return self::CASE_NLWC_TO_SINGLE_IN_SFDC;
            }

            // <::====== FROM SINGLE COMPANY ======::> //
            if (count($beforeExperience) == 1){
                // esto se comenta porque trae problemas con contactos que no fueron actualizados anteriormente en SFDC (saltean dos snapshots)

                // if ($afterExperience->first()->company_id == $beforeExperience->first()->company_id ||
                //     $afterExperience->first()->company->linkedin_id == $beforeExperience->first()->company->linkedin_id){
                //     return self::CASE_JOB_TITLE_CHANGE;
                // }
                //
                // $beforeAccount = $this->getAccountSafeIdFromCompany($beforeExperience->first()->company);
                // if (!empty($beforeAccount) && $afterAccount == $beforeAccount) {
                //     return self::CASE_JOB_TITLE_CHANGE;
                // }

                if (empty($afterAccount))
                    return self::CASE_SINGLE_TO_SINGLE_NOT_IN_SFDC;

                return self::CASE_SINGLE_TO_SINGLE_IN_SFDC;
            }

            if (empty($afterAccount))
                return self::CASE_MULTI_TO_SINGLE_NOT_IN_SFDC;

            return self::CASE_MULTI_TO_SINGLE_IN_SFDC;
        }

        $afterExperienceOnlyInSFDCAcc = $this->consolidateExperience($afterExperience, true);

        // <::====== TO SINGLE COMPANY ======::> //
        if (!empty($afterExperienceOnlyInSFDCAcc) && count($afterExperienceOnlyInSFDCAcc) == 1) {

            $afterAccount = $this->getAccountSafeIdFromCompany($afterExperienceOnlyInSFDCAcc->first()->company, true);
            if (!empty($afterAccount)) $this->newPositionData['account'] = $afterAccount;

            $this->newPositionData['title'] = $afterExperienceOnlyInSFDCAcc->first()->jobTitle;
            $this->newPositionData['from'] = (!empty($afterExperienceOnlyInSFDCAcc->first()->from))? $afterExperienceOnlyInSFDCAcc->first()->from : NULL;
            $this->newPositionData['company_id'] = (!empty($afterExperienceOnlyInSFDCAcc->first()->company->linkedin_id))? $afterExperienceOnlyInSFDCAcc->first()->company->linkedin_id : NULL;

            if ($contact->Account__c == $afterAccount)
                return self::CASE_JOB_TITLE_CHANGE;

            // <::====== FROM NO LONGER WITH COMPANY ======::> //
            if (count($beforeExperience) == 0)
                return self::CASE_NLWC_TO_SINGLE_IN_SFDC;

            // <::====== FROM SINGLE COMPANY ======::> //
            if (count($beforeExperience) == 1)
                return self::CASE_SINGLE_TO_SINGLE_IN_SFDC;

            return self::CASE_MULTI_TO_SINGLE_IN_SFDC;

        }elseif(!empty($afterExperienceOnlyInSFDCAcc) && count($afterExperienceOnlyInSFDCAcc) == 0) {
            return self::CASE_MULTI_TO_SINGLE_NOT_IN_SFDC; // en este caso no hay ninguna de las companias en SFDC, pero todas estan identificadas en LinkedIn
        }

        return self::CASE_MULTIPLE_NEW_COMPANIES;
    }

    protected function consolidateExperience( $experience, $excludeNotInSFDCAccounts = false )
    {
        if (count($experience) == 1) return $experience;

        $lastExp = NULL;
        foreach ($experience as $k => $position) {
            if (!empty($position->company->linkedin_id) ){
                if ($lastExp != NULL && !empty($position->company->linkedin_id) && $position->company->linkedin_id == $lastExp){
                    unset($experience[$k]); // remove
                }elseif($excludeNotInSFDCAccounts == true && empty($position->company->safeId)){
                    $safeId = $this->getAccountSafeIdFromCompany($position->company);
                    if (empty($safeId)) unset($experience[$k]); // remove
                }else{
                    $lastExp = $position->company->linkedin_id;
                }
            }
        }

        return $experience;
    }

    protected function hasNotIdentifiedCompanies( $experience )
    {
        $hasUnknownCompany = false;
        foreach ($experience as $k => $position) {
            if (empty($position->company->linkedin_id) || $position->company->linkedin_id == NULL )
                $hasUnknownCompany = true;
        }
        return $hasUnknownCompany;
    }

    protected function getAccountSafeIdFromCompany( $company, $newAccount = false )
    {
        if (!empty($company->safeId) && !empty($company->ownerId) && empty($newAccount)) return $company->safeId;

        if (empty($company->linkedin_id))
            return false;

        $companyPage = ServiceInventoryService::getInstance()->getLinkedInCompanyPage( $company->linkedin_id );

        if (empty($companyPage))
            return false;

        $company->safeId = $companyPage->Account__c;
        $company->ownerId = $companyPage->AccountOwner;
        $company->save();

        if ($newAccount == true){

            $account = ServiceInventoryService::getInstance()->getAccount( $companyPage->Account__c );

            if (!empty($account->Owner))
                $this->newPositionData['owner'] = $account->Owner;

            if (!empty($account->Monthly_SEM_Spend_formula__c))
                $this->newPositionData['domain_spend'] = $account->Monthly_SEM_Spend_formula__c;

            if (!empty($account->In_Patch__c))
                $this->newPositionData['account_in_patch'] = $account->In_Patch__c;
        }

        return $companyPage->Account__c;
    }

    protected function findUnknownAccountsByName($experience)
    {
        foreach ($experience as $k => $position) {
            if (empty($position->company->linkedin_id) ){


                $company = Company::where('name', '=', $position->company->name)->whereNotNull('linkedin_id')->first();
                if (!empty($company)){
                    echo $experience[$k]->company_id.' = '.$company->id.PHP_EOL; die;
                    // SnapshotExperience::where('id', 1)->update(['company_id' => $company->id]);
                }
                die(' <-------- not found!');

                // ServiceInventoryService::getInstance()->normalizeString()
                die;
            }
        }
        die('no encontro company duplicada');
        return $experience;
    }

    protected function getCompanyIds($positions)
    {
        $ids = array();
        foreach ($positions as $position) {
            $ids[] = $position->company->linkedin_id;
        }
        return $ids;
    }

    protected function getSFContactObjetc( $data )
    {
        $contact = new \stdclass();

        if (!empty($data['title']))
            $contact->Title = $data['title'];

        if (!empty($data['account']))
            $contact->AccountId = $data['account'];

        if (!empty($data['owner']))
            $contact->OwnerId = $data['owner'];

        if (!empty($data['new_company_date'])){
            $contact->Current_Position_Start_Date__c = gmdate("Y-m-d\TH:i:s\Z",strtotime($data['new_company_date']));
            $contact->Current_Company_Start_Date__c = gmdate("Y-m-d\TH:i:s\Z",strtotime($data['new_company_date']));
        }

        if (!empty($data['new_company_lid']))
            $contact->LinkedIn_Company_ID_on_profile__c = $data['new_company_lid'];

        if (!empty($data['new_position_date']))
            $contact->Current_Position_Start_Date__c = gmdate("Y-m-d\TH:i:s\Z",strtotime($data['new_position_date']));

        $contact->Grabber_detected__c = 0;
        $contact->Last_Grabber_update__c = gmdate("Y-m-d\TH:i:s\Z",date(time()));

        return $contact;
    }

    protected function getSFTaskObjetc( $contactSafeId, $accountSafeId )
    {
        $account = ServiceManager::SalesforceCompaniesService()->findByAccountId($accountSafeId);

        $newTask = new stdClass();
        $newTask->Description   = 'This contact’s LinkedIn page is showing a new job. Could be a great time to reach out to congratulate and start a conversation. \n After reaching out, or evaluating the situation, mark this task to Status = Completed';
        $newTask->OwnerId       = $account['accountOwner'];
        $newTask->Priority      = 'Normal';
        $newTask->Status        = 'Not Started';
        $newTask->Subject       = 'Contact switched jobs';
        $newTask->Type          = 'Internal Reminder';
        $newTask->WhoID         = $contactSafeId;
        $newTask->WhatID        = $accountSafeId;
        $newTask->ActivityDate  = gmdate("Y-m-d H:i:s", time() + (7 * 24 * 60 * 60));
        return $newTask;
    }

    protected function involvesTaskCreation( )
    {
        return false;
        // $jobTitleRelevant = $this->isJobTitleRelevant( $this->newPositionData['title'] );
        //
        // if ($jobTitleRelevant == false)
        //     return false;
        //
        // if (empty($this->newPositionData['from']) || empty($this->newPositionData['account']) || empty($this->newPositionData['owner']))
        //     return false;
        //
        // $plussixmonths = strtotime( $this->_gracePeriodToCreateTask, strtotime($this->newPositionData['from']));
        // $now = new \DateTime();
        // if ($plussixmonths < $now->getTimestamp()) // checking the job change date
        //     return false;
        //
        // if (!empty($this->newPositionData['account_in_patch']) && $this->newPositionData['account_in_patch'] === true)
        //     return true;
        //
        // if (!empty($this->newPositionData['domain_spend']) && $this->newPositionData['domain_spend'] <= $this->_minimumSpendRequeired)
        //     return true;
        //
        // // if () // PLA_Keywords__c > 10000 nuevo criterio
        //     // return true;
        //
        // return false;
    }

    protected function getJobTitleRelevance( $jobTitle )
    {
        $keywordLevel = $this->getTitleKeywordLevelByTitle($jobTitle);
        $irrelevantKeywordLevel = $this->getIrrelevantKeywordLevelByTitle($jobTitle);
        $seniorityLevel = $this->getSeniorityByTitle($jobTitle);

        if ($keywordLevel == 1 && $irrelevantKeywordLevel == 1.6){
            $result = floor( ($seniorityLevel + $keywordLevel)/2);
        }else{
            $result = ceil(floor(($seniorityLevel + $keywordLevel)/2)*$irrelevantKeywordLevel);
        }
        return ($result>5 || $result <1)? 5 : $result;
    }

    private function getIrrelevantKeywordLevelByTitle ( $jobTitle )
    {
        if ( preg_match('/.*(\bdean|adquisicion\sde\sdatos|aprendiz|architect|arquitecto|asistente del|assistant\sto the|athlete|atleta|business\sowner|candidat|catedratico|conseiller|consult|data\sacquisition|decano|doyen|educacion|educatif|education|educativo|engineer|entrenador|estudiante|etudiant|facult|franchise\sowner|freelance|free\-lance|ingeniero|instructeur|instructor|logiciel|mentor|pa\sto|pasante|president\'s|profesor|professeur|professor|recruit|recrut|research|scholar|student|teacher|trainee|trainer|tutor|\bintern\b).*/i',$jobTitle) ){
    		return 0;
    	}elseif( preg_match('/^((?!(\band\b|\s\&\s))[\s\S])*$/i',$jobTitle) && preg_match('/.*(brand|trade|retention|event|field|content|partner\smarketing|email|affiliate|contenidos|socios|advisor).*/i',$jobTitle) ){
    		return 1.6;
    	}else{
    		return 1;
    	}
    }

    private function getSeniorityByTitle( $jobTitle )
    {
        if ( preg_match('/.*(board\smember|cdo|ceo|cmo|chief|chef|founder|fundador|fondateur|fondatrice|geschaeftsfuehrer|geschaeftsleiter|jefe|managing\spartner|owner|pdg|p\-dg|president|vp).*/i',$jobTitle) ){
    		return 1;
    	}elseif (preg_match('/.*(directeur|directrice|director|direktor|head|officer).*/i',$jobTitle) ){
    		return 2;
    	}elseif (preg_match('/.*(chargee|gerant|gerente|lead|leiter|manager).*/i',$jobTitle) ){
    		return 3;
    	}elseif (preg_match('/.*(coordinator|coordinador|coordenador|coordinateur|coordinatrice|koordinator|executive|responsable|supervisor).*/i',$jobTitle) ){
    		return 4;
    	}else{
    		return 5;
    	}
    }

    private function getTitleKeywordLevelByTitle( $jobTitle )
    {
        if ( preg_match('/.*(cmo|(chief.*marketing|marketing.*chief)|(VP.*marketing|marketing.*VP)|(president.*marketing|marketing.*president)).*/i',$jobTitle) || preg_match('/.*(adword|biddable\smarketing|ppc|sem|doubleclick|google\splas|google\sshopping|kenshoo|pay\sper\sclick|product\slisting\sads|search\sads|sem|shopping\sads|shopping\splas|search\smarketing|search\sengine\smarketing|paid\ssearch|search\sengine\sadvertising).*/i',$jobTitle) ){
    		return 1;
    	}elseif ( preg_match('/.*(campagnes\sdigitals|campañas\sdigitales|campanhas\sdigitais|canais\sdigitais|demand\sgen|digital\scampaign|digitale\skampagnen|(digital.*marketing|marketing.*digital)|digitales\smarketing|e\scommerce|ecommerce|e\-commerce|online\smarketing|internet\smarketing|shopping|inovação\sdigital|marketing\sautomation|marketing\sonline|online\smarketing|online\sacquisition|online\sadvertising|onlinemarketing|paid\sadvertising|paid\schannel\smarketing|paid\smedia|performance\smarketing|transformação\sdigital|user\sacquisition|search|campaña\sdigital|campanha\sdigital|publicidad\sdigital).*/i',$jobTitle) ){
    		return 2;
    	}elseif ( preg_match('/.*(digital|advertising|marketing|biddable\smedia|growth|publicite).*/i',$jobTitle) ){
    		return 3;
    	}else{
    		return 5;
    	}
    }

    protected function isJobTitleRelevant( $jobTitle )
    {
        $relevance = $this->getJobTitleRelevance( $jobTitle );
        return ($relevance < 3);
    }
}
