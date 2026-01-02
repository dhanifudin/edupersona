<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\RecommendationEngine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateRecommendationsJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public ?int $subjectId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(RecommendationEngine $engine): void
    {
        Log::info('Generating recommendations for user', [
            'user_id' => $this->user->id,
            'subject_id' => $this->subjectId,
        ]);

        $recommendations = $engine->generateForStudent($this->user, $this->subjectId);

        Log::info('Recommendations generated', [
            'user_id' => $this->user->id,
            'count' => $recommendations->count(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return ['recommendations', 'user:'.$this->user->id];
    }
}
