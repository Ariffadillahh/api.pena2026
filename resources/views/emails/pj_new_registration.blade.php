<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: 4px solid #01244A;
        }

        .header {
            background-color: #f9f9f9;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
        }

        .content {
            padding: 20px 0;
        }

        .details {
            background-color: #f1f5f9;
            padding: 15px;
            border-left: 4px solid #F1C40F;
            margin: 15px 0;
        }

        .footer {
            font-size: 12px;
            color: #777;
            text-align: center;
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            Notifikasi Pendaftar Baru - PENA 2026
        </div>

        <div class="content">
            <p>Halo Panitia (PJ Lomba),</p>
            <p>Sistem mendeteksi ada satu tim baru yang telah menyelesaikan proses pendaftaran dan mengunggah bukti
                pembayaran pada arena lomba <strong>{{ $competition->title }}</strong>.</p>

            <div class="details">
                <strong>Detail Tim:</strong><br>
                Nama Tim : {{ $team->name }}<br>
                Instansi : {{ $team->institution }}<br>
                Waktu Submit : {{ $team->updated_at->format('d M Y, H:i') }} WIB
            </div>

            <p>Mohon segera login ke Dashboard Admin PENA 2026 untuk melakukan pengecekan berkas dan verifikasi
                pembayaran tim tersebut.</p>

            <p>Semangat bertugas!<br>
                <strong>Sistem PENA 2026</strong>
            </p>
        </div>

        <div class="footer">
            Email ini di-generate otomatis oleh sistem. Mohon tidak membalas email ini.
        </div>
    </div>
</body>

</html>
