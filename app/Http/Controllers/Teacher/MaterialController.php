<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MaterialController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $materials = $user->uploadedMaterials()
            ->with(['subject:id,name,code', 'classRoom:id,name,grade_level'])
            ->withCount('activities')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $subjects = $user->teacherSubjects()->get(['subjects.id', 'subjects.name', 'subjects.code']);

        return Inertia::render('teacher/Materials/Index', [
            'materials' => $materials,
            'subjects' => $subjects,
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();

        $subjects = $user->teacherSubjects()->get(['subjects.id', 'subjects.name', 'subjects.code']);
        $classes = $user->classSubjects()
            ->with('classRoom:id,name,grade_level,major')
            ->get()
            ->pluck('classRoom')
            ->unique('id')
            ->values();

        return Inertia::render('teacher/Materials/Create', [
            'subjects' => $subjects,
            'classes' => $classes,
            'materialTypes' => $this->getMaterialTypes(),
            'learningStyles' => $this->getLearningStyles(),
            'difficultyLevels' => $this->getDifficultyLevels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'topic' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['video', 'document', 'infographic', 'audio', 'simulation'])],
            'learning_style' => ['required', Rule::in(['visual', 'auditory', 'kinesthetic', 'all'])],
            'difficulty_level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'content_url' => ['nullable', 'url', 'max:500'],
            'file' => ['nullable', 'file', 'max:51200'], // 50MB max
            'is_active' => ['boolean'],
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('materials', 'public');
        }

        LearningMaterial::create([
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $request->user()->id,
            'class_id' => $validated['class_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'topic' => $validated['topic'] ?? null,
            'type' => $validated['type'],
            'learning_style' => $validated['learning_style'],
            'difficulty_level' => $validated['difficulty_level'],
            'content_url' => $validated['content_url'] ?? null,
            'file_path' => $filePath,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('manage.materials.index')
            ->with('success', 'Materi berhasil ditambahkan!');
    }

    public function show(Request $request, LearningMaterial $material): Response
    {
        Gate::authorize('view', $material);

        $material->load(['subject:id,name,code', 'classRoom:id,name,grade_level']);

        // Get activity stats
        $activities = $material->activities()
            ->with('user:id,name')
            ->orderByDesc('started_at')
            ->limit(20)
            ->get();

        $stats = [
            'totalViews' => $material->activities()->count(),
            'completions' => $material->activities()->whereNotNull('completed_at')->count(),
            'averageDuration' => round($material->activities()->avg('duration_seconds') / 60, 1),
        ];

        return Inertia::render('teacher/Materials/Show', [
            'material' => $material,
            'activities' => $activities,
            'stats' => $stats,
        ]);
    }

    public function edit(Request $request, LearningMaterial $material): Response
    {
        Gate::authorize('update', $material);

        $user = $request->user();

        $subjects = $user->teacherSubjects()->get(['subjects.id', 'subjects.name', 'subjects.code']);
        $classes = $user->classSubjects()
            ->with('classRoom:id,name,grade_level,major')
            ->get()
            ->pluck('classRoom')
            ->unique('id')
            ->values();

        return Inertia::render('teacher/Materials/Edit', [
            'material' => $material,
            'subjects' => $subjects,
            'classes' => $classes,
            'materialTypes' => $this->getMaterialTypes(),
            'learningStyles' => $this->getLearningStyles(),
            'difficultyLevels' => $this->getDifficultyLevels(),
        ]);
    }

    public function update(Request $request, LearningMaterial $material): RedirectResponse
    {
        Gate::authorize('update', $material);

        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'topic' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['video', 'document', 'infographic', 'audio', 'simulation'])],
            'learning_style' => ['required', Rule::in(['visual', 'auditory', 'kinesthetic', 'all'])],
            'difficulty_level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'content_url' => ['nullable', 'url', 'max:500'],
            'file' => ['nullable', 'file', 'max:51200'],
            'is_active' => ['boolean'],
        ]);

        $filePath = $material->file_path;
        if ($request->hasFile('file')) {
            // Delete old file
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }
            $filePath = $request->file('file')->store('materials', 'public');
        }

        $material->update([
            'subject_id' => $validated['subject_id'],
            'class_id' => $validated['class_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'topic' => $validated['topic'] ?? null,
            'type' => $validated['type'],
            'learning_style' => $validated['learning_style'],
            'difficulty_level' => $validated['difficulty_level'],
            'content_url' => $validated['content_url'] ?? null,
            'file_path' => $filePath,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('manage.materials.index')
            ->with('success', 'Materi berhasil diperbarui!');
    }

    public function destroy(Request $request, LearningMaterial $material): RedirectResponse
    {
        Gate::authorize('delete', $material);

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect()->route('manage.materials.index')
            ->with('success', 'Materi berhasil dihapus!');
    }

    public function toggleActive(Request $request, LearningMaterial $material): RedirectResponse
    {
        Gate::authorize('update', $material);

        $material->update(['is_active' => ! $material->is_active]);

        $status = $material->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Materi berhasil {$status}!");
    }

    private function getMaterialTypes(): array
    {
        return [
            ['value' => 'video', 'label' => 'Video'],
            ['value' => 'document', 'label' => 'Dokumen'],
            ['value' => 'infographic', 'label' => 'Infografis'],
            ['value' => 'audio', 'label' => 'Audio'],
            ['value' => 'simulation', 'label' => 'Simulasi'],
        ];
    }

    private function getLearningStyles(): array
    {
        return [
            ['value' => 'visual', 'label' => 'Visual'],
            ['value' => 'auditory', 'label' => 'Auditori'],
            ['value' => 'kinesthetic', 'label' => 'Kinestetik'],
            ['value' => 'all', 'label' => 'Semua Gaya'],
        ];
    }

    private function getDifficultyLevels(): array
    {
        return [
            ['value' => 'beginner', 'label' => 'Pemula'],
            ['value' => 'intermediate', 'label' => 'Menengah'],
            ['value' => 'advanced', 'label' => 'Lanjutan'],
        ];
    }
}
