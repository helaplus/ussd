<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUssdLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ussd_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('app_id')->default(1);
            $table->string('phone');
            $table->string('text')->nullable();
            $table->string('service_code')->nullable();
            $table->string('session_id')->nullable();
            $table->integer('type')->nullable();
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
        Schema::dropIfExists('ussd_logs');
    }
}
