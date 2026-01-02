<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\FeedbackGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateFeedbackJob implements ShouldQueue
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
        public string $contextType = 'general',
        public ?int $contextId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(FeedbackGenerator $generator): void
    {
        Log::info('Generating feedback for user', [
            'user_id' => $this->user->id,
            'context_type' => $this->contextType,
            'context_id' => $this->contextId,
        ]);

        $feedback = match ($this->contextType) {
            'activity_completion' => $this->contextId
                ? $generator->generateForActivity($this->user, $this->contextId)
                : null,
            'weekly_summary' => $generator->generateWeeklySummary($this->user),
            'inactivity_reminder' => $generator->generateInactivityReminder($this->user),
            default => $generator->generateForStudent($this->user, $this->contextType),
        };

        Log::info('Feedback generated', [
            'user_id' => $this->user->id,
            'feedback_id' => $feedback?->id,
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return ['feedback', 'user:'.$this->user->id, 'context:'.$this->contextType];
    }
}
