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
        Schema::create('student_subject_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->enum('enrollment_type', ['assigned', 'elective'])->default('elective');
            $table->timestamp('enrolled_at')->useCurrent();
            $table->enum('status', ['active', 'completed', 'dropped'])->default('active');
            $table->timestamps();

            $table->unique(['user_id', 'subject_id']);
            $table->index('enrollment_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_subject_enrollments');
    }
};
