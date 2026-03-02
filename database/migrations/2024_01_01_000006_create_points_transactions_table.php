<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('points'); // موجب أو سالب
            $table->string('type'); // attendance, badge, manual_adjustment, streak
            $table->string('reason');
            $table->morphs('transactionable'); // attendance_log, user_badge, etc.
            $table->foreignId('adjusted_by')->nullable()->constrained('users')->nullOnDelete(); // من أجرى التعديل
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_transactions');
    }
};
