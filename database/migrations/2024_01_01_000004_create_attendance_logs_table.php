<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->date('attendance_date');
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->decimal('check_in_lat', 10, 8)->nullable();
            $table->decimal('check_in_lng', 11, 8)->nullable();
            $table->decimal('check_out_lat', 10, 8)->nullable();
            $table->decimal('check_out_lng', 11, 8)->nullable();
            $table->decimal('check_in_distance', 8, 2)->nullable(); // المسافة بالمتر عند الدخول
            $table->enum('status', ['on_time', 'late', 'absent', 'excused'])->default('absent');
            $table->unsignedSmallInteger('delay_minutes')->default(0);
            $table->decimal('financial_deduction', 10, 2)->default(0); // الخصم المالي
            $table->decimal('overtime_hours', 5, 2)->default(0); // ساعات إضافية
            $table->integer('points_earned')->default(0); // النقاط المكتسبة من هذا السجل
            $table->boolean('is_manual')->default(false); // تسجيل يدوي بواسطة المدير
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'attendance_date', 'shift_id']);
            $table->index(['branch_id', 'attendance_date']);
            $table->index(['user_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
