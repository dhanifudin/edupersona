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
        Schema::create('learning_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('topic', 255)->nullable();
            $table->enum('type', ['video', 'document', 'infographic', 'audio', 'simulation']);
            $table->enum('learning_style', ['visual', 'auditory', 'kinesthetic', 'all'])->default('all');
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->string('content_url', 500)->nullable();
            $table->string('file_path', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('subject_id');
            $table->index('teacher_id');
            $table->index('class_id');
            $table->index('learning_style');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_materials');
    }
};
