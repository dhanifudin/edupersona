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
        .by-type {
            margin-bottom: 20px;
        }
        .by-type h2 {
            font-size: 12px;
            margin-bottom: 10px;
            color: #374151;
        }
        .type-item {
            display: inline-block;
            padding: 5px 15px;
            margin-right: 10px;
            margin-bottom: 5px;
            background: #f3f4f6;
            border-radius: 4px;
            font-size: 10px;
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
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 9px;
        }
        .badge-active { background: #10b981; color: white; }
        .badge-inactive { background: #6b7280; color: white; }
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
            <strong>{{ $total_materials }}</strong>
            Total Materi
        </div>
        <div class="summary-item">
            <strong>{{ $summary['active_materials'] }}</strong>
            Materi Aktif
        </div>
        <div class="summary-item">
            <strong>{{ $summary['total_usage'] }}</strong>
            Total Penggunaan
        </div>
    </div>

    @if($by_type->count() > 0)
        <div class="by-type">
            <h2>Penggunaan per Tipe Materi</h2>
            @foreach($by_type as $type)
                <span class="type-item">
                    {{ ucfirst($type->type) }}: {{ $type->count }} kali
                </span>
            @endforeach
        </div>
    @endif

    <h2 style="font-size: 12px; margin-bottom: 10px;">Detail Penggunaan Materi</h2>
    <table>
        <thead>
            <tr>
                <th>Judul Materi</th>
                <th>Mata Pelajaran</th>
                <th>Guru</th>
                <th>Tipe</th>
                <th>Gaya Belajar</th>
                <th>Penggunaan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($materials as $material)
                <tr>
                    <td>{{ $material['title'] }}</td>
                    <td>{{ $material['subject'] }}</td>
                    <td>{{ $material['teacher'] }}</td>
                    <td>{{ $material['type'] }}</td>
                    <td>{{ $material['learning_style'] }}</td>
                    <td>{{ $material['usage_count'] }}</td>
                    <td>
                        <span class="badge {{ $material['is_active'] === 'Aktif' ? 'badge-active' : 'badge-inactive' }}">
                            {{ $material['is_active'] }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem EduPersona.ai</p>
    </div>
</body>
</html>
