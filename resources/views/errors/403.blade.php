<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AKSES DITOLAK - KameraKita</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap');

        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #050505;
            color: #ff3333;
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
            max-width: 800px;
            padding: 40px;
            border: 1px solid #ff3333;
            background: rgba(20, 0, 0, 0.85);
            box-shadow: 0 0 20px rgba(255, 0, 0, 0.4), inset 0 0 30px rgba(255, 0, 0, 0.2);
            animation: glitch-border 3s infinite;
        }

        @keyframes glitch-border {
            0% { border-color: #ff3333; box-shadow: 0 0 20px rgba(255, 0, 0, 0.4); }
            45% { border-color: #ff3333; box-shadow: 0 0 20px rgba(255, 0, 0, 0.4); }
            50% { border-color: #ffffff; box-shadow: 0 0 40px rgba(255, 255, 255, 0.8); }
            55% { border-color: #ff3333; box-shadow: 0 0 20px rgba(255, 0, 0, 0.4); }
            100% { border-color: #ff3333; box-shadow: 0 0 20px rgba(255, 0, 0, 0.4); }
        }

        h1 {
            font-size: 5rem;
            margin: 0;
            line-height: 1;
            text-shadow: 0 0 10px #ff0000;
            letter-spacing: 5px;
        }

        h2 {
            font-size: 2rem;
            margin: 10px 0 30px;
            color: #ffffff;
            text-shadow: 0 0 8px #ffffff;
        }

        p {
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .warning-box {
            border-top: 1px dashed #ff3333;
            border-bottom: 1px dashed #ff3333;
            padding: 20px;
            margin: 30px 0;
            background: rgba(255, 0, 0, 0.05);
            text-align: left;
        }

        .code-line {
            display: block;
            margin-bottom: 8px;
            font-size: 1rem;
            color: #00ff00;
        }

        .code-line.red {
            color: #ff3333;
            font-weight: bold;
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
            color: #ff3333;
            border: 2px solid #ff3333;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: bold;
            transition: all 0.2s;
            cursor: pointer;
        }

        a.btn:hover {
            background-color: #ff3333;
            color: #000;
            box-shadow: 0 0 15px #ff3333;
        }

        /* Typewriter effect */
        .typewriter {
            overflow: hidden;
            border-right: .15em solid #ff3333;
            white-space: nowrap;
            margin: 0 auto;
            letter-spacing: .15em;
            animation: 
                typing 3.5s steps(40, end),
                blink-caret .75s step-end infinite;
        }

        @keyframes typing {
            from { width: 0 }
            to { width: 100% }
        }

        @keyframes blink-caret {
            from, to { border-color: transparent }
            50% { border-color: #ff3333; }
        }
    </style>
</head>
<body>
    <div class="scanlines"></div>

    <div class="container">
        <h1>403</h1>
        <h2>AKSES ILEGAL TERDETEKSI</h2>
        
        <p class="typewriter">>> Menganalisis aktivitas mencurigakan...</p>

        <div class="warning-box">
            <span class="code-line">IP Address : {{ request()->ip() }}</span>
            <span class="code-line">User Agent : {{ request()->userAgent() }}</span>
            <span class="code-line">Timestamp  : {{ now()->toDateTimeString() }}</span>
            <span class="code-line">Target URL : {{ request()->fullUrl() }}</span>
            <span class="code-line red">STATUS     : SIGNATURE INVALID / DITOLAK</span>
            <span class="code-line red">> PERINGATAN: Upaya manipulasi URL atau pembobolan data sedang dicatat!</span>
        </div>

        <p>Anda mencoba mengakses halaman atau file rahasia dengan tautan yang tidak sah (Invalid Signature).</p>
        <p>Segala bentuk percobaan akses ilegal, pencurian data, atau manipulasi sistem akan kami pantau dan <strong>dapat dilaporkan kepada pihak berwajib berdasarkan UU ITE Pasal 30 ayat (1), (2), dan (3).</strong></p>
        
        <a href="{{ url('/') }}" class="btn">TUTUP HALAMAN INI</a>
    </div>

    <script>
        // Add a slight flickering effect
        setInterval(() => {
            if (Math.random() > 0.95) {
                document.body.style.filter = 'invert(1)';
                setTimeout(() => {
                    document.body.style.filter = 'invert(0)';
                }, 100);
            }
        }, 1000);
    </script>
</body>
</html>
