<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreQuestionnaireRequest;
use App\Jobs\GenerateFeedbackJob;
use App\Jobs\GenerateRecommendationsJob;
use App\Models\LearningStyleQuestion;
use App\Models\LearningStyleResponse;
use App\Services\LearningStyleAnalyzer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class QuestionnaireController extends Controller
{
    public function __construct(
        private LearningStyleAnalyzer $analyzer
    ) {}

    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        // Redirect if already completed
        if ($user->learningStyleProfile) {
            return redirect()->route('learning-profile.show')
                ->with('info', 'Kamu sudah mengisi kuesioner. Lihat profil gaya belajarmu di sini.');
        }

        $questions = LearningStyleQuestion::active()
            ->ordered()
            ->get(['id', 'question_text', 'style_type', 'order']);

        return Inertia::render('student/Questionnaire', [
            'questions' => $questions,
        ]);
    }

    public function store(StoreQuestionnaireRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Prevent re-submission
        if ($user->learningStyleProfile) {
            return redirect()->route('learning-profile.show')
                ->with('error', 'Kamu sudah mengisi kuesioner sebelumnya.');
        }

        DB::transaction(function () use ($user, $request) {
            // Delete any existing responses (for safety)
            LearningStyleResponse::where('user_id', $user->id)->delete();

            // Store new responses
            foreach ($request->validated()['responses'] as $response) {
                LearningStyleResponse::create([
                    'user_id' => $user->id,
                    'question_id' => $response['question_id'],
                    'score' => $response['score'],
                ]);
            }

            // Analyze and create profile
            $this->analyzer->analyze($user);
        });

        // Generate initial AI recommendations and welcome feedback (async)
        GenerateRecommendationsJob::dispatch($user);
        GenerateFeedbackJob::dispatch($user, 'welcome');

        return redirect()->route('learning-profile.show')
            ->with('success', 'Kuesioner berhasil disimpan! Lihat profil gaya belajarmu.');
    }
}
