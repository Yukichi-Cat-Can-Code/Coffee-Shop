@echo off
chcp 65001
echo.
echo ==========================================
echo   🎭 LOCAL CHROME LIVE SHOW
echo ==========================================
echo.
echo 🎯 PURE LOCAL AUTOMATION!
echo    ✓ Uses YOUR local ChromeDriver
echo    ✓ No Docker required
echo    ✓ Chrome opens on your desktop
echo    ✓ No screenshots - just watching
echo.
echo 🖥️ ChromeDriver: E:\HK3 24-25\BTPM\chromedriver-win64\chromedriver-win64
echo 🌐 Website: http://localhost/Coffee-Shop
echo 👁️ Browser will open automatically
echo ⏱️ Demo runs for about 3-4 minutes
echo.
echo Press any key to start the local show...
pause > nul

echo.
echo 🎬 STARTING LOCAL CHROME AUTOMATION...
echo.
php tests/SimpleChromeDemo.php

echo.
echo ==========================================
echo   🎉 LOCAL CHROME SHOW COMPLETED!
echo ==========================================
echo.
echo 🎭 You just watched LOCAL Chrome automation:
echo   ✓ ChromeDriver running on your machine
echo   ✓ No Docker containers needed
echo   ✓ Direct browser control
echo   ✓ Smooth animations and interactions
echo   ✓ Responsive design testing
echo.
echo 💡 ChromeDriver used: E:\HK3 24-25\BTPM\chromedriver-win64\chromedriver-win64
echo.
pause
