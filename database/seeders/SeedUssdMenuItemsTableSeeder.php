<?php

namespace Helaplus\Ussd\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SeedUssdMenuItemsTableSeeder extends Seeder
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
        DB::table('ussd_menus_items')->truncate();

        DB::table('ussd_menus_items')->delete();

        DB::table('ussd_menus_items')->insert(array(
            array(
                'menu_id' => 1,
                'description' => 'Sample description 1 ',
                'next_menu_id' => 0,
                'step' => 1,
                'validation' => '',
                'confirmation_phrase' => 'Confirmation Phrase 1',
            ),
            array(
                'menu_id' => 1,
                'description' => 'Sample Description 2',
                'next_menu_id' => 0,
                'step' => 2,
                'validation' => '',
                'confirmation_phrase' => 'Confirmation Phrase 2',
            ),
            array(
                'menu_id' => 1,
                'description' => 'Sample Description 3',
                'next_menu_id' => 0,
                'step' => 3,
                'validation' => '',
                'confirmation_phrase' => 'Confirmation Phrase 3',
            ),

            array(
                'menu_id' => 2,
                'description' => 'Sample Description 4',
                'next_menu_id' => 2,
                'step' => 1,
                'validation' => 'custom',
                'confirmation_phrase' => 'Confirmation Phrase 4',
            ),
            array(
                'menu_id' => 3,
                'description' => 'Sample Description 5',
                'next_menu_id' => 0,
                'step' => 1,
                'validation' => 'custom',
                'confirmation_phrase' => 'Confirmation Phrase 5',
            ),
            array(
                'menu_id' => 3,
                'description' => 'Sample Description 6',
                'next_menu_id' => 0,
                'step' => 2,
                'validation' => 'min:4|max:4',
                'confirmation_phrase' => 'Confirmation Phrase 6',
            ),
            array(
                'menu_id' => 3,
                'description' => 'Sample Description 7',
                'next_menu_id' => 0,
                'step' => 3,
                'validation' => 'custom',
                'confirmation_phrase' => 'Confirmation Phrase 7',
            ),
            array(
                'menu_id' => 3,
                'description' => 'Sample Description 8',
                'next_menu_id' => 0,
                'step' => 3,
                'validation' => 'custom',
                'confirmation_phrase' => 'Confirmation Phrase 8',
            ),
            array(
                'menu_id' => 3,
                'description' => 'Sample Description 9',
                'next_menu_id' => 4,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => 'Confirmation Phrase 9',
            ),
            array(
                'menu_id' => 3,
                'description' => 'Sample Description 10',
                'next_menu_id' => 5,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => 'Confirmation Phrase 10',
            )));
    }
}
