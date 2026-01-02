<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\FeedbackGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class WeeklyFeedbackJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 300;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(FeedbackGenerator $generator): void
    {
        Log::info('Starting weekly feedback generation for all students');

        // Get all active students with learning profiles
        $students = User::query()
            ->where('role', 'student')
            ->whereHas('learningStyleProfile')
            ->get();

        $generatedCount = 0;
        $skippedCount = 0;

        foreach ($students as $student) {
            try {
                // Check if student has been active in the last week
                $lastActivity = $student->learningActivities()
                    ->latest('started_at')
                    ->first();

                if ($lastActivity && $lastActivity->started_at >= now()->subWeek()) {
                    // Active student - generate weekly summary
                    $generator->generateWeeklySummary($student);
                    $generatedCount++;
                } elseif ($lastActivity && $lastActivity->started_at >= now()->subDays(3)) {
                    // Slightly inactive - skip for now
                    $skippedCount++;
                } else {
                    // Inactive for 3+ days - generate reminder
                    $generator->generateInactivityReminder($student);
                    $generatedCount++;
                }
            } catch (\Exception $e) {
                Log::error('Failed to generate weekly feedback for student', [
                    'user_id' => $student->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Weekly feedback generation completed', [
            'total_students' => $students->count(),
            'generated' => $generatedCount,
            'skipped' => $skippedCount,
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return ['feedback', 'weekly', 'batch'];
    }
}
