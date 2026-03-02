<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedInteger('geofence_radius')->default(17); // بالمتر
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->integer('total_points')->default(0);
            $table->string('level')->default('مبتدئ'); // أسطوري، ألماسي، ذهبي، فضي، برونزي، مبتدئ
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
