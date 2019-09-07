<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Job;
use App\Process;

class SendReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send jobs report by Email';

    protected $to = '';
    protected $cc = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->to = env('DAILY_TO');
        $this->cc = explode(',', env('DAILY_CC'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Process::create([
            'name' => $this->signature,
            'status' => Process::STATUS_RUNNING,
            'detail' => '',
        ]);

        $Job = new Job();
        $report['data'] = $Job->getDashboardResume();
        $report['title'] = 'Report: ' . date('Y-m-d H:i:s');
        
        Mail::to($this->to)
            ->cc($this->cc)
            ->send(new \App\Mail\Report($report));

        Process::create([
            'name' => $this->signature,
            'status' => Process::STATUS_DONE,
            'detail' => '',
        ]);
    }
}
