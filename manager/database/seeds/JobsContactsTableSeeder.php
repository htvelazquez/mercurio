<?php

use Illuminate\Database\Seeder;

class JobsContactsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('jobs')->insert([[
            'id'                => 1,
            'name'              => 'High Priority',
            'priority'          => 1,
            'status'            => 0,
            'client_id'         => 1,
            'created_at'        => date("Y-m-d H:i:s"),
            'updated_at'        => date("Y-m-d H:i:s"),
        ],
        [
            'id'                => 2,
            'name'              => 'Medium Priority',
            'priority'          => 4,
            'status'            => 0,
            'client_id'         => 2,
            'created_at'        => date("Y-m-d H:i:s"),
            'updated_at'        => date("Y-m-d H:i:s"),
        ],
        [
            'id'                => 3,
            'name'              => 'Low Priority',
            'priority'          => 7,
            'status'            => 0,
            'client_id'         => 3,
            'created_at'        => date("Y-m-d H:i:s"),
            'updated_at'        => date("Y-m-d H:i:s"),
        ]]);

        /*
        $jobs_contacts = [];

        for ($i = 0; $i < 100; $i++) {
            $jobs_contacts[] = [
                'contact_id'        => 1,
                'job_id'            => 1,
                'status'            => rand(0,1),
                'attempts'          => 0,
                'created_at'        => date("Y-m-d H:i:s"),
                'updated_at'        => date("Y-m-d H:i:s"),
            ];
        }

        for ($i = 0; $i < 100; $i++) {
            $jobs_contacts[] = [
                'contact_id'        => 1,
                'job_id'            => 2,
                'status'            => rand(0,1),
                'attempts'          => 0,
                'created_at'        => date("Y-m-d H:i:s"),
                'updated_at'        => date("Y-m-d H:i:s"),
            ];
        }

        for ($i = 0; $i < 100; $i++) {
            $jobs_contacts[] = [
                'contact_id'        => 1,
                'job_id'            => 3,
                'status'            => rand(0,1),
                'attempts'          => 0,
                'created_at'        => date("Y-m-d H:i:s"),
                'updated_at'        => date("Y-m-d H:i:s"),
            ];
        }

        DB::table('jobs_contacts')->insert($jobs_contacts);
        */
    }
}