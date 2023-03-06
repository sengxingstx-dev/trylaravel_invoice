<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_name');
            $table->dateTime('receipt_date');
            $table->text('note')->nullable();
            $table->double('sub_total')->default(0);
            $table->double('discount')->default(0);
            $table->double('tax')->default(0);
            $table->double('total')->default(0);


            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->foreign('invoice_id')->references('id')
            ->on('invoices')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->foreign('company_id')->references('id')
            ->on('companies')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->foreign('currency_id')->references('id')
            ->on('currencies')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->foreign('created_by')->references('id')
            ->on('users')
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
        Schema::dropIfExists('receipts');
    }
}
