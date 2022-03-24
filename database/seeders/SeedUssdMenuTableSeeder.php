<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SeedUssdMenuTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('ussd_menus')->truncate();

        DB::table('ussd_menus')->delete();

        DB::table('ussd_menus')->insert(array(
            //menu 1
            array(
                'app_id' => 1,
                'title' => 'Sample Menu of Type Process',
                'description' => 'Sample Menu of Type 3',
                'is_root' => 1,
                'type' => 3,
                'skippable'=>true,
                'confirmable'=>0,
                'next_ussd_menu_id'=>2,
                'confirmation_message' => "Thank you for trying this Menu",
            ),
            //Menu 2
            array(
                'app_id' => 1,
                'title' => 'Sample Menu of Type 2',
                'description' => 'Sample Menu of Type 2',
                'is_root' => 0,
                'type' => 2,
                'skippable'=>false,
                'confirmable'=>0,
                'next_ussd_menu_id'=>0,
                'confirmation_message' => "This is a sample confirmation Message",
            ),
            //Menu 3
            array(
                'app_id' => 1,
                'title' => 'Sample Menu of Type 1',
                'description' => 'This is a sample description',
                'is_root' => 0,
                'type' => 1,
                'skippable'=>false,
                'confirmable'=>0,
                'next_ussd_menu_id'=>0,
                'confirmation_message' => "This is a sample confirmation Message",
            ),
        ));
    }
}
