<?php

use Illuminate\Database\Seeder;

class ContactsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('contacts')->insert(array(
            [
                'id'                        => 1,
                'priority'                  => 1,
                'url'                       => 'https://linkedin.com/sales/profile/10010445,ooG9,name',
                'external_id'               => '1200',
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 2,
                'priority'                  => 1,
                'url'                       => 'https://linkedin.com/sales/profile/100596701,pWU_,name',
                'external_id'               => '1201',
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 3,
                'priority'                  => 1,
                'url'                       => 'https://linkedin.com/sales/profile/10085857,rMId,name',
                'external_id'               => '1202',
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 4,
                'priority'                  => 1,
                'url'                       => 'https://linkedin.com/sales/profile/10104951,rOOD,name',
                'external_id'               => '1203',
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 5,
                'priority'                  => 1,
                'url'                       => 'https://linkedin.com/sales/profile/101897934,,',
                'external_id'               => '1204',
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 6,
                'priority'                  => 2,
                'url'                       => 'https://linkedin.com/sales/profile/10229305,E5-M,name',
                'external_id'               => '1205',
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 7,
                'priority'                  => 2,
                'url'                       => 'https://linkedin.com/sales/profile/1024954,wQK6,name',
                'external_id'               => '1206',
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 8,
                'priority'                  => 3,
                'url'                       => 'https://linkedin.com/sales/profile/10249979,t_fi,name',
                'external_id'               => '1207',
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 9,
                'priority'                  => 3,
                'url'                       => 'https://linkedin.com/sales/profile/10251036,u3xq,name',
                'external_id'               => '1208',
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ]
        ));

        // \Artisan::call('jobs:generate');
    }
}
