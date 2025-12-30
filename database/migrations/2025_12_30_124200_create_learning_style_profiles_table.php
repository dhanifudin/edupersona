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
        Schema::create('learning_style_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('visual_score', 5, 2);
            $table->decimal('auditory_score', 5, 2);
            $table->decimal('kinesthetic_score', 5, 2);
            $table->enum('dominant_style', ['visual', 'auditory', 'kinesthetic', 'mixed']);
            $table->timestamp('analyzed_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_style_profiles');
    }
};
