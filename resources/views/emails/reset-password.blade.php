<!DOCTYPE html>
<html>

<body
    style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #01244A; margin-bottom: 5px;">PENA 2026</h1>
        <p style="color: #666; margin-top: 0; font-size: 14px;">Pekan Edukasi dan Penalaran</p>
    </div>

    <p>Halo <strong>{{ $user->name }}</strong>,</p>

    <p>Kami menerima permintaan untuk mengatur ulang password akun Anda di sistem PENA 2026. Silakan klik tombol di
        bawah ini untuk membuat password baru:</p>

    <div style="text-align: center; margin: 35px 0;">
        <a href="{{ env('FRONTEND_URL') }}/auth/reset-password?email={{ urlencode($user->email) }}&token={{ $token }}"
            style="background-color: #F1C40F; color: #01244A; padding: 14px 28px; text-decoration: none; font-weight: bold; font-size: 16px; border: 2px solid #01244A; border-radius: 4px; display: inline-block;">
            Atur Ulang Password
        </a>
    </div>

    <p style="font-size: 14px;">Jika tombol di atas tidak berfungsi, Anda juga bisa menyalin dan menempelkan tautan
        berikut secara manual ke browser Anda:</p>

    <div
        style="background-color: #f4f4f5; padding: 15px; border-left: 4px solid #01244A; word-break: break-all; font-size: 13px; color: #555;">
        {{ env('FRONTEND_URL') }}/auth/reset-password?email={{ urlencode($user->email) }}&token={{ $token }}
    </div>

    <p style="margin-top: 30px; font-size: 14px; color: #e74c3c;">
        <strong>Perhatian:</strong> Tautan ini bersifat rahasia dan hanya berlaku selama <strong>15 menit</strong>. Jika
        Anda tidak pernah meminta reset password, mohon abaikan email ini.
    </p>

    <hr style="border: none; border-top: 1px dashed #ccc; margin: 30px 0;">

    <p style="font-size: 13px; color: #777;">
        Salam Inovasi,<br>
        <strong>Panitia PENA 2026</strong>
    </p>

</body>

</html>
