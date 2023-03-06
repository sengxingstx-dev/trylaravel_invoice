<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_details', function (Blueprint $table) {
            $table->id();
            $table->integer('order_number');
            $table->string('name');
            $table->text('description')->nullable();
            $table->double('quantity')->default(0);
            $table->double('price')->default(0);
            $table->double('total')->default(0);
            

            $table->unsignedBigInteger('quotation_id')->nullable();

            $table->foreign('quotation_id')->references('id')
            ->on('quotations')
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
        Schema::dropIfExists('quotation_details');
    }
}
