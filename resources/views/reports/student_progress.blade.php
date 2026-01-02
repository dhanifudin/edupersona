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
        .date-range {
            background: #f3f4f6;
            padding: 8px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 10px;
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
        .progress-bar {
            background: #e5e7eb;
            border-radius: 4px;
            height: 8px;
            width: 60px;
            display: inline-block;
            overflow: hidden;
        }
        .progress-fill {
            background: #4f46e5;
            height: 100%;
        }
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

    @if($date_from || $date_to)
        <div class="date-range">
            Periode: {{ $date_from ?? 'Semua' }} - {{ $date_to ?? 'Sekarang' }}
        </div>
    @endif

    <div class="summary-grid">
        <div class="summary-item">
            <strong>{{ $total_students }}</strong>
            Total Siswa
        </div>
        <div class="summary-item">
            <strong>{{ $summary['avg_completion_rate'] }}%</strong>
            Rata-rata Penyelesaian
        </div>
        <div class="summary-item">
            <strong>{{ $summary['total_activities'] }}</strong>
            Total Aktivitas
        </div>
        <div class="summary-item">
            <strong>{{ number_format($summary['total_minutes'] / 60, 1) }} jam</strong>
            Total Waktu Belajar
        </div>
    </div>

    <h2 style="font-size: 12px; margin-bottom: 10px;">Detail Kemajuan Siswa</h2>
    <table>
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>Email</th>
                <th>Gaya Belajar</th>
                <th>Total Aktivitas</th>
                <th>Selesai</th>
                <th>Penyelesaian</th>
                <th>Total Menit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
                <tr>
                    <td>{{ $student['name'] }}</td>
                    <td>{{ $student['email'] }}</td>
                    <td>{{ $student['learning_style'] }}</td>
                    <td>{{ $student['total_activities'] }}</td>
                    <td>{{ $student['completed_activities'] }}</td>
                    <td>{{ $student['completion_rate'] }}%</td>
                    <td>{{ $student['total_minutes'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem EduPersona.ai</p>
    </div>
</body>
</html>
