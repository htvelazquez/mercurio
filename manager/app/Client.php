<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Firebase\JWT\JWT;
use Firebase\JWT\JWT\UnexpectedValueException;
use Illuminate\Support\Facades\DB;

class Client extends Model
{
    protected $fillable = [
        'email', 
        'active', 
        'created_at', 
        'updated_at'
    ];

    protected $tokenKey = "odHRwOi8vZ";
    protected $tokenExpireTime = 86400; // One day

    public function makeLogin($email) {
        $Client = $this->where('email', $email)->where('active', 1);

        return $this->generateToken($Client->first()->id);
    }

    public function generateToken($id) {
        $data = [
            "client_id" =>  $id,
            "timestamp" => time()
        ];

        return JWT::encode($data, $this->tokenKey);
    }

    public function checkToken($token) {
        try {
            $payload = JWT::decode($token, $this->tokenKey, ['HS256']);

            if ((time() - $payload->timestamp) > $this->tokenExpireTime) {
                return;
            }

            return $payload->client_id;
        } catch(\Exception $e) {
            return;
        }
    }

    public function getAutoJobsCandidates() {
        $limit = env('AUTO_JOBS_REFILL_LIMIT');

        $clients = DB::table('clients')
            ->select(
                'clients.id',
                'clients.cap',
                DB::raw('(select count(jobs_contacts.id) as count from jobs_contacts left join jobs on (jobs_contacts.job_id = jobs.id) where jobs.client_id = clients.id and jobs_contacts.status = 0) as cant')
            )
            ->where('active', true)
            ->where('allow_automatic_backlog', true)
            ->whereRaw('((select count(jobs_contacts.id) from jobs_contacts left join jobs on (jobs_contacts.job_id = jobs.id) where jobs.client_id = clients.id and jobs_contacts.status = 0)  < ?)', [$limit])
            ->orderBy('clients.id')
            ->get();

        return $clients;
    }
}
