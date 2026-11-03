@echo off
setlocal
title KameraKita - 1618 Recording Sync
cd /d "%~dp0"
"C:\Users\Administrator\AppData\Local\Python\pythoncore-3.14-64\python.exe" -u "scripts\robotics1618_sync.py"
set "connectorExit=%errorlevel%"
if not "%1"=="--scheduled" pause
exit /b %connectorExit%
