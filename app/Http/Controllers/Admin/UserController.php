<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::query()
            ->withCount(['learningActivities', 'uploadedMaterials']);

        // Filter by role
        if ($request->has('role') && $request->query('role')) {
            $query->where('role', $request->query('role'));
        }

        // Search
        if ($request->has('search') && $request->query('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('student_id_number', 'like', "%{$search}%")
                    ->orWhere('teacher_id_number', 'like', "%{$search}%");
            });
        }

        $users = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'role' => $request->query('role'),
                'search' => $request->query('search'),
            ],
        ]);
    }

    public function create(): Response
    {
        $classes = ClassRoom::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level']);

        return Inertia::render('admin/Users/Create', [
            'classes' => $classes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', Rule::in(['student', 'teacher', 'admin'])],
            'student_id_number' => ['nullable', 'string', 'max:50'],
            'teacher_id_number' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'student_id_number' => $validated['student_id_number'] ?? null,
            'teacher_id_number' => $validated['teacher_id_number'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email_verified_at' => now(),
        ]);

        // Attach student to class if provided
        if ($validated['role'] === 'student' && ! empty($validated['class_id'])) {
            $user->classes()->attach($validated['class_id'], [
                'enrolled_at' => now(),
                'status' => 'active',
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan!');
    }

    public function show(User $user): Response
    {
        $user->load([
            'learningStyleProfile',
            'classes',
            'homeroomClasses',
        ]);

        // Get additional data based on role
        $additionalData = [];

        if ($user->isStudent()) {
            $additionalData = [
                'activitiesCount' => $user->learningActivities()->count(),
                'completedCount' => $user->learningActivities()->whereNotNull('completed_at')->count(),
                'totalLearningMinutes' => round($user->learningActivities()->sum('duration_seconds') / 60),
                'recentActivities' => $user->learningActivities()
                    ->with('material:id,title,type')
                    ->orderByDesc('started_at')
                    ->limit(10)
                    ->get(),
            ];
        } elseif ($user->isTeacher()) {
            $additionalData = [
                'materialsCount' => $user->uploadedMaterials()->count(),
                'activeMaterialsCount' => $user->uploadedMaterials()->where('is_active', true)->count(),
                'teachingClasses' => $user->classSubjects()
                    ->with(['classRoom:id,name,grade_level', 'subject:id,name,code'])
                    ->get(),
                'recentMaterials' => $user->uploadedMaterials()
                    ->with('subject:id,name')
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get(),
            ];
        }

        return Inertia::render('admin/Users/Show', [
            'user' => $user,
            'additionalData' => $additionalData,
        ]);
    }

    public function edit(User $user): Response
    {
        $user->load('classes');

        $classes = ClassRoom::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level']);

        return Inertia::render('admin/Users/Edit', [
            'user' => $user,
            'classes' => $classes,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', Password::defaults()],
            'role' => ['required', Rule::in(['student', 'teacher', 'admin'])],
            'student_id_number' => ['nullable', 'string', 'max:50'],
            'teacher_id_number' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'student_id_number' => $validated['student_id_number'] ?? null,
            'teacher_id_number' => $validated['teacher_id_number'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Update class for students
        if ($validated['role'] === 'student') {
            // Remove from all classes first
            $user->classes()->wherePivot('status', 'active')->updateExistingPivot(
                $user->classes()->pluck('classes.id'),
                ['status' => 'transferred']
            );

            // Add to new class if provided
            if (! empty($validated['class_id'])) {
                $user->classes()->syncWithoutDetaching([
                    $validated['class_id'] => [
                        'enrolled_at' => now(),
                        'status' => 'active',
                    ],
                ]);
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil diperbarui!');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri!');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus!');
    }
}
