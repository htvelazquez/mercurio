<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            BacklogDistributionTableSeeder::class,
            ClientsTableSeeder::class,
            //ClientCapsTableSeeder::class,
            //ContactsTableSeeder::class,
            //JobsContactsTableSeeder::class,
        ]);
    }
}
