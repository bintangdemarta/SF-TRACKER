<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->constrained('expense_categories');
            $table->bigInteger('amount');
            $table->enum('payment_source', ['cash', 'digital_balance'])->index();
            $table->unsignedInteger('odometer')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_reimbursable')->default(false);
            $table->timestamp('reimbursed_at')->nullable();
            $table->unsignedBigInteger('reimbursement_wallet_transaction_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
