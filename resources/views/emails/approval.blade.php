<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Kuesioner Disetujui</title>
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
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #3490dc;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #2779bd;
        }
        .link-text {
            word-break: break-all;
            color: #3490dc;
            margin: 15px 0;
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
        <h1>Pendaftaran Kuesioner Disetujui</h1>

        <p>Halo <strong>{{ $name }}</strong>,</p>
        <p>Permohonan Anda untuk mengikuti pengisian kuesioner telah <strong>disetujui</strong>.</p>

        <a href="{{ $link }}" class="button" target="_blank" rel="noopener">Mulai Isi Kuesioner</a>

        <p>Jika tombol tidak berfungsi, salin dan buka tautan berikut:</p>
        <p class="link-text">{{ $link }}</p>

        <div class="footer">
            <p>Terima kasih,<br>
            {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
