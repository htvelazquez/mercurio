<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;
use Illuminate\Support\Facades\DB;

use App\Client;
use App\Contact;
use App\JobsContact;
use App\Job;
use App\ClientCap;

class ExtensionController extends Controller
{
    public function auth(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'exists:clients,email',
                Rule::exists('clients')->where(function ($query) {
                    $query->where('active', 1);
                }),
            ]
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
    
        $Client = new Client();
        $token = $Client->makeLogin($request->email);
    
        return response()->json(['success' => true, 'token' => $token]);
    }
    
    public function get(Request $request) {
        $bearer = $request->bearerToken();
    
        if (empty($bearer)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        $Client = new Client();
        $clientId = $Client->checkToken($bearer);
    
        if (empty($clientId)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        $ClientCap = new ClientCap();
        $clientCap = $ClientCap->where('client_id', $clientId)->orderBy('created_at', 'desc')->first();
        
        if (empty($clientCap)) {
            return response()->json(['error' => "Invalid daily cap"], 401);
        }
    
        // check cap
        $check_cap = DB::table('jobs_contacts')
        ->join('jobs', 'jobs_contacts.job_id', '=', 'jobs.id')
        ->select(DB::raw('count(*) as cant'))
        ->whereRaw('DATEDIFF(CURDATE(), Date(jobs_contacts.updated_at)) < 1')
        ->whereIn('jobs_contacts.status', [1, 4])
        ->where('jobs.client_id', '=', $clientId)
        ->first();
    
        $ClientCurrent = $Client->find($clientId);
        if ($ClientCurrent->active == 0) {
            return response()->json(['success' => false, 'error' => 'client inactive'], 200);
        }
    
        if ($check_cap->cant >= $clientCap->cap) {
            return response()->json(['success' => false, 'error' => 'cap reached'], 200);
        }
    
        $JobsContact = new JobsContact();
        return response()->json(['success' => true, 'jobs' => [$JobsContact->getFirstReady($clientId)]], 200);
    }

    public function post($JobsContactsId, Request $request) {
        // @To-DO Extract bearer check token
        $bearer = $request->bearerToken();
    
        if (empty($bearer)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        $Client = new Client();
        $clientId = $Client->checkToken($bearer);
    
        if (empty($clientId)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        $JobsContact = new JobsContact();
        $job = $JobsContact->getJob($JobsContactsId);
    
        if (!empty($job) && $job->client_id != $clientId) {
            return response()->json(['error' => 'Unauthorized Client'], 401);
        }
    
        $data = $request->all();

        $Contact = new Contact();
        $contactData = $Contact->find($job->contact_id);
        $Contact->where('id', $job->contact_id)->update(['crawled_at' => DB::raw('now()')]);

        if ($data['status'] === 'done' && !empty($data['result'])) {
            $JobsContact = new JobsContact();
            $JobsContact->where('id', $JobsContactsId)->update(['status' => $JobsContact::STATUS_DONE]);
    
            $sendJob = \App\Services\ManagerService::getInstance()->sendJob($contactData->external_id, $contactData->priority, $data['result']);
            
            $response = ['success' => true, 'result' => $data['result'], 'status' => $data['status'], 'sendJob' => $sendJob];
        } elseif ($data['status'] === 'no-response') {
            $JobsContactDB = new JobsContact();
            $JobsContactDB->where('id', $JobsContactsId)->increment('attempts');
            $JobsContactAttempts = new JobsContact();
            $JCAttempts = $JobsContactAttempts->where('id', $JobsContactsId)->first();
    
            // ATTEMPTS_LIMIT
            $attemptsLimit = env('ATTEMPTS_LIMIT');
    
            if ($JCAttempts->attempts > $attemptsLimit) {
                $JobsContactUpdate = new JobsContact();
                $JobsContactUpdate->where('id', $JobsContactsId)->update(['status' => $JobsContactUpdate::STATUS_FAILED]);
            }
    
            $response = ['success' => false, 'status' => $data['status']];
        } else {
            $JobsContact = new JobsContact();
            $JobsContact->where('id', $JobsContactsId)->update(['status' => $JobsContact::STATUS_FAILED]);
            $response = ['success' => false, 'data' => $data];
        }
    
        $jobsContactsReady = DB::table('jobs_contacts')->where('status', $JobsContact::STATUS_READY)->where('job_id', $job->jobs_id)->count();
    
        if (empty($jobsContactsReady)) {
            $JobToDone = new Job();
            $JobToDone->where('id', $job->jobs_id)->update(['status' => $JobToDone::STATUS_DONE]);
        }
    
        return response()->json($response, 200);
    }
}
