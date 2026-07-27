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
            width: 90%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 40px;
            border: 1px solid #ff3333;
            background: rgba(20, 0, 0, 0.85);
            box-shadow: 0 0 20px rgba(255, 0, 0, 0.4), inset 0 0 30px rgba(255, 0, 0, 0.2);
            animation: glitch-border 3s infinite;
        }

        /* Custom Scrollbar for container */
        .container::-webkit-scrollbar {
            width: 8px;
        }
        .container::-webkit-scrollbar-track {
            background: rgba(255, 0, 0, 0.1); 
        }
        .container::-webkit-scrollbar-thumb {
            background: #ff3333; 
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
            margin: 10px 0 20px;
            color: #ffffff;
            text-shadow: 0 0 8px #ffffff;
        }

        p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .data-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
            text-align: left;
        }

        @media (max-width: 768px) {
            .data-grid {
                grid-template-columns: 1fr;
            }
        }

        .warning-box {
            border: 1px dashed #ff3333;
            padding: 15px;
            background: rgba(255, 0, 0, 0.05);
            font-size: 0.95rem;
        }

        .warning-box h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #fff;
            border-bottom: 1px solid #ff3333;
            padding-bottom: 5px;
        }

        .code-line {
            display: block;
            margin-bottom: 8px;
            color: #00ff00;
            word-break: break-all;
        }

        .code-line span.label {
            color: #ff9999;
            display: inline-block;
            width: 120px;
        }

        .code-line.red {
            color: #ff3333;
            font-weight: bold;
            margin-top: 15px;
        }

        /* Fake Terminal */
        .terminal-box {
            border: 1px solid #00ff00;
            background: rgba(0, 20, 0, 0.9);
            padding: 15px;
            text-align: left;
            height: 150px;
            overflow: hidden;
            position: relative;
            margin-top: 20px;
        }
        
        .terminal-content {
            color: #00ff00;
            font-size: 0.9rem;
            line-height: 1.5;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            height: 100%;
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
    </style>
</head>
<body>
    <div class="scanlines"></div>

    <div class="container">
        <h1>403</h1>
        <h2>AKSES ILEGAL TERDETEKSI</h2>
        
        <p style="color: #fff;">>> Mengumpulkan data jejak digital pelaku...</p>

        <div class="data-grid">
            <!-- Server Data -->
            <div class="warning-box">
                <h3>[ SERVER DATA ]</h3>
                <span class="code-line"><span class="label">IP Address</span>: {{ request()->ip() }}</span>
                <span class="code-line"><span class="label">Timestamp</span>: <span id="clock">{{ now()->toDateTimeString() }}</span></span>
                <span class="code-line"><span class="label">Target URL</span>: {{ request()->fullUrl() }}</span>
                <span class="code-line red">> STATUS: SIGNATURE INVALID / DITOLAK</span>
            </div>

            <!-- Client Hardware & Network -->
            <div class="warning-box">
                <h3>[ DEVICE FINGERPRINTING ]</h3>
                <span class="code-line"><span class="label">Platform/OS</span>: <span id="client-os">Scanning...</span></span>
                <span class="code-line"><span class="label">CPU Cores</span>: <span id="client-cores">Scanning...</span></span>
                <span class="code-line"><span class="label">Screen Res</span>: <span id="client-screen">Scanning...</span></span>
                <span class="code-line"><span class="label">Battery Status</span>: <span id="client-battery">Scanning...</span></span>
                <span class="code-line"><span class="label">Network</span>: <span id="client-network">Scanning...</span></span>
            </div>
        </div>

        <!-- GeoLocation Data -->
        <div class="warning-box">
            <h3>[ GEOLOCATION TRACE ]</h3>
            <span class="code-line"><span class="label">ISP Provider</span>: <span id="geo-isp">Tracing...</span></span>
            <span class="code-line"><span class="label">City/Region</span>: <span id="geo-city">Tracing...</span></span>
            <span class="code-line"><span class="label">Country</span>: <span id="geo-country">Tracing...</span></span>
            <span class="code-line"><span class="label">Coordinates</span>: <span id="geo-latlon">Tracing...</span></span>
        </div>

        <!-- Fake Terminal Animation -->
        <div class="terminal-box">
            <div class="terminal-content" id="terminal-content">
                <!-- Terminal lines will be injected here -->
            </div>
        </div>

        <p style="margin-top: 30px;">Anda mencoba mengakses file dengan otentikasi palsu.</p>
        <p>Segala bentuk percobaan akses ilegal, pencurian data, atau manipulasi sistem sedang dicatat dan <strong>dapat dilaporkan kepada pihak berwajib berdasarkan UU ITE Pasal 30 ayat (1), (2), dan (3).</strong></p>
        
        <a href="{{ url('/') }}" class="btn">TUTUP HALAMAN INI SEGERA</a>
    </div>

    <script>
        // 1. Hardware & Screen Fingerprinting
        document.getElementById('client-os').innerText = navigator.platform || navigator.userAgentData?.platform || 'Unknown';
        document.getElementById('client-cores').innerText = navigator.hardwareConcurrency ? navigator.hardwareConcurrency + ' Logical Cores' : 'Unknown';
        document.getElementById('client-screen').innerText = window.screen.width + 'x' + window.screen.height + ' (' + window.screen.colorDepth + ' bit)';
        
        // 2. Battery Status (If supported)
        if ('getBattery' in navigator) {
            navigator.getBattery().then(function(battery) {
                const level = Math.round(battery.level * 100) + '%';
                const charging = battery.charging ? ' (Charging)' : ' (Not Charging)';
                document.getElementById('client-battery').innerText = level + charging;
            });
        } else {
            document.getElementById('client-battery').innerText = 'API Blocked';
        }

        // 3. Network connection (If supported)
        if (navigator.connection) {
            document.getElementById('client-network').innerText = navigator.connection.effectiveType || 'Unknown';
        } else {
            document.getElementById('client-network').innerText = navigator.onLine ? 'Online' : 'Offline';
        }

        // 4. GeoIP Fetching
        fetch('https://ipwho.is/')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('geo-isp').innerText = data.connection.isp + ' (' + data.connection.asn + ')';
                    document.getElementById('geo-city').innerText = data.city + ', ' + data.region;
                    document.getElementById('geo-country').innerText = data.country + ' (' + data.postal + ')';
                    document.getElementById('geo-latlon').innerText = data.latitude + ', ' + data.longitude;
                } else {
                    document.getElementById('geo-isp').innerText = 'Trace Blocked / Failed';
                    document.getElementById('geo-city').innerText = 'Unknown';
                    document.getElementById('geo-country').innerText = 'Unknown';
                    document.getElementById('geo-latlon').innerText = 'Unknown';
                }
            })
            .catch(() => {
                document.getElementById('geo-isp').innerText = 'Trace Blocked (AdBlock/VPN detected)';
            });

        // 5. Fake Terminal Animation
        const terminalLines = [
            "> Initializing security protocol...",
            "> Bypassing local firewall proxy...",
            "> Scanning open ports on target machine...",
            "> Port 80 (HTTP) ... Open",
            "> Port 443 (HTTPS) ... Open",
            "> Analyzing browser cache & cookies...",
            "> Extracting local MAC Address... [SUCCESS]",
            "> Capturing front camera snapshot... [PERMISSION DENIED]",
            "> Initiating silent payload drop... [SKIPPED]",
            "> Compiling forensic report...",
            "> Transmitting data to secure server...",
            "> [✓] Target successfully logged in the database."
        ];
        
        const terminalContainer = document.getElementById('terminal-content');
        let currentLine = 0;

        function addTerminalLine() {
            if (currentLine < terminalLines.length) {
                const div = document.createElement('div');
                div.innerText = terminalLines[currentLine];
                terminalContainer.appendChild(div);
                currentLine++;
                
                // Random delay between 400ms and 1500ms
                setTimeout(addTerminalLine, Math.floor(Math.random() * 1100) + 400);
            } else {
                // Blink cursor at the end
                const cursor = document.createElement('div');
                cursor.innerText = "> _";
                cursor.style.animation = "blink-caret 1s step-end infinite";
                terminalContainer.appendChild(cursor);
            }
        }
        
        setTimeout(addTerminalLine, 1000);

        // Add a slight flickering effect to the whole body
        setInterval(() => {
            if (Math.random() > 0.95) {
                document.body.style.filter = 'invert(1)';
                setTimeout(() => {
                    document.body.style.filter = 'invert(0)';
                }, 80);
            }
        }, 1000);

        // Live Clock
        setInterval(() => {
            const now = new Date();
            document.getElementById('clock').innerText = now.toISOString().replace('T', ' ').substring(0, 19);
        }, 1000);
    </script>
</body>
</html>
