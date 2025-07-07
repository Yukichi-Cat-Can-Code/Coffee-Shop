@echo off
chcp 65001
echo.
echo ==========================================
echo   🚀 QUICK CHROME TEST
echo ==========================================
echo.
echo 🎯 Quick test to verify ChromeDriver works
echo    ► Opens Chrome browser
echo    ► Navigates to Coffee Shop homepage  
echo    ► Closes automatically after 10 seconds
echo.
echo Press any key to start quick test...
pause > nul

echo.
echo 🎬 STARTING QUICK CHROME TEST...
echo.
php tests/QuickChromeTest.php

echo.
echo ==========================================
echo   ✅ QUICK TEST COMPLETED!
echo ==========================================
echo.
pause
