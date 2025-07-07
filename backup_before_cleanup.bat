@echo off
chcp 65001
echo.
echo ==========================================
echo   💾 BACKUP BEFORE CLEANUP
echo ==========================================
echo.
echo 🎯 Creating backup of files that will be removed
echo    in case you need to restore them later
echo.

set BACKUP_DIR=backup_docker_files_%date:~10,4%%date:~4,2%%date:~7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set BACKUP_DIR=%BACKUP_DIR: =0%

echo 📁 Creating backup directory: %BACKUP_DIR%
mkdir "%BACKUP_DIR%" 2>nul

echo.
echo 💾 Backing up files...

REM Backup batch files
if exist "selenium_fix.bat" copy "selenium_fix.bat" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: selenium_fix.bat
if exist "fix_selenium.bat" copy "fix_selenium.bat" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: fix_selenium.bat
if exist "run_tests.bat" copy "run_tests.bat" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: run_tests.bat
if exist "run_tests_improved.bat" copy "run_tests_improved.bat" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: run_tests_improved.bat
if exist "run_tests_simple.bat" copy "run_tests_simple.bat" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: run_tests_simple.bat
if exist "demo_browser.bat" copy "demo_browser.bat" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: demo_browser.bat
if exist "watch_selenium.bat" copy "watch_selenium.bat" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: watch_selenium.bat
if exist "full_styling_demo.bat" copy "full_styling_demo.bat" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: full_styling_demo.bat
if exist "stable_demo.bat" copy "stable_demo.bat" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: stable_demo.bat
if exist "run_selenium_demo.bat" copy "run_selenium_demo.bat" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: run_selenium_demo.bat
if exist "run_tests.ps1" copy "run_tests.ps1" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: run_tests.ps1
if exist "run_tests.sh" copy "run_tests.sh" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: run_tests.sh

REM Backup test files
mkdir "%BACKUP_DIR%\tests" 2>nul
if exist "tests\TestRunner.php" copy "tests\TestRunner.php" "%BACKUP_DIR%\tests\" >nul && echo    ✅ Backed up: tests\TestRunner.php
if exist "tests\SimpleTest.php" copy "tests\SimpleTest.php" "%BACKUP_DIR%\tests\" >nul && echo    ✅ Backed up: tests\SimpleTest.php
if exist "tests\DemoTest.php" copy "tests\DemoTest.php" "%BACKUP_DIR%\tests\" >nul && echo    ✅ Backed up: tests\DemoTest.php
if exist "tests\DemoFullTest.php" copy "tests\DemoFullTest.php" "%BACKUP_DIR%\tests\" >nul && echo    ✅ Backed up: tests\DemoFullTest.php
if exist "tests\StableDemoTest.php" copy "tests\StableDemoTest.php" "%BACKUP_DIR%\tests\" >nul && echo    ✅ Backed up: tests\StableDemoTest.php
if exist "tests\SlowDemoTest.php" copy "tests\SlowDemoTest.php" "%BACKUP_DIR%\tests\" >nul && echo    ✅ Backed up: tests\SlowDemoTest.php
if exist "tests\FullResourcesTest.php" copy "tests\FullResourcesTest.php" "%BACKUP_DIR%\tests\" >nul && echo    ✅ Backed up: tests\FullResourcesTest.php
if exist "tests\SeleniumDemo.php" copy "tests\SeleniumDemo.php" "%BACKUP_DIR%\tests\" >nul && echo    ✅ Backed up: tests\SeleniumDemo.php
if exist "tests\check_setup.php" copy "tests\check_setup.php" "%BACKUP_DIR%\tests\" >nul && echo    ✅ Backed up: tests\check_setup.php
if exist "tests\selenium_manual_check.php" copy "tests\selenium_manual_check.php" "%BACKUP_DIR%\tests\" >nul && echo    ✅ Backed up: tests\selenium_manual_check.php
if exist "tests\selenium_diagnosis.php" copy "tests\selenium_diagnosis.php" "%BACKUP_DIR%\tests\" >nul && echo    ✅ Backed up: tests\selenium_diagnosis.php

REM Backup documentation
if exist "SELENIUM_STATUS.md" copy "SELENIUM_STATUS.md" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: SELENIUM_STATUS.md
if exist "SELENIUM_SETUP.md" copy "SELENIUM_SETUP.md" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: SELENIUM_SETUP.md
if exist "SELENIUM_IMPROVEMENTS.md" copy "SELENIUM_IMPROVEMENTS.md" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: SELENIUM_IMPROVEMENTS.md
if exist "SELENIUM_AUTOMATION_GUIDE.md" copy "SELENIUM_AUTOMATION_GUIDE.md" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: SELENIUM_AUTOMATION_GUIDE.md
if exist "QUICK_START.md" copy "QUICK_START.md" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: QUICK_START.md
if exist "FULL_STYLING_SCREENSHOTS.md" copy "FULL_STYLING_SCREENSHOTS.md" "%BACKUP_DIR%\" >nul && echo    ✅ Backed up: FULL_STYLING_SCREENSHOTS.md

echo.
echo ==========================================
echo   ✅ BACKUP COMPLETED
echo ==========================================
echo.
echo 💾 All files backed up to: %BACKUP_DIR%
echo 🎯 You can now safely run cleanup_docker_files.bat
echo 📁 To restore files later, copy them back from backup folder
echo.
echo Press any key to continue...
pause >nul

echo.
echo 🚀 Do you want to run the cleanup now? (Y/N)
set /p cleanup_choice="Run cleanup_docker_files.bat? "

if /i "%cleanup_choice%"=="Y" (
    echo.
    echo 🧹 Starting cleanup...
    call cleanup_docker_files.bat
) else (
    echo.
    echo ℹ️ Backup completed. Run cleanup_docker_files.bat when ready.
)

pause
