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
        Schema::create('trip_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_session_id')->constrained()->cascadeOnDelete();
            $table->string('order_id')->nullable();
            $table->bigInteger('fare_amount')->default(0);
            $table->bigInteger('tip_cash')->default(0);
            $table->bigInteger('tip_app')->default(0);
            $table->unsignedInteger('points_earned')->default(0);
            $table->decimal('distance_km', 6, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_logs');
    }
};
