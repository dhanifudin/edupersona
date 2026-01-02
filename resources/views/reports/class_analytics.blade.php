<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4f46e5;
        }
        .header h1 {
            font-size: 18px;
            color: #4f46e5;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .summary-grid {
            margin-bottom: 20px;
        }
        .summary-item {
            display: inline-block;
            padding: 10px 15px;
            background: #f3f4f6;
            margin-right: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .summary-item strong {
            display: block;
            font-size: 14px;
            color: #4f46e5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background: #4f46e5;
            color: white;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .style-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            margin-right: 2px;
            color: white;
        }
        .style-visual { background: #3b82f6; }
        .style-auditory { background: #10b981; }
        .style-kinesthetic { background: #f97316; }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>EduPersona.ai - Platform Pembelajaran Personalisasi</p>
        <p>Dibuat pada: {{ $generated_at }}</p>
    </div>

    <div class="summary-grid">
        <div class="summary-item">
            <strong>{{ $total_classes }}</strong>
            Total Kelas Aktif
        </div>
        <div class="summary-item">
            <strong>{{ $summary['total_students'] }}</strong>
            Total Siswa
        </div>
        <div class="summary-item">
            <strong>{{ $summary['total_activities'] }}</strong>
            Total Aktivitas
        </div>
    </div>

    <h2 style="font-size: 12px; margin-bottom: 10px;">Analitik per Kelas</h2>
    <table>
        <thead>
            <tr>
                <th>Nama Kelas</th>
                <th>Tingkat</th>
                <th>Tahun Ajaran</th>
                <th>Siswa</th>
                <th>Distribusi Gaya Belajar</th>
                <th>Aktivitas</th>
                <th>Rata-rata Menit/Siswa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($classes as $class)
                <tr>
                    <td>{{ $class['name'] }}</td>
                    <td>{{ $class['grade_level'] }}</td>
                    <td>{{ $class['academic_year'] }}</td>
                    <td>{{ $class['total_students'] }}</td>
                    <td>
                        @if($class['visual_count'] > 0)
                            <span class="style-badge style-visual">V: {{ $class['visual_count'] }}</span>
                        @endif
                        @if($class['auditory_count'] > 0)
                            <span class="style-badge style-auditory">A: {{ $class['auditory_count'] }}</span>
                        @endif
                        @if($class['kinesthetic_count'] > 0)
                            <span class="style-badge style-kinesthetic">K: {{ $class['kinesthetic_count'] }}</span>
                        @endif
                    </td>
                    <td>{{ $class['total_activities'] }}</td>
                    <td>{{ $class['avg_minutes_per_student'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem EduPersona.ai</p>
    </div>
</body>
</html>
