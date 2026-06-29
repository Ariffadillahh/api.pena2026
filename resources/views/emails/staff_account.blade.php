<!DOCTYPE html>
<html>

<head>
    <title>Akun PENA 2026</title>
</head>

<body style="font-family: monospace; background-color: #f4f4f5; padding: 20px;">
    <div style="background-color: #ffffff; border: 4px solid #01244A; padding: 20px; max-width: 500px; margin: 0 auto;">
        <h2 style="color: #01244A; text-transform: uppercase;">Selamat Datang di Kepanitiaan PENA 2026</h2>
        <p>Halo, Anda telah ditambahkan sebagai <strong>{{ $role }}</strong>.</p>
        <p>Berikut adalah akses login Anda untuk masuk ke Dashboard Admin:</p>
        <div
            style="background-color: #F1C40F; padding: 15px; border: 2px solid #01244A; font-weight: bold; margin: 20px 0;">
            <p style="margin: 0;">Email: {{ $email }}</p>
            <p style="margin: 0;">Password: {{ $password }}</p>
        </div>
        <p style="color: red; font-size: 12px;">*Harap segera ubah password Anda setelah berhasil login pertama kali.</p>
        <br>
        <p>Salam hangat,<br>Project Officer PENA 2026</p>
    </div>
</body>

</html>
