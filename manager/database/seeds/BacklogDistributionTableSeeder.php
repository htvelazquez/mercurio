<?php

use Illuminate\Database\Seeder;

class BacklogDistributionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('backlog_distributions')->insert(array(
            [
                'id'                        => 1,
                'priority'                  => 1,
                'amount'                    => 5347,
                'offset_days'               => 60,
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 2,
                'priority'                  => 2,
                'amount'                    => 19307,
                'offset_days'               => 90,
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 3,
                'priority'                  => 3,
                'amount'                    => 404,
                'offset_days'               => 120,
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 4,
                'priority'                  => 4,
                'amount'                    => 2111,
                'offset_days'               => 150,
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 5,
                'priority'                  => 5,
                'amount'                    => 1650,
                'offset_days'               => 180,
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 6,
                'priority'                  => 6,
                'amount'                    => 7006,
                'offset_days'               => 180,
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 7,
                'priority'                  => 7,
                'amount'                    => 937,
                'offset_days'               => 240,
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 8,
                'priority'                  => 8,
                'amount'                    => 2864,
                'offset_days'               => 240,
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ]
        ));
    }
}
