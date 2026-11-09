@echo off
setlocal
cd /d "%~dp0"

REM Ensure PostgreSQL server is running
echo Checking PostgreSQL status...
"C:\Program Files\PostgreSQL\18\bin\pg_ctl.exe" status -D "C:\Users\Wilmark\Desktop\Postgre" >nul 2>&1
if errorlevel 1 (
    echo Starting PostgreSQL on port 5552...
    "C:\Program Files\PostgreSQL\18\bin\pg_ctl.exe" start -D "C:\Users\Wilmark\Desktop\Postgre" -w
) else (
    echo PostgreSQL is already running.
)

set "BCC_PHP=C:\Users\Wilmark\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
if not exist "%BCC_PHP%" set "BCC_PHP=php"
echo Starting BCC Online Enrollment at http://127.0.0.1:8001
echo Keep this window open. Press Ctrl+C to stop.
"%BCC_PHP%" -S 127.0.0.1:8001 -t public dev-router.php
pause
