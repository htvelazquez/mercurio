<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ClientCap;
use App\Client;
use App\Process;

class GenerateCaps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'caps:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates client caps';

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

        // Clients
        $Client = new Client;
        $clients = $Client->get();

        foreach ($clients as $client) {
            $RandomizedCap = $client->cap + rand(-10, 10);

            $ClientCap = new ClientCap;
            $ClientCap->client_id   = $client->id;
            $ClientCap->cap         = $RandomizedCap;
            $ClientCap->save();
        }

        
        Process::create([
            'name' => $this->signature,
            'status' => Process::STATUS_DONE,
            'detail' => '{clients: ' . count($clients) . '}',
        ]);
    }
}
