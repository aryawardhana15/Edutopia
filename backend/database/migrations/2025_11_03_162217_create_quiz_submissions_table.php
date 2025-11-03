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
        Schema::create('quiz_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->foreignId('pelajar_id')->constrained('users')->onDelete('cascade');
            $table->json('answers'); // {"question_id": "answer"}
            $table->integer('score')->nullable();
            $table->integer('total_score')->nullable();
            $table->datetime('started_at')->useCurrent();
            $table->datetime('submitted_at')->nullable();
            $table->integer('time_spent')->nullable(); // In minutes
            $table->enum('status', ['in_progress', 'submitted', 'graded'])->default('in_progress');
            $table->timestamps();
            
            $table->unique(['quiz_id', 'pelajar_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_submissions');
    }
};

