<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_transaction_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('installment_no');
            $table->date('due_date')->nullable();
            $table->double('installment_amount', 10, 2);
            $table->double('previous_outstanding', 10, 2)->nullable();
            $table->double('total_recievable', 10, 2);
            $table->double('amount_received', 10, 2)->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->boolean('penalty')->default(0);
            $table->boolean('active')->default(1);
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
        Schema::dropIfExists('loan_payments');
    }
};
