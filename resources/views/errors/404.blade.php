<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 HALAMAN TIDAK DITEMUKAN - KameraKita</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap');

        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #050505;
            color: #00ccff;
            font-family: 'Share Tech Mono', monospace;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            text-align: center;
            position: relative;
            z-index: 10;
            width: 90%;
            max-width: 800px;
            padding: 40px;
            border: 1px solid #00ccff;
            background: rgba(0, 10, 20, 0.85);
            box-shadow: 0 0 20px rgba(0, 204, 255, 0.4), inset 0 0 30px rgba(0, 204, 255, 0.2);
            animation: glitch-border 4s infinite;
        }

        @keyframes glitch-border {
            0% { border-color: #00ccff; box-shadow: 0 0 20px rgba(0, 204, 255, 0.4); }
            45% { border-color: #00ccff; box-shadow: 0 0 20px rgba(0, 204, 255, 0.4); }
            50% { border-color: #ffffff; box-shadow: 0 0 40px rgba(255, 255, 255, 0.8); }
            55% { border-color: #00ccff; box-shadow: 0 0 20px rgba(0, 204, 255, 0.4); }
            100% { border-color: #00ccff; box-shadow: 0 0 20px rgba(0, 204, 255, 0.4); }
        }

        h1 {
            font-size: 6rem;
            margin: 0;
            line-height: 1;
            text-shadow: 0 0 10px #00ccff;
            letter-spacing: 5px;
        }

        h2 {
            font-size: 2rem;
            margin: 10px 0 20px;
            color: #ffffff;
            text-shadow: 0 0 8px #ffffff;
        }

        p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .scanlines {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: linear-gradient(
                to bottom,
                rgba(255,255,255,0),
                rgba(255,255,255,0) 50%,
                rgba(0,0,0,0.2) 50%,
                rgba(0,0,0,0.2)
            );
            background-size: 100% 4px;
            pointer-events: none;
            z-index: 50;
        }

        a.btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background-color: transparent;
            color: #00ccff;
            border: 2px solid #00ccff;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: bold;
            transition: all 0.2s;
            cursor: pointer;
        }

        a.btn:hover {
            background-color: #00ccff;
            color: #000;
            box-shadow: 0 0 15px #00ccff;
        }
        
        .radar {
            width: 100px;
            height: 100px;
            border: 2px solid #00ccff;
            border-radius: 50%;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
        }
        
        .radar::before {
            content: '';
            position: absolute;
            top: 0; left: 50%;
            width: 50%; height: 50%;
            background: linear-gradient(45deg, transparent, rgba(0,204,255,0.5));
            transform-origin: bottom left;
            animation: scan 2s linear infinite;
        }
        
        @keyframes scan {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="scanlines"></div>

    <div class="container">
        <h1>404</h1>
        <h2>SEKTOR TIDAK DITEMUKAN</h2>
        
        <div class="radar"></div>

        <p style="color: #fff;">>> Memindai koordinat server...</p>
        <p>Halaman yang Anda tuju telah dihapus, pindah dimensi, atau memang tidak pernah ada. <br>Navigasi sistem gagal menemukan target: <strong style="color: #ff3333;">{{ request()->path() }}</strong></p>

        <a href="{{ url('/') }}" class="btn">KEMBALI KE BASE (BERANDA)</a>
    </div>
</body>
</html>
