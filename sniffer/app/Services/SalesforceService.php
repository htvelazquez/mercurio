<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\Request;
use \Illuminate\Support\Facades\DB;
use \Omniphx\Forrest\Providers\Laravel\Facades\Forrest as Forrest;

class SalesforceService extends Service
{
    protected $_ApiKey = null;
    protected $_ApiURL = null;

    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @return SalesforceService
     */
    public static function getInstance()
    {
        return parent::build(self::class);
    }

    public function pushContact( $contact, $safeId)
    {
        echo $safeId.' :: '; var_dump($contact); echo PHP_EOL; return;
        // Forrest::authenticate();
        //
        // $result = Forrest::sobjects('Contact/'.$safeId , [
        //     'method'  => 'patch',
        //     'body'    => $contact
        // ]);
    }

    protected function pushTask( $task )
    {
        var_dump($task); return;
        // Forrest::authenticate();
        //
        // $result = Forrest::sobjects('Task' , [
        //     'method'  => 'post',
        //     'body'    => $task
        // ]);
    }

    public function generateNewCompanyTask( $owner, $contact, $account )
    {
        return $this->pushTask(
            $this->generateTask('Contact Switched Jobs',
                'This contacts LinkedIn profile shows a new job. It could be a great time to reach out to congratulate them and start a conversation. After reaching out, or evaluating the situation, change this task status to completed or will not be done',
                $owner, $contact, $account
            )
        );
    }

    public function generateNewPositionTaks( $owner, $contact, $account )
    {
        return $this->pushTask(
            $this->generateTask('Contact Has a New Position',
                'This contacts LinkedIn profile shows a new position. This could be a promotion, and a great time to reach out to congratulate them and start a conversation. After reaching out, or evaluating the situation, change this task status to completed or will not be done',
                $owner, $contact, $account
            )
        );
    }

    private function generateTask( $subject, $description, $owner, $contact, $account )
    {
        $newTask = new \stdClass();
        $newTask->Subject       = $subject;
        $newTask->Description   = $description;
        $newTask->OwnerId       = $owner;
        $newTask->Priority      = 'Normal';
        $newTask->Status        = 'Not Started';
        $newTask->Type          = 'Internal Reminder';
        $newTask->WhoID         = $contact;
        $newTask->WhatID        = $account;
        $newTask->ActivityDate  = gmdate("Y-m-d", time() + (7 * 24 * 60 * 60));
        return $newTask;
    }


}
