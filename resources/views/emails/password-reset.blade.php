<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Password Anda</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 20px;">
    <div style="max-w: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="color: #1f2937; margin-top: 0;">Halo, {{ $user->name ?? 'Pengguna' }}!</h2>
        
        <p style="color: #4b5563; font-size: 16px; line-height: 1.5;">
            Kabar baik! Permintaan pemulihan kata sandi (Lupa Password) Anda telah disetujui oleh Administrator kami.
        </p>
        
        <p style="color: #4b5563; font-size: 16px; line-height: 1.5;">
            Silakan klik tombol di bawah ini untuk mengatur kata sandi Anda yang baru. Tautan ini hanya berlaku untuk satu kali penggunaan dan akan kedaluwarsa dalam 30 menit.
        </p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('password.reset', ['token' => $token, 'email' => $email]) }}" 
               style="background-color: #4f46e5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">
                Reset Password Sekarang
            </a>
        </div>
        
        <p style="color: #4b5563; font-size: 14px; line-height: 1.5; border-top: 1px solid #e5e7eb; padding-top: 20px;">
            Jika Anda tidak merasa meminta perubahan kata sandi ini, silakan abaikan email ini atau hubungi Admin secepatnya.
            <br><br>
            Salam hangat,<br>
            Tim KameraKita AI
        </p>
    </div>
</body>
</html>
