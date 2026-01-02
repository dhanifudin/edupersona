<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LearningActivity;
use App\Models\LearningMaterial;
use App\Models\LearningStyleProfile;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('admin/Reports/Index', [
            'reportTypes' => $this->getReportTypes(),
        ]);
    }

    public function generate(Request $request): Response|StreamedResponse
    {
        $request->validate([
            'type' => 'required|in:learning_styles,student_progress,material_usage,class_analytics',
            'format' => 'required|in:pdf,csv',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $type = $request->input('type');
        $format = $request->input('format');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $data = match ($type) {
            'learning_styles' => $this->getLearningStylesData(),
            'student_progress' => $this->getStudentProgressData($dateFrom, $dateTo),
            'material_usage' => $this->getMaterialUsageData($dateFrom, $dateTo),
            'class_analytics' => $this->getClassAnalyticsData(),
            default => [],
        };

        if ($format === 'pdf') {
            return $this->generatePdf($type, $data);
        }

        return $this->generateCsv($type, $data);
    }

    /**
     * Get available report types.
     *
     * @return array<int, array<string, string>>
     */
    private function getReportTypes(): array
    {
        return [
            [
                'id' => 'learning_styles',
                'name' => 'Distribusi Gaya Belajar',
                'description' => 'Laporan distribusi gaya belajar siswa',
            ],
            [
                'id' => 'student_progress',
                'name' => 'Kemajuan Siswa',
                'description' => 'Laporan kemajuan belajar semua siswa',
            ],
            [
                'id' => 'material_usage',
                'name' => 'Penggunaan Materi',
                'description' => 'Laporan statistik penggunaan materi pembelajaran',
            ],
            [
                'id' => 'class_analytics',
                'name' => 'Analitik Kelas',
                'description' => 'Laporan analitik per kelas',
            ],
        ];
    }

    /**
     * Get learning styles distribution data.
     *
     * @return array<string, mixed>
     */
    private function getLearningStylesData(): array
    {
        $distribution = LearningStyleProfile::query()
            ->select('dominant_style', DB::raw('COUNT(*) as count'))
            ->groupBy('dominant_style')
            ->get();

        $profiles = LearningStyleProfile::query()
            ->with('user:id,name,email')
            ->latest('analyzed_at')
            ->get()
            ->map(fn ($profile) => [
                'student_name' => $profile->user?->name ?? 'Unknown',
                'email' => $profile->user?->email ?? '',
                'dominant_style' => $this->getStyleLabel($profile->dominant_style),
                'visual_score' => $profile->visual_score,
                'auditory_score' => $profile->auditory_score,
                'kinesthetic_score' => $profile->kinesthetic_score,
                'analyzed_at' => $profile->analyzed_at?->format('d M Y'),
            ]);

        return [
            'title' => 'Laporan Distribusi Gaya Belajar',
            'generated_at' => now()->format('d M Y H:i'),
            'distribution' => $distribution,
            'profiles' => $profiles,
            'total_students' => $profiles->count(),
        ];
    }

    /**
     * Get student progress data.
     *
     * @return array<string, mixed>
     */
    private function getStudentProgressData(?string $dateFrom, ?string $dateTo): array
    {
        $query = User::query()
            ->where('role', 'student')
            ->with(['learningStyleProfile', 'learningActivities' => function ($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) {
                    $q->where('started_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $q->where('started_at', '<=', $dateTo.' 23:59:59');
                }
            }]);

        $students = $query->get()->map(function ($student) {
            $activities = $student->learningActivities;
            $totalMinutes = $activities->sum('duration_seconds') / 60;
            $completed = $activities->whereNotNull('completed_at')->count();
            $total = $activities->count();

            return [
                'name' => $student->name,
                'email' => $student->email,
                'learning_style' => $student->learningStyleProfile
                    ? $this->getStyleLabel($student->learningStyleProfile->dominant_style)
                    : 'Belum diisi',
                'total_activities' => $total,
                'completed_activities' => $completed,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
                'total_minutes' => round($totalMinutes),
            ];
        });

        return [
            'title' => 'Laporan Kemajuan Siswa',
            'generated_at' => now()->format('d M Y H:i'),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'students' => $students,
            'total_students' => $students->count(),
            'summary' => [
                'avg_completion_rate' => $students->count() > 0
                    ? round($students->avg('completion_rate'))
                    : 0,
                'total_activities' => $students->sum('total_activities'),
                'total_minutes' => $students->sum('total_minutes'),
            ],
        ];
    }

    /**
     * Get material usage data.
     *
     * @return array<string, mixed>
     */
    private function getMaterialUsageData(?string $dateFrom, ?string $dateTo): array
    {
        $query = LearningMaterial::query()
            ->withCount(['activities as usage_count' => function ($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) {
                    $q->where('started_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $q->where('started_at', '<=', $dateTo.' 23:59:59');
                }
            }])
            ->with('subject:id,name', 'teacher:id,name');

        $materials = $query->get()->map(fn ($material) => [
            'title' => $material->title,
            'subject' => $material->subject?->name ?? 'N/A',
            'teacher' => $material->teacher?->name ?? 'N/A',
            'type' => $this->getMaterialTypeLabel($material->type),
            'learning_style' => $this->getStyleLabel($material->learning_style),
            'usage_count' => $material->usage_count,
            'is_active' => $material->is_active ? 'Aktif' : 'Nonaktif',
        ]);

        $byType = LearningActivity::query()
            ->join('learning_materials', 'learning_activities.material_id', '=', 'learning_materials.id')
            ->when($dateFrom, fn ($q) => $q->where('started_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('started_at', '<=', $dateTo.' 23:59:59'))
            ->select('learning_materials.type', DB::raw('COUNT(*) as count'))
            ->groupBy('learning_materials.type')
            ->get();

        return [
            'title' => 'Laporan Penggunaan Materi',
            'generated_at' => now()->format('d M Y H:i'),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'materials' => $materials->sortByDesc('usage_count')->values(),
            'total_materials' => $materials->count(),
            'by_type' => $byType,
            'summary' => [
                'total_usage' => $materials->sum('usage_count'),
                'active_materials' => $materials->where('is_active', 'Aktif')->count(),
            ],
        ];
    }

    /**
     * Get class analytics data.
     *
     * @return array<string, mixed>
     */
    private function getClassAnalyticsData(): array
    {
        $classes = \App\Models\ClassRoom::query()
            ->where('is_active', true)
            ->with(['students' => function ($q) {
                $q->with('learningStyleProfile', 'learningActivities');
            }])
            ->get()
            ->map(function ($class) {
                $students = $class->students;
                $totalStudents = $students->count();

                $styleDistribution = $students
                    ->filter(fn ($s) => $s->learningStyleProfile)
                    ->groupBy(fn ($s) => $s->learningStyleProfile->dominant_style)
                    ->map(fn ($group) => $group->count());

                $totalActivities = $students->sum(fn ($s) => $s->learningActivities->count());
                $totalMinutes = $students->sum(fn ($s) => $s->learningActivities->sum('duration_seconds') / 60);
                $avgMinutes = $totalStudents > 0 ? round($totalMinutes / $totalStudents) : 0;

                return [
                    'name' => $class->name,
                    'grade_level' => $class->grade_level,
                    'academic_year' => $class->academic_year,
                    'total_students' => $totalStudents,
                    'visual_count' => $styleDistribution->get('visual', 0),
                    'auditory_count' => $styleDistribution->get('auditory', 0),
                    'kinesthetic_count' => $styleDistribution->get('kinesthetic', 0),
                    'total_activities' => $totalActivities,
                    'avg_minutes_per_student' => $avgMinutes,
                ];
            });

        return [
            'title' => 'Laporan Analitik Kelas',
            'generated_at' => now()->format('d M Y H:i'),
            'classes' => $classes,
            'total_classes' => $classes->count(),
            'summary' => [
                'total_students' => $classes->sum('total_students'),
                'total_activities' => $classes->sum('total_activities'),
            ],
        ];
    }

    private function generatePdf(string $type, array $data): Response
    {
        $view = 'reports.'.$type;
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('a4', 'landscape');

        $filename = $type.'_report_'.now()->format('Y-m-d_His').'.pdf';

        return $pdf->download($filename);
    }

    private function generateCsv(string $type, array $data): StreamedResponse
    {
        $filename = $type.'_report_'.now()->format('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($type, $data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            match ($type) {
                'learning_styles' => $this->writeLearningStylesCsv($file, $data),
                'student_progress' => $this->writeStudentProgressCsv($file, $data),
                'material_usage' => $this->writeMaterialUsageCsv($file, $data),
                'class_analytics' => $this->writeClassAnalyticsCsv($file, $data),
                default => null,
            };

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Write learning styles data to CSV.
     *
     * @param  resource  $file
     * @param  array<string, mixed>  $data
     */
    private function writeLearningStylesCsv($file, array $data): void
    {
        fputcsv($file, ['Nama Siswa', 'Email', 'Gaya Dominan', 'Visual (%)', 'Auditori (%)', 'Kinestetik (%)', 'Tanggal Analisis']);

        foreach ($data['profiles'] as $profile) {
            fputcsv($file, [
                $profile['student_name'],
                $profile['email'],
                $profile['dominant_style'],
                $profile['visual_score'],
                $profile['auditory_score'],
                $profile['kinesthetic_score'],
                $profile['analyzed_at'],
            ]);
        }
    }

    /**
     * Write student progress data to CSV.
     *
     * @param  resource  $file
     * @param  array<string, mixed>  $data
     */
    private function writeStudentProgressCsv($file, array $data): void
    {
        fputcsv($file, ['Nama Siswa', 'Email', 'Gaya Belajar', 'Total Aktivitas', 'Aktivitas Selesai', 'Tingkat Penyelesaian (%)', 'Total Menit']);

        foreach ($data['students'] as $student) {
            fputcsv($file, [
                $student['name'],
                $student['email'],
                $student['learning_style'],
                $student['total_activities'],
                $student['completed_activities'],
                $student['completion_rate'],
                $student['total_minutes'],
            ]);
        }
    }

    /**
     * Write material usage data to CSV.
     *
     * @param  resource  $file
     * @param  array<string, mixed>  $data
     */
    private function writeMaterialUsageCsv($file, array $data): void
    {
        fputcsv($file, ['Judul Materi', 'Mata Pelajaran', 'Guru', 'Tipe', 'Gaya Belajar', 'Jumlah Penggunaan', 'Status']);

        foreach ($data['materials'] as $material) {
            fputcsv($file, [
                $material['title'],
                $material['subject'],
                $material['teacher'],
                $material['type'],
                $material['learning_style'],
                $material['usage_count'],
                $material['is_active'],
            ]);
        }
    }

    /**
     * Write class analytics data to CSV.
     *
     * @param  resource  $file
     * @param  array<string, mixed>  $data
     */
    private function writeClassAnalyticsCsv($file, array $data): void
    {
        fputcsv($file, ['Nama Kelas', 'Tingkat', 'Tahun Ajaran', 'Total Siswa', 'Visual', 'Auditori', 'Kinestetik', 'Total Aktivitas', 'Rata-rata Menit/Siswa']);

        foreach ($data['classes'] as $class) {
            fputcsv($file, [
                $class['name'],
                $class['grade_level'],
                $class['academic_year'],
                $class['total_students'],
                $class['visual_count'],
                $class['auditory_count'],
                $class['kinesthetic_count'],
                $class['total_activities'],
                $class['avg_minutes_per_student'],
            ]);
        }
    }

    private function getStyleLabel(string $style): string
    {
        return match ($style) {
            'visual' => 'Visual',
            'auditory' => 'Auditori',
            'kinesthetic' => 'Kinestetik',
            'all' => 'Semua',
            default => $style,
        };
    }

    private function getMaterialTypeLabel(string $type): string
    {
        return match ($type) {
            'video' => 'Video',
            'document' => 'Dokumen',
            'infographic' => 'Infografis',
            'audio' => 'Audio',
            'simulation' => 'Simulasi',
            default => $type,
        };
    }
}
