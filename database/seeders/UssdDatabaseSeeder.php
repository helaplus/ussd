<?php

namespace Helaplus\Ussd\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UssdDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call("Helaplus\Ussd\Database\Seeders\SeedUssdMenuTableSeeder");
        $this->call("Helaplus\Ussd\Database\Seeders\SeedUssdMenuItemsTableSeeder");
    }
}
