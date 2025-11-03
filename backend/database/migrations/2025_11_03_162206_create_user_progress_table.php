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
        Schema::create('user_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mission_id')->nullable()->constrained('missions')->onDelete('set null');
            $table->enum('progress_type', ['mission', 'course', 'assignment', 'forum'])->default('mission');
            $table->foreignId('related_id')->nullable(); // ID dari course, assignment, atau forum post
            $table->integer('xp_earned')->default(0);
            $table->integer('level_points')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_progress');
    }
};

