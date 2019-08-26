<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Formatter\LineFormatter;
use App\JobsContact;
use App\BacklogDistribution;
use App\Client;
use App\Process;

class GenerateJobs extends Command
{
    protected $log = false;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates jobs for clients when active = true and allow_automatic_backlog = true';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
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

        $this->log = new Logger('GenerateJobs');
        $formatter = new LineFormatter(null, null, false, true);
        $streamHandler = new StreamHandler(storage_path() . '/logs/generate_job.log', Logger::DEBUG);
        $streamHandler->setFormatter($formatter);
        $this->log->pushHandler($streamHandler);

        // Clients
        $Client = new Client;
        $clients = $Client->getAutoJobsCandidates();

        $totalClients = count($clients);
        $workingCapacity = 0;
        foreach ($clients as $client) {
            $workingCapacity += $client->cap;
        }

        $this->info('Total Clients: ' . $totalClients);
        $this->info('Working capacity: ' . $workingCapacity);
        $this->log->debug('Clients: ' . $totalClients);
        $this->log->debug('Working capacity: ' . $workingCapacity);

        // JobsContact
        $JobsContact = new JobsContact;
        $jobsContactsResume = $JobsContact->getMonthlyResume();

        // BacklogDistribution
        $backlogDistribution = BacklogDistribution::orderBy('priority')->get();

        $assignedCapacity = 0;
        $capacityLeft = $workingCapacity;

        foreach ($backlogDistribution as $distribution) {
            $distributionLeft = $distribution->amount;
            $assigned = 0;
            if (array_key_exists($distribution->priority, $jobsContactsResume)) {
                $assigned = $jobsContactsResume[$distribution->priority];
                $distributionLeft -= $assigned;
            }

            if ($distributionLeft > 0) {
                $this->info('>> Priority: [' . $distribution->priority . ']: ' . $distribution->amount . ' assigned: ' . $assigned . ' available: ' . $distributionLeft);
                $this->log->info('>> Priority: [' . $distribution->priority . ']: ' . $distribution->amount . ' assigned: ' . $assigned . ' available: ' . $distributionLeft);

                if ($capacityLeft > 0) {
                    $toAssignCapacity = ($distributionLeft > $capacityLeft) ? $capacityLeft : $distributionLeft;
                    $assignedCapacity += $JobsContact->createAutoJobs($toAssignCapacity, $distribution->priority, $clients, $distribution->offset_days);
                    $distributionLeft -= $assignedCapacity;
                    $capacityLeft -= $assignedCapacity;
                }

                $this->info('Capacity left: ' . $capacityLeft);
                $this->log->debug('Capacity left: ' . $capacityLeft);
            }
        }

        Process::create([
            'name' => $this->signature,
            'status' => Process::STATUS_DONE,
            'detail' => '{assigned_capacity: ' . $assignedCapacity . '}',
        ]);
    }
}
