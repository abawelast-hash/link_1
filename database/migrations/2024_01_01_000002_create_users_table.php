<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('employee_id')->unique()->nullable(); // رقم الموظف
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->unsignedTinyInteger('security_level')->default(2); // 1-10
            $table->boolean('is_super_admin')->default(false);
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('position')->nullable(); // المنصب الوظيفي
            $table->string('department')->nullable();
            $table->decimal('hourly_rate', 10, 2)->default(0); // معدل الساعة للتكاليف
            $table->integer('total_points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('fcm_token')->nullable(); // للإشعارات
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'security_level']);
            $table->index('is_active');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
