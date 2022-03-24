<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUssdResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ussd_responses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone')->default(0);
            $table->integer('menu_id')->unsigned()->nullable();
            $table->integer('menu_item_id')->unsigned()->nullable();
            $table->string('variable')->nullable();
            $table->longText('response', 45);
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
        Schema::dropIfExists('ussd_responses');
    }
}
