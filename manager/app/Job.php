<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Job extends Model
{
    const STATUS_READY      = 0;
    const STATUS_DONE       = 1;
    const STATUS_PAUSED     = 2;
    const STATUS_DELETED    = 3;

    protected $fillable = [
        'name',
        'priority',
        'status',
        'client_id',
        'created_at',
        'updated_at',
    ];

    public function getDashboardResume() {
        $resume = DB::table('jobs_contacts AS jc')
            ->select('c.id', 'c.name', 'c.active', 'c.email',
                DB::raw('count(CASE WHEN jc.status = 0 THEN 1 ELSE NULL END) AS status_ready'),
                DB::raw('count(CASE WHEN (DATEDIFF(CURDATE(), Date(jc.updated_at)) < 1 and jc.status = 1) THEN 1 ELSE NULL END) AS today_status_done'),
                DB::raw('count(CASE WHEN (DATEDIFF(CURDATE(), Date(jc.updated_at)) < 1 and jc.status > 1) THEN 1 ELSE NULL END) AS today_status_fail'),
                DB::raw('count(CASE WHEN (DATEDIFF(CURDATE(), Date(jc.updated_at)) < 2 and jc.status = 1) THEN 1 ELSE NULL END) AS l24h_status_done'),
                DB::raw('count(CASE WHEN (DATEDIFF(CURDATE(), Date(jc.updated_at)) < 2 and jc.status > 1) THEN 1 ELSE NULL END) AS l24h_status_fail'),
                DB::raw('count(CASE WHEN (DATEDIFF(CURDATE(), Date(jc.updated_at)) < 8 and jc.status = 1) THEN 1 ELSE NULL END) AS l7d_status_done'),
                DB::raw('count(CASE WHEN (DATEDIFF(CURDATE(), Date(jc.updated_at)) < 8 and jc.status > 1) THEN 1 ELSE NULL END) AS l7d_status_fail'),
                'cp.cap'
            )
            ->join('jobs AS j', 'jc.job_id', '=', 'j.id')
            ->join('clients AS c', 'j.client_id', '=', 'c.id')
            ->join(DB::raw('(SELECT MAX(cap) cap, client_id from client_caps where Updated_at > CURDATE() GROUP BY client_id) AS cp'), 'c.id', '=', 'cp.client_id')
            ->where(DB::raw('DATEDIFF(CURDATE(), Date(jc.updated_at))'), '<', 8)
            ->groupBy('c.id', 'cp.cap')
            ->get();

        return $resume;
    }

    public function getJobs($StatusId = null, $PriorityId = null) {
        $jobs = DB::table('jobs')
            ->join('clients', 'clients.id', '=', 'jobs.client_id')
            ->select(
                'jobs.id', 'jobs.status', 'jobs.priority', 'jobs.name', 'clients.name as client_name', 'clients.email',
                DB::raw('(select count(*) from jobs_contacts where jobs_contacts.job_id = jobs.id) as total'),
                DB::raw('(select count(*) from jobs_contacts where jobs_contacts.job_id = jobs.id and jobs_contacts.status <> 0) as processed')
            )
            ->where('jobs.status', $StatusId);

        if ($PriorityId !== '-') {
            $jobs->where('jobs.priority', $PriorityId);
        }

        $jobs->orderBy('jobs.id');

        return $jobs->get();
    }
}
