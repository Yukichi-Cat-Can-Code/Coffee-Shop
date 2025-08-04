@echo off
chcp 65001
echo.
echo ==========================================
echo   🔍 CHECKING CHROMEDRIVER SETUP
echo ==========================================
echo.

set CHROMEDRIVER_PATH=E:\HK3 24-25\BTPM\chromedriver-win64\chromedriver-win64\chromedriver.exe

echo 📁 Checking ChromeDriver path...
echo    Path: %CHROMEDRIVER_PATH%

if exist "%CHROMEDRIVER_PATH%" (
    echo    ✅ ChromeDriver file found!
) else (
    echo    ❌ ChromeDriver NOT found!
    echo    ℹ️ Please make sure ChromeDriver is installed at:
    echo       %CHROMEDRIVER_PATH%
    echo.
    echo    📥 Download from: https://chromedriver.chromium.org/
    goto end
)

echo.
echo 🚀 Testing ChromeDriver execution...
echo    Attempting to run ChromeDriver --version...

"%CHROMEDRIVER_PATH%" --version

if %ERRORLEVEL% EQU 0 (
    echo    ✅ ChromeDriver is executable!
) else (
    echo    ❌ ChromeDriver execution failed!
    echo    ℹ️ Check if the file is not corrupted or blocked
    goto end
)

echo.
echo 🌐 Checking Chrome browser...
where chrome.exe >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo    ✅ Chrome browser found in PATH
) else (
    echo    ⚠️ Chrome browser not found in PATH
    echo    ℹ️ Chrome might still work if installed in default location
)

echo.
echo 🔗 Checking localhost Coffee Shop...
curl -s -o nul -w "HTTP Status: %%{http_code}\n" http://localhost/Coffee-Shop/ 2>nul
if %ERRORLEVEL% EQU 0 (
    echo    ✅ Coffee Shop website accessible at http://localhost/Coffee-Shop/
) else (
    echo    ❌ Cannot access http://localhost/Coffee-Shop/
    echo    ℹ️ Make sure XAMPP is running and website is accessible
)

echo.
echo ==========================================
echo   ✅ CHROMEDRIVER CHECK COMPLETED
echo ==========================================
echo.
echo 🎯 If all checks passed, you can run:
echo    ► live_show.bat (to watch automation)
echo    ► php tests/WatchOnlyTest.php (direct test)
echo.

:end
pause
