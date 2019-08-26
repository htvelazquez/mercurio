<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Contact;
use App\Snapshot;
use App\SnapshotExperience;
use App\Alert;
use App\Jobs\ProcessAlert;
use App\Services\SalesforceService;

class ProcessSnapshot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contact;
    protected $log = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (empty($this->contact->salesforce_id))
            return false;

        try {
            $lastProcessedExperiences = Snapshot::with('current_experience')->where([
                'contact_id' => $this->contact->id,
                'status' => Snapshot::STATUS_PROCESSED,
                ])
                ->orderBy('id', 'desc')
                ->first();

            $lastUnprocessedExperiences = Snapshot::with('current_experience')->where([
                'contact_id' => $this->contact->id,
                'status' => Snapshot::STATUS_NEW,
                ])
                ->orderBy('id', 'desc')
                ->first();

            $newAlert = false;

            if (!empty($lastProcessedExperiences) && !empty($lastUnprocessedExperiences)) {
                foreach($lastProcessedExperiences->current_experience as $beforeExperience) {
                    $change = true;
                    foreach($lastUnprocessedExperiences->current_experience as $afterExperience) {
                        if (
                            $beforeExperience->jobTitle === $afterExperience->jobTitle &&
                            $beforeExperience->company_id === $afterExperience->company_id &&
                            ($beforeExperience->to === null or $afterExperience->to === null)
                        ) {
                            $change = false;
                        }
                    }

                    if ($change) {
                        SnapshotExperience::find($beforeExperience->id)->update(['before_diff' => 1]);
                        $newAlert = true;
                    }
                }

                foreach($lastUnprocessedExperiences->current_experience as $afterExperience) {
                    $change = true;
                    foreach($lastProcessedExperiences->current_experience as $beforeExperience) {
                        if (
                            $beforeExperience->jobTitle === $afterExperience->jobTitle &&
                            $beforeExperience->company_id === $afterExperience->company_id &&
                            ($beforeExperience->to === null or $afterExperience->to === null)
                        ) {
                            $change = false;
                        }
                    }

                    if ($change) {
                        SnapshotExperience::find($afterExperience->id)->update(['after_diff' => 1]);
                        $newAlert = true;
                    }
                }
            }elseif (empty($lastProcessedExperiences)) {
                $newAlert = true;
            }

            Snapshot::where([
                    'status' => Snapshot::STATUS_NEW,
                    'contact_id' => $this->contact->id
                ])->update(['status' => Snapshot::STATUS_PROCESSED]);

            if ($newAlert) {
                $alertData = [
                    "before_snapshot_id"    => (!empty($lastProcessedExperiences))? $lastProcessedExperiences->id : NULL,
                    "after_snapshot_id"     => $lastUnprocessedExperiences->id,
                    "status"                => 0
                ];

                $oldAlerts = Alert::whereIn('before_snapshot_id', function($query){
                    $query->select('id')
                    ->from('snapshots')
                    ->where('contact_id', $this->contact->id);
                })->update(['status' => 1]);

                if (!empty($this->contact->salesforce_id)){
                    $sfdcContact = new \stdclass();
                    $sfdcContact->Grabber_detected__c = 1;
                    $sfdcContact->Last_alert_Date__c = gmdate("Y-m-d\TH:i:s\Z",date(time()));
                    SalesforceService::getInstance()->pushContact($sfdcContact, $this->contact->salesforce_id);
                }

                $alert = Alert::create($alertData);
                ProcessAlert::dispatch($alert);
            }
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
