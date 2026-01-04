<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL for MySQL to properly handle the constraint changes
        if (DB::connection()->getDriverName() === 'mysql') {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Drop the unique constraint
            DB::statement('ALTER TABLE student_progress DROP INDEX student_progress_user_id_subject_id_class_id_topic_unique');

            // Make class_id nullable
            DB::statement('ALTER TABLE student_progress MODIFY class_id BIGINT UNSIGNED NULL');

            // Add new unique constraint
            DB::statement('ALTER TABLE student_progress ADD UNIQUE student_progress_user_id_subject_id_topic_unique (user_id, subject_id, topic)');

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } else {
            // For SQLite (testing)
            Schema::table('student_progress', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'subject_id', 'class_id', 'topic']);
            });

            Schema::table('student_progress', function (Blueprint $table) {
                $table->unsignedBigInteger('class_id')->nullable()->change();
                $table->unique(['user_id', 'subject_id', 'topic']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Drop new unique constraint
            DB::statement('ALTER TABLE student_progress DROP INDEX student_progress_user_id_subject_id_topic_unique');

            // Make class_id required (set to 1 for existing nulls)
            DB::statement('UPDATE student_progress SET class_id = 1 WHERE class_id IS NULL');
            DB::statement('ALTER TABLE student_progress MODIFY class_id BIGINT UNSIGNED NOT NULL');

            // Restore old unique constraint
            DB::statement('ALTER TABLE student_progress ADD UNIQUE student_progress_user_id_subject_id_class_id_topic_unique (user_id, subject_id, class_id, topic)');

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } else {
            Schema::table('student_progress', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'subject_id', 'topic']);
            });

            Schema::table('student_progress', function (Blueprint $table) {
                $table->unsignedBigInteger('class_id')->nullable(false)->change();
                $table->unique(['user_id', 'subject_id', 'class_id', 'topic']);
            });
        }
    }
};
