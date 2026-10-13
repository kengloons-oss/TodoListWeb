@echo off
setlocal

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0start-dev.ps1"
set "DEV_EXIT_CODE=%ERRORLEVEL%"

if not "%DEV_EXIT_CODE%"=="0" (
    echo.
    pause
)

exit /b %DEV_EXIT_CODE%
