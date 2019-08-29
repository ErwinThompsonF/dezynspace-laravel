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
            $table->string('paypal_id')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('report_time');
            $table->string('timezone');
            $table->float('price');
            $table->decimal('status',4,2);
            $table->string('payment_status')->nullable();
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
