<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // التعاميم والإعلانات
        Schema::create('circulars', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('body');
            $table->string('priority')->default('normal'); // urgent, high, normal
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->json('target_branches')->nullable(); // null = جميع الفروع
            $table->json('target_levels')->nullable();   // مستويات أمنية محددة
            $table->string('attachment')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('published_at');
        });

        // قراءات التعاميم
        Schema::create('circular_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('circular_id')->constrained('circulars')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at')->useCurrent();
            $table->timestamps();

            $table->unique(['circular_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('circular_reads');
        Schema::dropIfExists('circulars');
    }
};
