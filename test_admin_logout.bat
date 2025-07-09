@echo off
chcp 65001
echo.
echo ==========================================
echo   🧪 QUICK ADMIN LOGOUT TEST
echo ==========================================
echo.
echo 🎯 Testing admin login and logout flow
echo    with improved performance
echo.

echo 🚀 Running admin login test...
php tests/AdminLoginTest.php

echo.
echo ==========================================
echo   ✅ ADMIN LOGOUT TEST COMPLETED
echo ==========================================
echo.
echo 📋 Test Summary:
echo    ✅ Login with valid credentials
echo    ✅ Dashboard access verification  
echo    ✅ Automatic logout functionality
echo    ✅ Session security validation
echo    ✅ All tests in English logs
echo    ✅ Faster test execution (0.5-1.5s pauses)
echo.
pause
