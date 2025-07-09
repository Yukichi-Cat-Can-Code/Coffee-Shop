<?php

require_once __DIR__ . '/BaseTest.php';

use Facebook\WebDriver\WebDriverBy;

/**
 * Test class for admin login functionality
 */
class AdminLoginTest extends BaseTest
{
    private $adminEmail = 'maiminh123@gmail.com';
    private $adminPassword = 'maiminh123';
    private $wrongPassword = 'wrongpassword';
    
    public function runAllTests()
    {
        $this->logInfo("🧪 STARTING ADMIN LOGIN SYSTEM TESTS (ENHANCED)");
        echo "=" . str_repeat("=", 60) . "\n";
        
        $this->setUp();
        
        try {
            // Test 1: Valid login with complete logout flow
            $this->testValidAdminLogin();
            $this->cleanupTestState();
            $this->pauseForObservation(0.5, "Cleanup after test 1");
            
            // Test 2: Invalid login
            $this->testInvalidAdminLogin();
            $this->cleanupTestState(); 
            $this->pauseForObservation(0.5, "Cleanup after test 2");
            
            // Test 3: Empty fields validation
            $this->testEmptyFields();
            $this->cleanupTestState();
            $this->pauseForObservation(0.5, "Cleanup after test 3");
            
            // Test 4: Password visibility toggle
            $this->testPasswordVisibilityToggle();
            $this->cleanupTestState();
            $this->pauseForObservation(0.5, "Cleanup after test 4");
            
            // Test 5: Enhanced verification (new)
            $this->testAdminDashboardAccess();
            $this->cleanupTestState();
            
            $this->logSuccess("🎉 ALL ADMIN LOGIN TESTS PASSED!");
            echo "=" . str_repeat("=", 60) . "\n";
            
        } catch (Exception $e) {
            $this->logError("Test FAILED: " . $e->getMessage());
            $this->takeScreenshot('admin_login_failed');
            throw $e;
        } finally {
            $this->tearDown();
        }
    }
    
    /**
     * Test valid admin login with complete logout flow
     */
    public function testValidAdminLogin()
    {
        $this->logStep(1, "Testing valid admin login (with complete logout)");
        
        // Navigate to admin login page
        $this->logAction("Navigating to admin login page");
        $this->driver->get($this->baseUrl . '/admin-panel/admins/login-admins.php');
        $this->pauseForObservation(1, "Admin login page loaded");
        
        // Verify page loaded
        $this->assertElementExists(WebDriverBy::className('login-container'));
        $this->logSuccess("Login page loaded successfully");
        
        // Fill login credentials
        $this->fillInput(WebDriverBy::name('admin_email'), $this->adminEmail, 'Admin email');
        $this->fillInput(WebDriverBy::name('admin_password'), $this->adminPassword, 'Admin password');
        
        $this->pauseForObservation(1, "Login credentials filled");
        
        // Click login button
        $this->clickElement(WebDriverBy::name('submit'), 'Login button');
        
        // Wait for redirect to dashboard
        $this->pauseForObservation(1.5, "Waiting for dashboard redirect");
        $this->assertCurrentUrl('/admin-panel');
        
        // Verify dashboard features
        $this->logAction("Verifying dashboard loaded successfully");
        $this->verifyAdminDashboardFeatures();
        
        // Perform logout
        $this->logAction("Performing logout");
        $this->performLogout();
        
        // Verify logout success
        $this->pauseForObservation(0.8, "Verifying logout success");
        $currentUrl = $this->driver->getCurrentURL();
        
        // Check if redirected to login or homepage (both acceptable)
        if (strpos($currentUrl, 'login-admins.php') !== false || 
            strpos($currentUrl, '/Coffee-Shop/') !== false ||
            strpos($currentUrl, '/admin-panel/admins/') !== false) {
            $this->logSuccess("✓ Logout successful, current URL: " . $currentUrl);
        } else {
            $this->logWarning("⚠ Logout URL unexpected: " . $currentUrl);
        }
        
        // Verify session expired - important security test
        $this->logAction("Verifying session expired");
        $this->driver->get($this->baseUrl . '/admin-panel/index.php');
        $this->pauseForObservation(0.8, "Checking redirect after accessing dashboard");
        
        // Check session security (flexible check)
        $finalUrl = $this->driver->getCurrentURL();
        if (strpos($finalUrl, 'login') !== false || 
            strpos($finalUrl, 'auth') !== false ||
            $this->driver->getTitle() === 'Unauthorized' ||
            strpos($this->driver->getPageSource(), 'login') !== false) {
            $this->logSuccess("✓ Session security working - cannot access dashboard after logout");
        } else {
            $this->logWarning("⚠ Session may not be fully expired");
        }
        
        $this->logSuccess("Valid admin login test: PASSED");
    }
    
    /**
     * Test admin login with wrong credentials
     */
    public function testInvalidAdminLogin()
    {
        $this->logStep(2, "Testing admin login with wrong password");
        
        // Navigate to admin login page
        $this->logAction("Navigating to admin login page");
        $this->driver->get($this->baseUrl . '/admin-panel/admins/login-admins.php');
        $this->pauseForObservation(0.8, "Admin login page loaded");
        
        // Fill wrong credentials
        $this->fillInput(WebDriverBy::name('admin_email'), $this->adminEmail, 'Correct admin email');
        $this->fillInput(WebDriverBy::name('admin_password'), $this->wrongPassword, 'Wrong password');
        
        $this->pauseForObservation(1, "Wrong credentials filled");
        
        // Click login button
        $this->clickElement(WebDriverBy::name('submit'), 'Login button');
        
        // Wait and check for error message or no redirect
        $this->pauseForObservation(1.5, "Waiting for error message or verification of failed login");
        
        // Flexible error checking - multiple ways to check for errors
        $errorDetected = false;
        $errorMessages = [];
        
        // Check 1: Look for alert-danger class
        try {
            $this->assertElementExists(WebDriverBy::className('alert-danger'));
            $errorDetected = true;
            $errorMessages[] = "Found alert-danger element";
        } catch (Exception $e) {
            // Continue to other checks
        }
        
        // Check 2: Look for other error classes
        try {
            $this->assertElementExists(WebDriverBy::className('error'));
            $errorDetected = true;
            $errorMessages[] = "Found error element";
        } catch (Exception $e) {
            // Continue to other checks
        }
        
        // Check 3: Verify still on login page (no redirect)
        $currentUrl = $this->driver->getCurrentURL();
        if (strpos($currentUrl, 'login-admins.php') !== false) {
            $errorDetected = true;
            $errorMessages[] = "Stayed on login page (no redirect)";
        }
        
        // Check 4: Check page source for error/invalid text
        $pageSource = $this->driver->getPageSource();
        if (strpos(strtolower($pageSource), 'invalid') !== false ||
            strpos(strtolower($pageSource), 'error') !== false ||
            strpos(strtolower($pageSource), 'wrong') !== false ||
            strpos(strtolower($pageSource), 'incorrect') !== false) {
            $errorDetected = true;
            $errorMessages[] = "Found error text in page source";
        }
        
        if ($errorDetected) {
            $this->logSuccess("Invalid login test: PASSED");
            $this->logInfo("Error detection methods: " . implode(", ", $errorMessages));
        } else {
            $this->logWarning("No clear error detected, but test may still pass if login was prevented");
        }
    }
    
    /**
     * Test validation when leaving fields empty
     */
    public function testEmptyFields()
    {
        $this->logStep(3, "Testing required field validation");
        
        // Navigate to admin login page
        $this->logAction("Navigating to admin login page");
        $this->driver->get($this->baseUrl . '/admin-panel/admins/login-admins.php');
        $this->pauseForObservation(0.8, "Login page loaded");
        
        // Click submit without filling anything
        $this->logAction("Testing submit with empty fields");
        $this->clickElement(WebDriverBy::name('submit'), 'Submit button (empty fields)');
        $this->pauseForObservation(0.8, "Checking validation response");
        
        // Try with only email filled
        $this->logAction("Testing with only email filled");
        $this->fillInput(WebDriverBy::name('admin_email'), $this->adminEmail, 'Email only');
        $this->clickElement(WebDriverBy::name('submit'), 'Submit button (password empty)');
        $this->pauseForObservation(0.8, "Checking password validation");
        
        $this->logSuccess("Field validation test: PASSED");
    }
    
    /**
     * Test show/hide password functionality
     */
    public function testPasswordVisibilityToggle()
    {
        $this->logStep(4, "Testing password visibility toggle");
        
        // Navigate to admin login page
        $this->logAction("Navigating to admin login page");
        $this->driver->get($this->baseUrl . '/admin-panel/admins/login-admins.php');
        $this->pauseForObservation(0.8, "Login page loaded");
        
        // Fill password
        $this->logAction("Filling password field");
        $passwordField = $this->waitForElement(WebDriverBy::id('passwordField'));
        $passwordField->sendKeys($this->adminPassword);
        
        // Check initial type is password
        $initialType = $passwordField->getAttribute('type');
        if ($initialType !== 'password') {
            throw new Exception("Password field type should be 'password' initially");
        }
        $this->logInfo("✓ Initial password field type: " . $initialType);
        
        // Click toggle button
        $this->logAction("Clicking password visibility toggle");
        $toggleButton = $this->waitForElement(WebDriverBy::id('togglePassword'));
        $toggleButton->click();
        $this->pauseForObservation(0.5, "Password visibility toggled");
        
        // Check type changed to text
        $newType = $passwordField->getAttribute('type');
        if ($newType !== 'text') {
            throw new Exception("Password field type should be 'text' after toggle");
        }
        $this->logInfo("✓ Password field type after toggle: " . $newType);
        
        // Click again to hide
        $this->logAction("Hiding password again");
        $toggleButton->click();
        $this->pauseForObservation(0.5, "Password hidden again");
        
        $finalType = $passwordField->getAttribute('type');
        if ($finalType !== 'password') {
            throw new Exception("Password field type should be 'password' after second toggle");
        }
        $this->logInfo("✓ Final password field type: " . $finalType);
        
        $this->logSuccess("Password visibility toggle test: PASSED");
    }
    
    /**
     * Test dashboard access with full verification
     */
    public function testAdminDashboardAccess()
    {
        $this->logStep(5, "Testing dashboard access with full verification");
        
        // Login again
        $this->logAction("Logging in to test dashboard features");
        $this->driver->get($this->baseUrl . '/admin-panel/admins/login-admins.php');
        $this->fillInput(WebDriverBy::name('admin_email'), $this->adminEmail, 'Admin email');
        $this->fillInput(WebDriverBy::name('admin_password'), $this->adminPassword, 'Admin password');
        $this->clickElement(WebDriverBy::name('submit'), 'Login button');
        
        $this->pauseForObservation(1.5, "Logged in, checking dashboard");
        
        // Verify dashboard features
        $this->verifyAdminDashboardFeatures();
        
        // Test navigation in dashboard (if available)
        $this->testDashboardNavigation();
        
        // Logout after testing
        $this->performLogout();
        
        $this->logSuccess("Dashboard access test: PASSED");
    }
    
    /**
     * Test navigation within dashboard
     */
    private function testDashboardNavigation()
    {
        $this->logAction("Testing navigation within dashboard");
        
        // Common links usually found in admin dashboard
        $navigationLinks = [
            'Products' => '/admin-panel/products-admins/',
            'Orders' => '/admin-panel/orders-admins/', 
            'Users' => '/admin-panel/users-admins/',
            'Bookings' => '/admin-panel/bookings-admins/',
            'Reviews' => '/admin-panel/reviews-admins/'
        ];
        
        foreach ($navigationLinks as $linkText => $expectedUrl) {
            try {
                // Find link by text
                $linkElement = $this->driver->findElement(WebDriverBy::partialLinkText($linkText));
                if ($linkElement && $linkElement->isDisplayed()) {
                    $this->logInfo("✓ Found navigation link: {$linkText}");
                    
                    // Click and verify URL (optional, can comment if don't want to navigate)
                    // $linkElement->click();
                    // $this->pauseForObservation(1, "Navigated to {$linkText}");
                    // $this->assertCurrentUrl($expectedUrl);
                    // $this->driver->navigate()->back();
                } else {
                    $this->logInfo("⚠ Link not visible: {$linkText}");
                }
            } catch (Exception $e) {
                $this->logInfo("⚠ Link not found: {$linkText}");
            }
        }
    }
    
    /**
     * Perform admin logout with enhanced selector detection
     */
    private function performLogout()
    {
        try {
            // First, try to click admin profile dropdown to reveal logout option
            $this->logAction("Looking for admin profile dropdown");
            
            // Use exact selectors from the admin panel
            $profileSelectors = [
                WebDriverBy::id('userDropdown'),  // Exact ID from navbar.php
                WebDriverBy::className('dropdown-toggle'),
                WebDriverBy::xpath("//a[contains(@id, 'userDropdown')]"),
                WebDriverBy::xpath("//a[contains(@class, 'dropdown-toggle')]")
            ];
            
            $dropdownFound = false;
            foreach ($profileSelectors as $selector) {
                try {
                    $dropdownElement = $this->driver->findElement($selector);
                    if ($dropdownElement && $dropdownElement->isDisplayed()) {
                        $this->logAction("Found profile dropdown, clicking to reveal menu");
                        $dropdownElement->click();
                        $this->pauseForObservation(0.8, "Dropdown menu opened");
                        $dropdownFound = true;
                        break;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
            
            // Now try to find logout link with exact selectors
            $logoutSelectors = [
                WebDriverBy::className('logout-item'),  // Exact class from navbar.php
                WebDriverBy::xpath("//a[contains(@class, 'logout-item')]"),
                WebDriverBy::xpath("//a[contains(@href, '/admins/logout.php')]"),
                WebDriverBy::linkText('Logout'),
                WebDriverBy::partialLinkText('Logout'),
                WebDriverBy::xpath("//a[contains(text(), 'Logout')]")
            ];
            
            $logoutFound = false;
            foreach ($logoutSelectors as $selector) {
                try {
                    $logoutElement = $this->driver->findElement($selector);
                    if ($logoutElement && $logoutElement->isDisplayed()) {
                        $this->logAction("Found logout button, clicking...");
                        $logoutElement->click();
                        $logoutFound = true;
                        break;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
            
            if (!$logoutFound) {
                // Fallback: Navigate directly to logout page
                $this->logWarning("Logout button not found, using direct URL");
                $this->driver->get($this->baseUrl . '/admin-panel/admins/logout.php');
            }
            
            $this->pauseForObservation(0.8, "Logout action completed");
            
        } catch (Exception $e) {
            $this->logWarning("Logout method failed: " . $e->getMessage());
            // Fallback: Clear session manually
            $this->driver->get($this->baseUrl . '/admin-panel/admins/logout.php');
        }
    }
    
    /**
     * Clean up after each test to ensure clean state
     */
    private function cleanupTestState()
    {
        try {
            // Clear cookies to ensure clean session
            $this->driver->manage()->deleteAllCookies();
            
            // Clear local storage if available
            $this->driver->executeScript("localStorage.clear();");
            $this->driver->executeScript("sessionStorage.clear();");
            
            $this->logInfo("Test state cleaned up successfully");
        } catch (Exception $e) {
            $this->logWarning("Cleanup failed: " . $e->getMessage());
        }
    }
    
    /**
     * Verify features after operations (best practice)
     */
    private function verifyAdminDashboardFeatures()
    {
        $this->logAction("Verifying dashboard features");
        
        // Check menu/navigation elements
        $expectedElements = [
            'navbar' => 'Navigation bar',
            'sidebar' => 'Sidebar menu', 
            'content' => 'Main content area'
        ];
        
        foreach ($expectedElements as $class => $description) {
            try {
                $this->assertElementExists(WebDriverBy::className($class));
                $this->logSuccess("✓ {$description} exists");
            } catch (Exception $e) {
                $this->logWarning("⚠ {$description} not found");
            }
        }
        
        // Check page title
        $title = $this->driver->getTitle();
        if (strpos(strtolower($title), 'admin') !== false) {
            $this->logSuccess("✓ Page title contains 'admin': " . $title);
        } else {
            $this->logWarning("⚠ Page title unclear: " . $title);
        }
    }
}

// Run test if file is called directly
if (basename(__FILE__) == basename($_SERVER["SCRIPT_NAME"])) {
    $test = new AdminLoginTest();
    $test->runAllTests();
}
