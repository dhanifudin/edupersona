<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClassController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ClassRoom::query()
            ->with('homeroomTeacher:id,name')
            ->withCount(['activeStudents', 'classSubjects']);

        // Filter by grade level
        if ($request->has('grade') && $request->query('grade')) {
            $query->where('grade_level', $request->query('grade'));
        }

        // Filter by academic year
        if ($request->has('year') && $request->query('year')) {
            $query->where('academic_year', $request->query('year'));
        }

        // Filter by status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Search
        if ($request->has('search') && $request->query('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('major', 'like', "%{$search}%");
            });
        }

        $classes = $query->orderBy('grade_level')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // Get distinct academic years for filter
        $academicYears = ClassRoom::distinct()->pluck('academic_year')->sort()->values();

        return Inertia::render('admin/Classes/Index', [
            'classes' => $classes,
            'academicYears' => $academicYears,
            'filters' => [
                'grade' => $request->query('grade'),
                'year' => $request->query('year'),
                'active' => $request->query('active'),
                'search' => $request->query('search'),
            ],
        ]);
    }

    public function create(): Response
    {
        $teachers = User::where('role', 'teacher')
            ->orderBy('name')
            ->get(['id', 'name', 'teacher_id_number']);

        return Inertia::render('admin/Classes/Create', [
            'teachers' => $teachers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'grade_level' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'major' => ['nullable', 'string', 'max:100'],
            'academic_year' => ['required', 'string', 'max:9', 'regex:/^\d{4}\/\d{4}$/'],
            'homeroom_teacher_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['boolean'],
        ]);

        // Validate uniqueness of name + academic_year
        $exists = ClassRoom::where('name', $validated['name'])
            ->where('academic_year', $validated['academic_year'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'name' => 'Kelas dengan nama ini sudah ada di tahun ajaran yang sama.',
            ]);
        }

        ClassRoom::create([
            'name' => $validated['name'],
            'grade_level' => $validated['grade_level'],
            'major' => $validated['major'] ?? null,
            'academic_year' => $validated['academic_year'],
            'homeroom_teacher_id' => $validated['homeroom_teacher_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil ditambahkan!');
    }

    public function show(ClassRoom $class): Response
    {
        $class->load([
            'homeroomTeacher:id,name,email,teacher_id_number',
            'activeStudents' => fn ($q) => $q->select('users.id', 'users.name', 'users.email', 'users.student_id_number')
                ->with('learningStyleProfile:user_id,dominant_style'),
            'classSubjects.subject:id,name,code',
            'classSubjects.teacher:id,name',
        ]);

        // Get statistics
        $stats = [
            'totalStudents' => $class->activeStudents->count(),
            'totalSubjects' => $class->classSubjects->count(),
        ];

        // Learning style distribution
        $learningStyleDistribution = $class->activeStudents
            ->filter(fn ($s) => $s->learningStyleProfile)
            ->groupBy(fn ($s) => $s->learningStyleProfile->dominant_style)
            ->map->count()
            ->toArray();

        return Inertia::render('admin/Classes/Show', [
            'class' => $class,
            'stats' => $stats,
            'learningStyleDistribution' => $learningStyleDistribution,
        ]);
    }

    public function edit(ClassRoom $class): Response
    {
        $teachers = User::where('role', 'teacher')
            ->orderBy('name')
            ->get(['id', 'name', 'teacher_id_number']);

        return Inertia::render('admin/Classes/Edit', [
            'class' => $class,
            'teachers' => $teachers,
        ]);
    }

    public function update(Request $request, ClassRoom $class): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'grade_level' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'major' => ['nullable', 'string', 'max:100'],
            'academic_year' => ['required', 'string', 'max:9', 'regex:/^\d{4}\/\d{4}$/'],
            'homeroom_teacher_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['boolean'],
        ]);

        // Validate uniqueness (excluding current)
        $exists = ClassRoom::where('name', $validated['name'])
            ->where('academic_year', $validated['academic_year'])
            ->where('id', '!=', $class->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'name' => 'Kelas dengan nama ini sudah ada di tahun ajaran yang sama.',
            ]);
        }

        $class->update([
            'name' => $validated['name'],
            'grade_level' => $validated['grade_level'],
            'major' => $validated['major'] ?? null,
            'academic_year' => $validated['academic_year'],
            'homeroom_teacher_id' => $validated['homeroom_teacher_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil diperbarui!');
    }

    public function destroy(ClassRoom $class): RedirectResponse
    {
        // Check if class has students
        if ($class->activeStudents()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus kelas yang masih memiliki siswa aktif!');
        }

        $class->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil dihapus!');
    }

    public function toggleActive(ClassRoom $class): RedirectResponse
    {
        $class->update(['is_active' => ! $class->is_active]);

        $status = $class->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kelas berhasil {$status}!");
    }
}
