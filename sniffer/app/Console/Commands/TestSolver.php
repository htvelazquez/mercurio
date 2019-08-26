<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Alert;
use App\Jobs\ProcessAlert;

class TestSolver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:solver';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It dispatchs alerts jobs to test sniffer solver';

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
        // $alerts = Alert::where([
        //     ['checked_by_solver', '=', 0],
        //     ['status', '<>', 1]])->get();
        //
        $alert = Alert::find(76980);
        // foreach ($alerts as $alert) {
            ProcessAlert::dispatch($alert);
        // }
    }

}
