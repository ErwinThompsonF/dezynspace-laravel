<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::enableForeignKeyConstraints();
        Schema::create('bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('plan');
            $table->unsignedBigInteger('clientId');
            $table->foreign('clientId')->references('id')->on('users');
            $table->unsignedBigInteger('designerId')->nullable();
            $table->foreign('designerId')->references('id')->on('designers');
            $table->string('paypal_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('report_time');
            $table->string('timezone');
            $table->integer('price');
            $table->string('status');
            $table->string('payment_status');
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
        Schema::dropIfExists('bookings');
    }
}
