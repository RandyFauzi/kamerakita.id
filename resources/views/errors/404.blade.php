<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tidak Ditemukan - KameraKita</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Nunito', sans-serif;
            background-color: #f3f6fd;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #334155;
            text-align: center;
        }
        .container {
            max-width: 600px;
            padding: 50px 40px;
            background: white;
            border-radius: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .animation-container {
            width: 200px;
            height: 180px;
            margin: 0 auto 30px;
            position: relative;
        }

        .ghost {
            position: absolute;
            left: 50%;
            top: 40%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 90px;
            background: #3b82f6;
            border-radius: 40px 40px 0 0;
            animation: float-ghost 3s ease-in-out infinite;
        }
        
        .ghost::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 0;
            width: 100%;
            height: 15px;
            background: repeating-linear-gradient(
                -45deg,
                #3b82f6 0,
                #3b82f6 10px,
                transparent 10px,
                transparent 20px
            );
        }

        .eye {
            position: absolute;
            width: 12px;
            height: 12px;
            background: white;
            border-radius: 50%;
            top: 30px;
        }
        .eye.left { left: 20px; }
        .eye.right { right: 20px; }
        
        .mouth {
            position: absolute;
            width: 20px;
            height: 10px;
            border-radius: 0 0 20px 20px;
            background: white;
            top: 50px;
            left: 30px;
        }
        
        @keyframes float-ghost {
            0%, 100% { transform: translate(-50%, -50%) translateY(0); }
            50% { transform: translate(-50%, -50%) translateY(-15px); }
        }

        h1 {
            font-size: 2.2rem;
            color: #1e293b;
            margin: 0 0 15px 0;
            font-weight: 800;
        }
        p {
            font-size: 1.1rem;
            color: #64748b;
            margin-bottom: 35px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 14px 36px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }
        .btn:hover {
            background: #2563eb;
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(59, 130, 246, 0.4);
        }
        
        .error-code {
            position: absolute;
            top: -30px;
            left: 20px;
            font-size: 12rem;
            font-weight: 800;
            color: #f1f5f9;
            z-index: -1;
            line-height: 1;
            user-select: none;
        }
        
        .shadow {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 15px;
            background: rgba(0,0,0,0.05);
            border-radius: 50%;
            animation: shrink 3s ease-in-out infinite;
        }
        
        @keyframes shrink {
            0%, 100% { transform: translateX(-50%) scale(1); opacity: 0.5; }
            50% { transform: translateX(-50%) scale(0.6); opacity: 0.2; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">404</div>
        
        <div class="animation-container">
            <div class="ghost">
                <div class="eye left"></div>
                <div class="eye right"></div>
                <div class="mouth"></div>
            </div>
            <div class="shadow"></div>
        </div>

        <h1>Ups! Halaman Tidak Ditemukan</h1>
        <p>Maaf, halaman yang Anda cari sepertinya sudah dihapus,<br>pindah URL, atau memang tidak pernah ada.</p>
        
        <a href="{{ url('/') }}" class="btn">Kembali ke Beranda</a>
    </div>
</body>
</html>
