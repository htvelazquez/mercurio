<?php

use Illuminate\Database\Seeder;

class ClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('clients')->insert(array(
            [
                'id'                        => 1,
                'name'                      => 'Bruno',
                'email'                     => 'bruno.paglialunga@mercurio.com',
                'related_salesforce_user'   => null,
                'allow_automatic_backlog'   => 1,
                'active'                    => 1,
                'tester'                    => 0,
                'cap'                       => 150,
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 2,
                'name'                      => 'Hernán Velazquez',
                'email'                     => 'htvelazquez@gmail.com',
                'related_salesforce_user'   => null,
                'allow_automatic_backlog'   => 1,
                'active'                    => 1,
                'tester'                    => 0,
                'cap'                       => 150,
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ]
        ));

        \Artisan::call('caps:generate');
    }
}