<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // الورديات
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // الوردية الصباحية، المسائية، إلخ
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('grace_minutes')->default(10); // دقائق السماح
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ورديات المستخدمين (كيان مستقل)
        Schema::create('user_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_shifts');
        Schema::dropIfExists('shifts');
    }
};
