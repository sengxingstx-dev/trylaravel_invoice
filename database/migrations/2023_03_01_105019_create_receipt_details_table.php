<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiptDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipt_details', function (Blueprint $table) {
            $table->id();
            $table->integer('order_number');
            $table->string('name');
            $table->text('description');
            $table->double('quantity')->default(0);
            $table->double('price')->default(0);
            $table->double('total')->default(0);


            $table->unsignedBigInteger('receipt_id')->nullable();

            $table->foreign('receipt_id')->references('id')
            ->on('receipts')
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receipt_details');
    }
}
