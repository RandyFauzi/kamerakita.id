@echo off
title KameraKita - Atlas Dashboard Auto Sync
echo ========================================================
echo   KAMERAKITA - SINKRONISASI OTOMATIS DATA ATLAS
echo ========================================================
echo.
echo [*] Memulai proses sinkronisasi dari Atlas Dashboard ke Web...
echo [*] Mode: Murni Sync Dashboard Web (Tanpa Ekspor Excel AIKu Studio)
echo.

cd /d "C:\laragon\www\kamerakita.id-main"

"C:\Users\Administrator\AppData\Local\Python\pythoncore-3.14-64\python.exe" -u "C:\Users\Administrator\.gemini\antigravity\scratch\atlas_sync.py" --all

echo.
echo ========================================================
echo   SINKRONISASI SELESAI!
echo ========================================================
echo.
pause
