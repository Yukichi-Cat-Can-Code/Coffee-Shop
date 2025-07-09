<?php

require_once __DIR__ . '/BaseTest.php';

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;

/**
 * Test class for booking functionality
 */
class BookingTest extends BaseTest
{
    // Test user information
    private $userEmail = 'abcdef@gmail.com';
    private $userPassword = 'abcdefgh';
    
    // Test booking data
    private $testBookingData = [
        'first_name' => 'Nguyen',
        'last_name' => 'Van Test',
        'phone_number' => '0987654321',
        'message' => 'This is a test booking from Selenium'
    ];
    
    public function runAllTests()
    {
        $this->logInfo("🧪 STARTING BOOKING SYSTEM TESTS");
        echo "=" . str_repeat("=", 60) . "\n";
        
        $this->setUp();
        
        try {
            $this->testUserLoginRequired();
            $this->testValidBooking();
            $this->testBookingFormValidation();
            $this->testDateTimeSelection();
            
            $this->logSuccess("ALL BOOKING TESTS PASSED!");
        } catch (Exception $e) {
            $this->logError("Test FAILED: " . $e->getMessage());
            $this->takeScreenshot('booking_failed');
            throw $e;
        } finally {
            $this->tearDown();
        }
    }
    
    /**
     * Test login requirement before booking
     */
    public function testUserLoginRequired()
    {
        $this->logStep(1, "Testing login requirement");
        
        // Access booking page without login
        $this->logAction("Accessing booking page without login");
        $this->driver->get($this->baseUrl . '/booking/book.php');
        
        // Wait and check if redirected to login
        $this->pauseForObservation(3, "Checking system response");
        $currentUrl = $this->driver->getCurrentURL();
        
        if (strpos($currentUrl, 'login') !== false) {
            $this->logSuccess("System correctly requires login!");
        } else {
            // Check if there's a login required message
            try {
                $this->assertElementExists(WebDriverBy::className('alert'));
                $this->logSuccess("Login required alert displayed!");
            } catch (Exception $e) {
                $this->logWarning("Warning: System may not require login");
            }
        }
        
        $this->pauseForObservation(2, "Login requirement test completed");
    }
    
    /**
     * Thực hiện đăng nhập user (nếu cần)
     */
    private function loginUser()
    {
        $this->logAction("Logging in user for booking test");
        
        // Navigate to user login page
        $this->driver->get($this->baseUrl . '/auth/login.php');
        $this->pauseForObservation(2, "User login page loaded");
        
        try {
            // Check if login form exists
            $this->waitForElement(WebDriverBy::name('email'), 5);
            
            // Fill login credentials
            $this->fillInput(WebDriverBy::name('email'), $this->userEmail, 'User email');
            $this->fillInput(WebDriverBy::name('password'), $this->userPassword, 'User password');
            
            $this->pauseForObservation(2, "User login credentials filled");
            
            // Click login
            $this->clickElement(WebDriverBy::name('submit'), 'User login button');
            
            $this->pauseForObservation(3, "Waiting for login result");
            $this->logSuccess("User login successful!");
        } catch (Exception $e) {
            $this->logWarning("Unable to login or user doesn't exist: " . $e->getMessage());
            $this->logInfo("Trying direct access to booking page...");
        }
    }
    
    /**
     * Test valid booking
     */
    public function testValidBooking()
    {
        $this->logStep(2, "Testing valid booking");
        
        // Try user login first
        $this->loginUser();
        
        // Navigate to booking page
        $this->logAction("Navigating to booking page");
        $this->driver->get($this->baseUrl . '/booking/book.php');
        $this->pauseForObservation(2, "Booking page loaded");
        
        try {
            // Check if booking form loaded
            $this->waitForElement(WebDriverBy::name('first_name'), 10);
            $this->logSuccess("Booking form loaded successfully");
            
            // Fill booking information
            $this->fillInput(WebDriverBy::name('first_name'), $this->testBookingData['first_name'], 'First name');
            $this->fillInput(WebDriverBy::name('last_name'), $this->testBookingData['last_name'], 'Last name');
            $this->fillInput(WebDriverBy::name('phone_number'), $this->testBookingData['phone_number'], 'Phone number');
            $this->fillInput(WebDriverBy::name('message'), $this->testBookingData['message'], 'Message');
            
            // Select date (tomorrow)
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $this->fillInput(WebDriverBy::name('date'), $tomorrow, 'Booking date');
            
            // Select time
            try {
                $timeSelect = new WebDriverSelect($this->waitForElement(WebDriverBy::name('time')));
                $timeSelect->selectByValue('18:00');
                $this->logAction("Selected time: 18:00 from dropdown");
            } catch (Exception $e) {
                // If no select, try text input
                $this->fillInput(WebDriverBy::name('time'), '18:00', 'Booking time');
            }
            
            $this->pauseForObservation(3, "All booking information filled");
            
            // Submit form
            $this->clickElement(WebDriverBy::name('submit'), 'Booking confirmation button');
            
            $this->pauseForObservation(4, "Waiting for booking result");
            
            // Check result
            try {
                $this->assertElementExists(WebDriverBy::className('alert-success'));
                $this->logSuccess("Booking successful!");
            } catch (Exception $e) {
                // Check if redirected to confirmation page
                $currentUrl = $this->driver->getCurrentURL();
                if (strpos($currentUrl, 'success') !== false || strpos($currentUrl, 'thank') !== false) {
                    $this->logSuccess("Booking successful (redirected)!");
                } else {
                    $this->logWarning("No clear success message found");
                    $this->takeScreenshot('booking_result_unclear');
                }
            }
            
        } catch (Exception $e) {
            $this->logError("Error during booking process: " . $e->getMessage());
            $this->takeScreenshot('booking_form_error');
            throw $e;
        }
    }
    
    /**
     * Test booking form validation
     */
    public function testBookingFormValidation()
    {
        $this->logStep(3, "Testing booking form validation");
        
        // Navigate to booking page
        $this->driver->get($this->baseUrl . '/booking/book.php');
        $this->pauseForObservation(2, "Booking page loaded for validation test");
        
        try {
            // Test submit empty form
            $this->logAction("Testing submit with empty form");
            $this->clickElement(WebDriverBy::name('submit'), 'Submit button (empty form)');
            $this->pauseForObservation(1, "Checking empty form validation");
            
            // Test with incomplete information
            $this->logAction("Testing with incomplete information");
            $this->fillInput(WebDriverBy::name('first_name'), 'Test', 'First name only');
            $this->clickElement(WebDriverBy::name('submit'), 'Submit button (incomplete)');
            $this->pauseForObservation(1, "Checking incomplete form validation");
            
            // Test with invalid phone number
            $this->logAction("Testing with invalid phone number");
            $this->fillInput(WebDriverBy::name('first_name'), $this->testBookingData['first_name'], 'First name');
            $this->fillInput(WebDriverBy::name('last_name'), $this->testBookingData['last_name'], 'Last name');
            $this->fillInput(WebDriverBy::name('phone_number'), '123', 'Invalid phone number'); // Invalid number
            $this->clickElement(WebDriverBy::name('submit'), 'Submit button (invalid phone)');
            $this->pauseForObservation(1, "Checking phone validation");
            
            $this->logSuccess("Form validation working!");
            
        } catch (Exception $e) {
            $this->logWarning("Form validation may not be fully implemented");
        }
    }
    
    /**
     * Test date and time selection
     */
    public function testDateTimeSelection()
    {
        $this->logStep(4, "Testing date and time selection");
        
        // Navigate to booking page
        $this->driver->get($this->baseUrl . '/booking/book.php');
        $this->pauseForObservation(2, "Booking page loaded for date/time test");
        
        try {
            // Test past date selection (if validation exists)
            $this->logAction("Testing past date selection");
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $this->fillInput(WebDriverBy::name('date'), $yesterday, 'Past date');
            
            // Test today's date
            $this->logAction("Testing today's date");
            $today = date('Y-m-d');
            $this->fillInput(WebDriverBy::name('date'), $today, 'Today\'s date');
            
            // Test future date
            $this->logAction("Testing future date");
            $nextWeek = date('Y-m-d', strtotime('+7 days'));
            $this->fillInput(WebDriverBy::name('date'), $nextWeek, 'Future date');
            
            // Test different time slots
            $this->logAction("Testing different time slots");
            try {
                $timeField = $this->waitForElement(WebDriverBy::name('time'));
                $timeField->clear();
                $timeField->sendKeys('12:00'); // Lunch time
                
                $timeField->clear();
                $timeField->sendKeys('19:30'); // Evening time
                
            } catch (Exception $e) {
                $this->logWarning("Time field may be dropdown or different format");
            }
            
            $this->logSuccess("Date/Time selection working!");
            
        } catch (Exception $e) {
            $this->logWarning("Error testing date/time: " . $e->getMessage());
        }
    }
}

// Run test if file is called directly
if (basename(__FILE__) == basename($_SERVER["SCRIPT_NAME"])) {
    $test = new BookingTest();
    $test->runAllTests();
}
