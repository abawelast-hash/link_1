<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // قبو البلاغات المشفرة (Whistleblower Vault) — السرية التامة
        Schema::create('whistleblower_reports', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique(); // رمز سري للمتابعة بدون كشف الهوية
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // اختياري للسرية
            $table->string('subject');
            $table->text('body'); // مشفر AES-256
            $table->string('category'); // مالي، إداري، تحرش، إلخ
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['received', 'under_review', 'escalated', 'resolved', 'closed'])->default('received');
            $table->string('attachment')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable(); // ملاحظات المستوى 10 فقط
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whistleblower_reports');
    }
};
