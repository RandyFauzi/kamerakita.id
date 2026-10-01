<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 KESALAHAN SERVER - KameraKita</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap');

        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #050505;
            color: #ff9900;
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
            border: 1px solid #ff9900;
            background: rgba(20, 10, 0, 0.85);
            box-shadow: 0 0 20px rgba(255, 153, 0, 0.4), inset 0 0 30px rgba(255, 153, 0, 0.2);
            animation: glitch-border 2s infinite;
        }

        @keyframes glitch-border {
            0% { border-color: #ff9900; box-shadow: 0 0 20px rgba(255, 153, 0, 0.4); }
            45% { border-color: #ff9900; box-shadow: 0 0 20px rgba(255, 153, 0, 0.4); }
            50% { border-color: #ffffff; box-shadow: 0 0 40px rgba(255, 255, 255, 0.8); }
            55% { border-color: #ff3333; box-shadow: 0 0 20px rgba(255, 51, 51, 0.4); }
            100% { border-color: #ff9900; box-shadow: 0 0 20px rgba(255, 153, 0, 0.4); }
        }

        h1 {
            font-size: 6rem;
            margin: 0;
            line-height: 1;
            text-shadow: 0 0 10px #ff9900;
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
            color: #ff9900;
            border: 2px solid #ff9900;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: bold;
            transition: all 0.2s;
            cursor: pointer;
        }

        a.btn:hover {
            background-color: #ff9900;
            color: #000;
            box-shadow: 0 0 15px #ff9900;
        }
        
        .error-log {
            text-align: left;
            background: #000;
            border: 1px dashed #ff3333;
            padding: 15px;
            color: #ff3333;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="scanlines"></div>

    <div class="container">
        <h1>500</h1>
        <h2>SISTEM MENGALAMI KESALAHAN FATAL</h2>
        
        <div class="error-log">
            > KERNEL PANIC: Unhandled Exception<br>
            > MEMORY DUMP: Completed<br>
            > STATUS: Menunggu perbaikan teknisi
        </div>

        <p style="color: #fff;">>> Server kami mengalami malfungsi teknis.</p>
        <p>Jangan panik, tim engineer kami (atau log sistem) sedang mencatat kejadian ini untuk segera diperbaiki. Silakan coba kembali dalam beberapa menit.</p>

        <a href="{{ url('/') }}" class="btn">REBOOT KONEKSI (BERANDA)</a>
    </div>
</body>
</html>
