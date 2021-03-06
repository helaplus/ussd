<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUssdMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ussd_menu_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('menu_id')->unsigned();
            $table->text('description');
            $table->integer('type')->default(0);
            $table->integer('next_menu_id')->default(NULL);
            $table->integer('step');
            $table->string('validation')->nullable();
            $table->string('variable_name')->nullable();
            $table->string('variable_type')->default('string');
            $table->string('confirmation_phrase');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ussd_menu_items');
    }
}
