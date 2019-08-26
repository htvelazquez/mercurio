<?php

use Illuminate\Database\Seeder;

class ClientCapsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('client_caps')->insert(array(
            [
                'id'                        => 1,
                'client_id'                 => 1,
                'cap'                       => 5,
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
            [
                'id'                        => 2,
                'client_id'                 => 2,
                'cap'                       => 5,
                'created_at'                => date("Y-m-d H:i:s"),
                'updated_at'                => date("Y-m-d H:i:s"),
            ],
        ));
    }
}
