@echo off
chcp 65001
echo.
echo ==========================================
echo   🧹 CLEANUP DOCKER-RELATED FILES
echo ==========================================
echo.
echo 🎯 This script will remove Docker/Selenium files
echo    that are no longer needed since we use ChromeDriver local
echo.
echo ⚠️ FILES TO BE REMOVED:
echo    📁 Docker-related batch files
echo    📁 Old Docker Selenium test files  
echo    📁 Obsolete documentation
echo    📁 Unused screenshots
echo.
echo ✅ FILES TO KEEP:
echo    📁 ChromeDriver local files
echo    📁 BaseTest.php (updated)
echo    📁 TestConfig.php (updated) 
echo    📁 Core test files
echo    📁 Current documentation
echo.
echo Press Y to proceed with cleanup, N to cancel
set /p choice="Continue? (Y/N): "

if /i "%choice%" NEQ "Y" (
    echo Cleanup cancelled.
    goto end
)

echo.
echo 🧹 STARTING CLEANUP...
echo.

REM Remove Docker-related batch files
echo 📁 Removing Docker-related scripts...
if exist "selenium_fix.bat" (
    del "selenium_fix.bat"
    echo    ✅ Removed: selenium_fix.bat
)
if exist "fix_selenium.bat" (
    del "fix_selenium.bat" 
    echo    ✅ Removed: fix_selenium.bat
)
if exist "run_tests.bat" (
    del "run_tests.bat"
    echo    ✅ Removed: run_tests.bat
)
if exist "run_tests_improved.bat" (
    del "run_tests_improved.bat"
    echo    ✅ Removed: run_tests_improved.bat
)
if exist "run_tests_simple.bat" (
    del "run_tests_simple.bat"
    echo    ✅ Removed: run_tests_simple.bat
)
if exist "demo_browser.bat" (
    del "demo_browser.bat"
    echo    ✅ Removed: demo_browser.bat
)
if exist "watch_selenium.bat" (
    del "watch_selenium.bat"
    echo    ✅ Removed: watch_selenium.bat
)
if exist "full_styling_demo.bat" (
    del "full_styling_demo.bat"
    echo    ✅ Removed: full_styling_demo.bat
)
if exist "stable_demo.bat" (
    del "stable_demo.bat"
    echo    ✅ Removed: stable_demo.bat
)
if exist "run_selenium_demo.bat" (
    del "run_selenium_demo.bat"
    echo    ✅ Removed: run_selenium_demo.bat
)

REM Remove old Docker test files
echo.
echo 📁 Removing old Docker test files...
if exist "tests\TestRunner.php" (
    del "tests\TestRunner.php"
    echo    ✅ Removed: tests\TestRunner.php
)
if exist "tests\SimpleTest.php" (
    del "tests\SimpleTest.php"
    echo    ✅ Removed: tests\SimpleTest.php
)
if exist "tests\DemoTest.php" (
    del "tests\DemoTest.php"
    echo    ✅ Removed: tests\DemoTest.php
)
if exist "tests\DemoFullTest.php" (
    del "tests\DemoFullTest.php"
    echo    ✅ Removed: tests\DemoFullTest.php
)
if exist "tests\StableDemoTest.php" (
    del "tests\StableDemoTest.php"
    echo    ✅ Removed: tests\StableDemoTest.php
)
if exist "tests\SlowDemoTest.php" (
    del "tests\SlowDemoTest.php"
    echo    ✅ Removed: tests\SlowDemoTest.php
)
if exist "tests\FullResourcesTest.php" (
    del "tests\FullResourcesTest.php"
    echo    ✅ Removed: tests\FullResourcesTest.php
)
if exist "tests\SeleniumDemo.php" (
    del "tests\SeleniumDemo.php"
    echo    ✅ Removed: tests\SeleniumDemo.php
)
if exist "tests\check_setup.php" (
    del "tests\check_setup.php"
    echo    ✅ Removed: tests\check_setup.php
)
if exist "tests\selenium_manual_check.php" (
    del "tests\selenium_manual_check.php"
    echo    ✅ Removed: tests\selenium_manual_check.php
)
if exist "tests\selenium_diagnosis.php" (
    del "tests\selenium_diagnosis.php"
    echo    ✅ Removed: tests\selenium_diagnosis.php
)

REM Remove old documentation
echo.
echo 📁 Removing old documentation...
if exist "SELENIUM_STATUS.md" (
    del "SELENIUM_STATUS.md"
    echo    ✅ Removed: SELENIUM_STATUS.md
)
if exist "SELENIUM_SETUP.md" (
    del "SELENIUM_SETUP.md"
    echo    ✅ Removed: SELENIUM_SETUP.md
)
if exist "SELENIUM_IMPROVEMENTS.md" (
    del "SELENIUM_IMPROVEMENTS.md"
    echo    ✅ Removed: SELENIUM_IMPROVEMENTS.md
)
if exist "SELENIUM_AUTOMATION_GUIDE.md" (
    del "SELENIUM_AUTOMATION_GUIDE.md"
    echo    ✅ Removed: SELENIUM_AUTOMATION_GUIDE.md
)
if exist "QUICK_START.md" (
    del "QUICK_START.md"
    echo    ✅ Removed: QUICK_START.md
)
if exist "FULL_STYLING_SCREENSHOTS.md" (
    del "FULL_STYLING_SCREENSHOTS.md"
    echo    ✅ Removed: FULL_STYLING_SCREENSHOTS.md
)

REM Keep certain PowerShell scripts if they exist
if exist "run_tests.ps1" (
    del "run_tests.ps1"
    echo    ✅ Removed: run_tests.ps1
)
if exist "run_tests.sh" (
    del "run_tests.sh"
    echo    ✅ Removed: run_tests.sh
)

REM Remove old screenshots if they exist
echo.
echo 📁 Removing old screenshots...
if exist "tests\screenshots" (
    rmdir /s /q "tests\screenshots" 2>nul
    echo    ✅ Removed: tests\screenshots folder
)
if exist "screenshots" (
    rmdir /s /q "screenshots" 2>nul
    echo    ✅ Removed: screenshots folder
)

echo.
echo ==========================================
echo   ✅ CLEANUP COMPLETED
echo ==========================================
echo.
echo 🧹 Removed obsolete Docker/Selenium files
echo 📁 Kept essential ChromeDriver files:
echo    ✓ tests/BaseTest.php
echo    ✓ tests/TestConfig.php
echo    ✓ tests/AdminLoginTest.php
echo    ✓ tests/BookingTest.php
echo    ✓ tests/QuickChromeTest.php
echo    ✓ tests/WatchOnlyTest.php
echo    ✓ tests/SimpleChromeDemo.php
echo    ✓ tests/LocalChromeTest.php
echo    ✓ tests/LocalLiveDemo.php
echo    ✓ tests/EnhancedChromeDemo.php
echo    ✓ live_show.bat
echo    ✓ quick_chrome_test.bat
echo    ✓ check_chromedriver.bat
echo    ✓ selenium_menu.bat
echo    ✓ CHROMEDRIVER_LOCAL_GUIDE.md
echo    ✓ MIGRATION_COMPLETED.md
echo.
echo 🎯 Your project is now optimized for ChromeDriver local!
echo.

:end
pause
