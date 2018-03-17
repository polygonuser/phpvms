<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJournalTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journal_transactions', function (Blueprint $table) {
            $table->char('id', 36)->unique();
            $table->string('transaction_group')->nullable();
            $table->integer('journal_id');
            $table->unsignedBigInteger('credit')->nullable();
            $table->unsignedBigInteger('debit')->nullable();
            $table->char('currency', 5);
            $table->text('memo')->nullable();
            $table->string('tags')->nullable();
            $table->string('ref_class', 50)->nullable();
            $table->string('ref_class_id', 36)->nullable();
            $table->timestamps();
            $table->date('post_date');

            $table->primary('id');
            $table->index('journal_id');
            $table->index('transaction_group');
            $table->index(['ref_class', 'ref_class_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('journal_transactions');
    }
}
