@echo off
chcp 65001
echo.
echo ==========================================
echo   🎯 COFFEE SHOP SELENIUM - MENU OPTIONS
echo ==========================================
echo.
echo 🚀 QUICK SETUP ^& TESTS:
echo    1. check_chromedriver.bat    - Kiểm tra ChromeDriver setup
echo    2. quick_chrome_test.bat     - Test nhanh 10 giây  
echo.
echo 🎬 LIVE AUTOMATION DEMOS:
echo    3. live_show.bat            - Demo automation trực tiếp (3-4 phút)
echo    4. php tests/WatchOnlyTest.php - Demo với interaction
echo.
echo 📋 TEST FILES TRỰC TIẾP:
echo    5. php tests/QuickChromeTest.php    - Test nhanh
echo    6. php tests/SimpleChromeDemo.php   - Demo đơn giản
echo    7. php tests/AdminLoginTest.php     - Test đăng nhập admin (English logs)
echo    8. test_admin_logout.bat            - Test logout admin nhanh
echo    9. php tests/BookingTest.php        - Test booking
echo.
echo 📚 TÀI LIỆU HƯỚNG DẪN:
echo   10. CHROMEDRIVER_LOCAL_GUIDE.md - Hướng dẫn ChromeDriver local
echo   11. MIGRATION_COMPLETED.md - Tổng kết migration
echo   12. ADMIN_TEST_IMPROVEMENTS.md - Cải tiến test admin
echo.
echo 🧹 CLEANUP ^& MAINTENANCE:
echo   13. backup_before_cleanup.bat - Backup trước khi cleanup
echo   14. cleanup_docker_files.bat - Xóa files Docker không cần
echo   15. CLEANUP_GUIDE.md - Hướng dẫn cleanup
echo.
echo ==========================================
echo   ⚙️ CURRENT CONFIGURATION
echo ==========================================
echo   🖥️ ChromeDriver: LOCAL (E:\HK3 24-25\BTPM\chromedriver-win64\chromedriver-win64)
echo   🌐 Website: http://localhost/Coffee-Shop  
echo   👁️ UI Mode: ENABLED (browser hiển thị)
echo   🐳 Docker: DISABLED (không cần container)
echo ==========================================
echo.
echo Select an option (1-13):
set /p choice="Enter your choice: "

if "%choice%"=="1" goto check_chromedriver
if "%choice%"=="2" goto quick_test
if "%choice%"=="3" goto live_show
if "%choice%"=="4" goto watch_only
if "%choice%"=="5" goto quick_php
if "%choice%"=="6" goto simple_demo
if "%choice%"=="7" goto admin_test
if "%choice%"=="8" goto booking_test
if "%choice%"=="9" goto guide_chrome
if "%choice%"=="10" goto migration_completed
if "%choice%"=="11" goto backup_cleanup
if "%choice%"=="12" goto cleanup_docker
if "%choice%"=="13" goto cleanup_guide

echo Invalid choice. Please run again.
goto end

:check_chromedriver
echo.
echo 🔍 Running ChromeDriver check...
call check_chromedriver.bat
goto end

:quick_test
echo.
echo ⚡ Running quick test...
call quick_chrome_test.bat
goto end

:live_show
echo.
echo 🎬 Starting live show...
call live_show.bat
goto end

:watch_only
echo.
echo 👁️ Running watch only demo...
php tests/WatchOnlyTest.php
goto end

:quick_php
echo.
echo ⚡ Running quick PHP test...
php tests/QuickChromeTest.php
goto end

:simple_demo
echo.
echo 🎯 Running simple demo...
php tests/SimpleChromeDemo.php
goto end

:admin_test
echo.
echo 👨‍💼 Running admin login test...
php tests/AdminLoginTest.php
goto end

:booking_test
echo.
echo 📅 Running booking test...
php tests/BookingTest.php
goto end

:guide_chrome
echo.
echo 📖 Opening ChromeDriver Local Guide...
start CHROMEDRIVER_LOCAL_GUIDE.md
goto end

:migration_completed
echo.
echo 📖 Opening Migration Summary...
start MIGRATION_COMPLETED.md
goto end

:backup_cleanup
echo.
echo � Running backup before cleanup...
call backup_before_cleanup.bat
goto end

:cleanup_docker
echo.
echo 🧹 Running Docker files cleanup...
call cleanup_docker_files.bat
goto end

:cleanup_guide
echo.
echo 📖 Opening Cleanup Guide...
start CLEANUP_GUIDE.md
goto end

:end
echo.
pause
