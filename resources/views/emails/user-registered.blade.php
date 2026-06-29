<!DOCTYPE html>
<html>

<body
    style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #01244A;">PENA 2026</h1>
    </div>

    <p>Halo <strong>{{ $user->name }}</strong>,</p>

    <p>Terima kasih telah mendaftar di ajang kompetisi PENA 2026. Untuk melanjutkan proses registrasi, silakan gunakan
        kode verifikasi di bawah ini:</p>

    <div
        style="background-color: #F1C40F; padding: 20px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #01244A; margin: 30px 0;">
        {{ $otp }}
    </div>

    <p>Kode ini berlaku untuk 5 menit ke depan. Jangan berikan kode ini kepada siapapun.</p>

    <p>Salam Inovasi,<br><strong>Panitia PENA 2026</strong></p>
</body>

</html>
