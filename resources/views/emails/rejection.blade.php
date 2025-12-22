<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Kuesioner Ditolak</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 30px;
        }
        h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pendaftaran Kuesioner Ditolak</h1>

        <p>Halo <strong>{{ $name }}</strong>,</p>
        <p>Mohon maaf, permohonan Anda untuk pengisian kuesioner <strong>belum dapat kami proses</strong> saat ini.</p>

        @if (!empty($notes))
            <p><strong>Alasan penolakan:</strong></p>
            <p>{{ $notes }}</p>
        @endif

        <p>Silakan periksa kembali data yang Anda kirimkan atau hubungi admin bila membutuhkan bantuan lebih lanjut.</p>

        <div class="footer">
            <p>Terima kasih,<br>
            {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
