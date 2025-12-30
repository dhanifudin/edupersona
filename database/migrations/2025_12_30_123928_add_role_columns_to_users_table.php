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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['student', 'teacher', 'admin'])->default('student')->after('email');
            $table->string('student_id_number', 50)->nullable()->after('role');
            $table->string('teacher_id_number', 50)->nullable()->after('student_id_number');
            $table->string('phone', 20)->nullable()->after('teacher_id_number');
            $table->text('learning_interests')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'student_id_number', 'teacher_id_number', 'phone', 'learning_interests']);
        });
    }
};
