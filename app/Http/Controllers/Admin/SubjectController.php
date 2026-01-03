<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SubjectController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Subject::query()
            ->withCount(['learningMaterials', 'classSubjects']);

        // Search
        if ($request->has('search') && $request->query('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $subjects = $query->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/Subjects/Index', [
            'subjects' => $subjects,
            'filters' => [
                'search' => $request->query('search'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/Subjects/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Subject::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('manage.subjects.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan!');
    }

    public function show(Subject $subject): Response
    {
        $subject->loadCount(['learningMaterials', 'classSubjects']);

        // Get materials for this subject
        $materials = $subject->learningMaterials()
            ->with('teacher:id,name')
            ->withCount('activities')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Get classes that have this subject
        $classes = $subject->classSubjects()
            ->with([
                'classRoom:id,name,grade_level,academic_year',
                'teacher:id,name',
            ])
            ->get();

        // Statistics
        $stats = [
            'totalMaterials' => $subject->learning_materials_count,
            'activeMaterials' => $subject->learningMaterials()->where('is_active', true)->count(),
            'totalClasses' => $classes->count(),
            'totalTeachers' => $classes->pluck('teacher_id')->unique()->count(),
        ];

        return Inertia::render('admin/Subjects/Show', [
            'subject' => $subject,
            'materials' => $materials,
            'classes' => $classes,
            'stats' => $stats,
        ]);
    }

    public function edit(Subject $subject): Response
    {
        return Inertia::render('admin/Subjects/Edit', [
            'subject' => $subject,
        ]);
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', Rule::unique('subjects')->ignore($subject->id)],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $subject->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('manage.subjects.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui!');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        // Check if subject has materials
        if ($subject->learningMaterials()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus mata pelajaran yang masih memiliki materi!');
        }

        // Check if subject is assigned to classes
        if ($subject->classSubjects()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus mata pelajaran yang masih digunakan di kelas!');
        }

        $subject->delete();

        return redirect()->route('manage.subjects.index')
            ->with('success', 'Mata pelajaran berhasil dihapus!');
    }
}
