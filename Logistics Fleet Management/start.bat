@echo off
echo ========================================================
echo Starting FleetTrack Logistics Fleet Management System
echo ========================================================
echo.
echo Opening server at http://localhost:8000
echo Press Ctrl+C in this window to stop the server.
echo.

set "PHP_EXE=C:\Users\DELL\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.5_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"

if exist "%PHP_EXE%" (
    start http://localhost:8000
    "%PHP_EXE%" -S localhost:8000
) else (
    start http://localhost:8000
    php -S localhost:8000
)

pause
