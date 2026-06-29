<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Juri PENA 2026</title>
    <style>
        /* CSS Reset untuk Email */
        body,
        table,
        td,
        div,
        p,
        a {
            font-family: Arial, Helvetica, sans-serif;
            text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f5;
        }

        /* Utilitas Neo-Brutalism */
        .wrapper {
            width: 100%;
            background-color: #f4f4f5;
            padding: 40px 20px;
        }

        .card {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 4px solid #01244A;
            /* Box shadow standar, aman di sebagian besar webmail modern */
            box-shadow: 8px 8px 0px 0px #01244A;
        }

        .header {
            background-color: #F1C40F;
            border-bottom: 4px solid #01244A;
            padding: 25px;
            text-align: center;
        }

        .header h1 {
            color: #01244A;
            margin: 0;
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .body-content {
            padding: 30px;
            color: #333333;
            line-height: 1.6;
            font-size: 16px;
        }

        .credentials-box {
            background-color: #f0f4f8;
            border: 2px dashed #01244A;
            padding: 20px;
            margin: 25px 0;
        }

        .credentials-box p {
            margin: 5px 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 16px;
            color: #01244A;
        }

        .btn-container {
            text-align: center;
            margin: 35px 0 20px 0;
        }

        .btn {
            display: inline-block;
            background-color: #F1C40F;
            color: #01244A;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            padding: 14px 28px;
            border: 3px solid #01244A;
            box-shadow: 4px 4px 0px 0px #01244A;
            text-transform: uppercase;
        }

        .footer {
            background-color: #01244A;
            color: #ffffff;
            text-align: center;
            padding: 20px;
            font-size: 13px;
        }

        .warning-text {
            font-size: 13px;
            color: #dc2626;
            font-style: italic;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>PANEL JURI PENA 2026</h1>
            </div>

            <div class="body-content">
                <p>Yth. Bapak/Ibu <strong>{{ $user->name }}</strong>,</p>

                <p>Anda telah ditambahkan sebagai Juri untuk kompetisi di <strong>Pekan Edukasi dan Penalaran (PENA)
                        2026</strong>. Berikut adalah detail akun Anda untuk masuk ke sistem penilaian kami:</p>

                <div class="credentials-box">
                    <p><strong>Email Akses :</strong> {{ $user->email }}</p>
                    <p><strong>Password :</strong> {{ $rawPassword }}</p>
                </div>

                <p class="warning-text">
                    *Harap segera ubah password Anda setelah berhasil login pertama kali demi keamanan akun dan data
                    penilaian.
                </p>

                <div class="btn-container">
                    <a href="{{ $loginUrl }}" class="btn">Login ke Panel Juri</a>
                </div>

                <p style="margin-top: 30px;">
                    Terima kasih atas kontribusi dan dedikasi Anda.<br>
                    <strong>Panitia PENA 2026</strong>
                </p>
            </div>

            <div class="footer">
                &copy; {{ date('Y') }} Pekan Edukasi dan Penalaran. Politeknik Negeri Jakarta.
            </div>
        </div>
    </div>
</body>

</html>
