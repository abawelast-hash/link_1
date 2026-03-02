<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // الشارات
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->default('🏅');
            $table->integer('points_reward')->default(0);
            $table->string('condition_type'); // streak_days, monthly_nodelta, monthly_overtime, etc.
            $table->json('condition_params')->nullable(); // {"days": 7}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // شارات المستخدمين (كيان مستقل)
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained('badges')->cascadeOnDelete();
            $table->date('awarded_at');
            $table->string('period')->nullable(); // "2026-01" للشهري
            $table->timestamps();

            $table->index(['user_id', 'badge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
    }
};
