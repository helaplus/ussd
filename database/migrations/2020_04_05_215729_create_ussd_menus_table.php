<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUssdMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ussd_menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('app_id')->default(1);
            $table->string('title')->nullable();
            $table->string('is_root')->default(0);
            $table->string('description')->nullable();
            $table->integer('type')->default(1);
            $table->boolean('skippable')->default(0);
            $table->boolean('confirmable')->default(0);
            $table->integer('next_ussd_menu_id')->default(0);
            $table->string('event')->nullable();
            $table->text('confirmation_message')->nullable();
            $table->text('sms')->nullable();
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
        Schema::dropIfExists('ussd_menus');
    }
}
