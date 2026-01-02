<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\RecommendationEngine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RefreshRecommendationsJob implements ShouldQueue
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
    public function handle(RecommendationEngine $engine): void
    {
        Log::info('Starting daily recommendations refresh for active students');

        // Get students who were active in the last 7 days and have learning profiles
        $activeStudents = User::query()
            ->where('role', 'student')
            ->whereHas('learningStyleProfile')
            ->whereHas('learningActivities', function ($query) {
                $query->where('started_at', '>=', now()->subDays(7));
            })
            ->get();

        $refreshedCount = 0;

        foreach ($activeStudents as $student) {
            try {
                // Clear recommendation cache
                Cache::forget("recommendations:{$student->id}");

                // Refresh recommendations
                $engine->refreshForStudent($student);
                $refreshedCount++;

                Log::debug('Refreshed recommendations for student', [
                    'user_id' => $student->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to refresh recommendations for student', [
                    'user_id' => $student->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Daily recommendations refresh completed', [
            'total_students' => $activeStudents->count(),
            'refreshed' => $refreshedCount,
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return ['recommendations', 'daily', 'batch'];
    }
}
