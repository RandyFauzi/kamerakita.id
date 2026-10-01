<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Sedang Sibuk - KameraKita</title>
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
        
        /* Modern Gear Animation CSS */
        .gear-wrapper {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .gear {
            position: absolute;
            background: #3b82f6;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .gear::before {
            content: "";
            position: absolute;
            width: 100px;
            height: 100px;
            border: 12px dashed #3b82f6;
            border-radius: 50%;
            box-sizing: border-box;
        }
        .gear::after {
            content: "";
            position: absolute;
            width: 30px;
            height: 30px;
            background: white;
            border-radius: 50%;
        }
        .gear-1 {
            top: 20px;
            left: 30px;
            animation: spin 6s linear infinite;
        }
        .gear-2 {
            bottom: 20px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: #f59e0b;
            animation: spin-reverse 4s linear infinite;
        }
        .gear-2::before {
            width: 76px;
            height: 76px;
            border-color: #f59e0b;
            border-width: 10px;
        }
        .gear-2::after {
            width: 24px;
            height: 24px;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        @keyframes spin-reverse { 100% { transform: rotate(-360deg); } }
        
        .floating {
            animation: float 4s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
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
        
        /* Big background text */
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
        
        .sparks {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #f59e0b;
            border-radius: 50%;
            top: 50%;
            left: 50%;
            opacity: 0;
            animation: pop 2s ease-out infinite;
        }
        .spark-1 { transform: translate(-80px, -60px); animation-delay: 0.2s; }
        .spark-2 { transform: translate(70px, -40px); animation-delay: 0.9s; background: #3b82f6;}
        .spark-3 { transform: translate(-40px, 70px); animation-delay: 1.5s; }
        
        @keyframes pop {
            0% { opacity: 1; transform: scale(1) translate(var(--tx), var(--ty)); }
            100% { opacity: 0; transform: scale(0) translate(var(--tx), var(--ty)); }
        }
    </style>
</head>
<body>
    <div class="container floating">
        <div class="error-code">500</div>
        
        <div class="animation-container">
            <div class="gear gear-1"></div>
            <div class="gear gear-2"></div>
            
            <div class="sparks spark-1" style="--tx: -50px; --ty: -50px;"></div>
            <div class="sparks spark-2" style="--tx: 50px; --ty: -20px;"></div>
            <div class="sparks spark-3" style="--tx: -20px; --ty: 50px;"></div>
        </div>

        <h1>Ups! Server Sedang Berbenah</h1>
        <p>Maaf, sepertinya ada sedikit kendala teknis di sistem kami.<br>Tim engineer terbaik kami sedang memperbaikinya sekarang juga!</p>
        
        <a href="{{ url('/') }}" class="btn">Kembali ke Beranda</a>
    </div>
</body>
</html>
