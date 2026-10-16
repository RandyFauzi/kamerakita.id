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

"C:\Users\Administrator\AppData\Local\Python\pythoncore-3.14-64\python.exe" -u "scripts\atlas_sync.py" --all
if errorlevel 1 goto :failed

echo.
echo ========================================================
echo   SINKRONISASI SELESAI!
echo ========================================================
echo.

rem Hanya tunggu tombol jika dijalankan secara manual (bukan Task Scheduler)
if "%1"=="--scheduled" goto :eof
if "%1"=="--silent" goto :eof
pause
exit /b 0

:failed
echo [ERROR] Sinkronisasi gagal. Periksa pesan error di atas.
if "%1"=="--scheduled" exit /b 1
if "%1"=="--silent" exit /b 1
pause
exit /b 1
