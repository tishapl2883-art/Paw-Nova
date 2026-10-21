@echo off
title PawConnect - Pet Adoption System
echo ========================================================
echo               PawConnect - Pet Adoption System
echo ========================================================
echo.
echo Starting PHP Built-in Server at http://localhost:8000 ...
echo Press Ctrl+C anytime to stop the server.
echo.

if exist "C:\xampp\php\php.exe" (
    start http://localhost:8000
    "C:\xampp\php\php.exe" -S localhost:8000
) else (
    echo [ERROR] PHP executable not found at C:\xampp\php\php.exe
    pause
)
