<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class JobsContact extends Model
{
    const STATUS_READY          = 0;
    const STATUS_DONE           = 1;
    const STATUS_NO_RESPONSE    = 2;
    const STATUS_FAILED         = 4;

    protected $fillable = [
        'contact_id',
        'job_id',
        'status',
        'attempts',
        'created_at',
        'updated_at',
    ];

    public function getFirstReady($clientId) {
        $job_contact = DB::table('jobs_contacts')
        ->join('jobs', 'jobs_contacts.job_id', '=', 'jobs.id')
        ->join('contacts', 'jobs_contacts.contact_id', '=', 'contacts.id')
        ->join('clients', 'jobs.client_id', '=', 'clients.id')
        ->select('contacts.url', 'jobs_contacts.id as jobs_contacts_id')
        ->where('jobs_contacts.status', self::STATUS_READY)
        ->where('clients.id', $clientId)
        ->orderBy('jobs_contacts.id', 'asc')
        ->first();

        return $job_contact;
    }

    public function getJob($id) {
        $job_contact = DB::table('jobs_contacts')
            ->join('jobs', 'jobs_contacts.job_id', '=', 'jobs.id')
            ->join('clients', 'jobs.client_id', '=', 'clients.id')
            ->select('jobs_contacts.status as jobs_contacts_status', 'jobs_contacts.contact_id', 'jobs.id as jobs_id', 'clients.id as client_id')
            ->where('jobs_contacts.id', $id)
            ->first();

        return $job_contact;
    }

    public function getMonthlyResume() {
        $resume = DB::table('jobs_contacts')
            ->join('jobs', 'jobs_contacts.job_id', '=', 'jobs.id')
            ->select('jobs.priority', DB::raw('count(jobs.priority) as count'))
            ->whereMonth('jobs_contacts.created_at', DB::raw('MONTH(CURRENT_DATE())'))
            ->groupBy('jobs.priority')
            ->get();

        $associativeJobsContacts = [];
        foreach ($resume as $jobsContact) {
            echo '[' . $jobsContact->priority . ']: ' . $jobsContact->count . PHP_EOL;
            $associativeJobsContacts[$jobsContact->priority] = $jobsContact->count;
        }

        return $associativeJobsContacts;
    }

    function createAutoJobs($quantity, $priority, $clients, $offsetDays = 30) {
        $alertedDays = env('ALERTED_CONTACTS_BACKLOG_OFFSET');

        $total = 0;
        foreach ($clients as $client) {
            $contacts = DB::table('contacts')
                ->select('contacts.id')
                ->whereRaw('id NOT IN (
                    SELECT contact_id FROM jobs_contacts WHERE status = 0
                )')
                ->where('contacts.priority', $priority)
                ->whereRAW("(contacts.crawled_at IS NULL OR contacts.crawled_at < DATE_SUB(NOW(), INTERVAL {$offsetDays} DAY))")
                ->whereRAW("(contacts.alerted_at IS NULL OR contacts.alerted_at < DATE_SUB(NOW(), INTERVAL {$alertedDays} DAY))")
                ->whereNotNull('contacts.external_id')
                ->orderBy('contacts.crawled_at')
                ->limit($client->cap)
                ->get();

            if (count($contacts) > 0) {
                $Job = new Job;
                $Job->name      = 'Job ' . date('Y-m-d H:i:s') . ' ' . $priority;
                $Job->priority  = $priority;
                $Job->status    = Job::STATUS_READY;
                $Job->client_id = $client->id;
                $Job->save();

                echo 'client_id: ' . $client->id . PHP_EOL;
                echo 'jobs_id: ' . $Job->id . PHP_EOL;

                foreach ($contacts as $contact) {
                    $JobsContact = new JobsContact;
                    $JobsContact->contact_id    = $contact->id;
                    $JobsContact->job_id        = $Job->id;
                    $JobsContact->status        = self::STATUS_READY;
                    $JobsContact->attempts      = 0;
                    $JobsContact->save();
                }

                echo 'Total contacts: ' . count($contacts) . PHP_EOL;
            }

            $total += count($contacts);
        }

        return $total;
    }
}
