<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Illuminate\Validation\Rule;
use App\Job;
use App\JobsContact;
use App\Contact;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function ping() {
        $sql = "SELECT
            (
                SELECT count(*)
                FROM jobs_contacts
                WHERE DATEDIFF(CURDATE(), Date(updated_at)) < 1
                AND status >= 1
            ) as 'profilesToday',
            (
                SELECT count(*)
                FROM jobs_contacts
                WHERE DATEDIFF(CURDATE(), Date(updated_at)) < 2
                AND status >= 1
            ) as 'profilesLast24H',
            (
                SELECT count(*)
                FROM jobs_contacts
                WHERE DATEDIFF(CURDATE(), Date(updated_at)) < 8
                AND status >= 1
            ) as 'profilesLast7D';";

        $data = DB::select($sql);

        $responseData = [
            'profilesToday' => $data[0]->profilesToday,
            'profilesLast24H' => $data[0]->profilesLast24H,
            'profilesLast7Days' => $data[0]->profilesLast7D
        ];

        return response()->json([
            'success' => true,
            'data' => $responseData
        ], 200);
    }

    public function dashboard() {
        $Job = new Job();
        return response()->json(['success' => true, 'clients' => $Job->getDashboardResume()], 200);
    }

    public function get($StatusId, $PriorityId) {
        $Job = new Job();
        return response()->json(['success' => true, 'jobs' => $Job->getJobs($StatusId, $PriorityId)], 200);
    }

    public function post(Request $request) {
        $validator = Validator::make($request->all(), [
            'job_client' => [
                'required',
                'exists:clients,id'
            ],
            'job_priority' => ['required'],
            'job_name' => ['required'],
            'job_contacts' => ['required']
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }
    
        $Job = new Job;
        $Job->client_id = $request->job_client;
        $Job->priority = $request->job_priority;
        $Job->name = $request->job_name;
        $Job->status = $Job::STATUS_READY;
        $Job->save();
    
        $contacts = [];
        
        if (!empty($request->job_contacts)) {
            foreach ($request->job_contacts as $contact) {
                $contactExist = Contact::where('url', 'like', '%' . $contact . '%')->first();
                $contactId = null;
                
                if (!empty($contactExist)) {
                    $contactId = $contactExist->id;
                    $contactData = $contactExist;
                } else {
                    $Contact = Contact::create([
                        'priority' => $Job->priority,
                        'url' => $contact
                    ]);
    
                    $contactId = $Contact->id;
                    $contactData = $Contact;
                }
    
                $JobsContact = JobsContact::create([
                    'contact_id' => $contactId,
                    'job_id' => $Job->id,
                    'status' => 0,
                    'attempts' => 0,
                ]);
    
                $contacts[] = $contactData;
            }
        }
    
        return response()->json(['success' => true, 'job' => $Job, 'contacts' => $contacts]);
    }
}
