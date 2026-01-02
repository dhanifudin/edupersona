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
        .summary {
            display: flex;
            margin-bottom: 20px;
        }
        .summary-item {
            display: inline-block;
            padding: 10px 20px;
            background: #f3f4f6;
            margin-right: 10px;
            border-radius: 4px;
        }
        .summary-item strong {
            display: block;
            font-size: 16px;
            color: #4f46e5;
        }
        .distribution {
            margin-bottom: 20px;
        }
        .distribution h2 {
            font-size: 12px;
            margin-bottom: 10px;
            color: #374151;
        }
        .distribution-item {
            display: inline-block;
            padding: 5px 15px;
            margin-right: 10px;
            margin-bottom: 5px;
            border-radius: 4px;
            color: white;
            font-size: 10px;
        }
        .visual { background: #3b82f6; }
        .auditory { background: #10b981; }
        .kinesthetic { background: #f97316; }
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

    <div class="summary">
        <div class="summary-item">
            <strong>{{ $total_students }}</strong>
            Total Siswa
        </div>
    </div>

    <div class="distribution">
        <h2>Distribusi Gaya Belajar</h2>
        @foreach($distribution as $item)
            <span class="distribution-item {{ $item->dominant_style }}">
                {{ ucfirst($item->dominant_style) }}: {{ $item->count }} siswa
            </span>
        @endforeach
    </div>

    <h2 style="font-size: 12px; margin-bottom: 10px;">Detail Profil Gaya Belajar</h2>
    <table>
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>Email</th>
                <th>Gaya Dominan</th>
                <th>Visual (%)</th>
                <th>Auditori (%)</th>
                <th>Kinestetik (%)</th>
                <th>Tanggal Analisis</th>
            </tr>
        </thead>
        <tbody>
            @foreach($profiles as $profile)
                <tr>
                    <td>{{ $profile['student_name'] }}</td>
                    <td>{{ $profile['email'] }}</td>
                    <td>{{ $profile['dominant_style'] }}</td>
                    <td>{{ $profile['visual_score'] }}</td>
                    <td>{{ $profile['auditory_score'] }}</td>
                    <td>{{ $profile['kinesthetic_score'] }}</td>
                    <td>{{ $profile['analyzed_at'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem EduPersona.ai</p>
    </div>
</body>
</html>
