<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email</title>
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
        <h1>Verifikasi Email Anda</h1>
        
        <p>Terima kasih telah mendaftar. Silakan verifikasi email Anda untuk melanjutkan pengisian kuesioner.</p>
        
        <a href="{{ $verificationUrl }}" class="button">Verifikasi Email</a>
        
        <p>Atau salin tautan berikut dan buka di browser Anda:</p>
        <p class="link-text">{{ $verificationUrl }}</p>
        
        <p>Jika Anda tidak merasa melakukan pendaftaran ini, abaikan email ini.</p>
        
        <div class="footer">
            <p>Terima kasih,<br>
            {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
