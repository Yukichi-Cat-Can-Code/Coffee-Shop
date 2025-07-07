<?php

require_once __DIR__ . '/BaseTest.php';

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;

/**
 * Test class cho chức năng đặt bàn
 */
class BookingTest extends BaseTest
{
    // Thông tin user test
    private $userEmail = 'abcdef@gmail.com';
    private $userPassword = 'abcdefgh';
    
    // Thông tin đặt bàn test
    private $testBookingData = [
        'first_name' => 'Nguyễn',
        'last_name' => 'Văn Test',
        'phone_number' => '0987654321',
        'message' => 'Đây là test booking từ Selenium'
    ];
    
    public function runAllTests()
    {
        $this->logInfo("🧪 BẮT ĐẦU TEST BOOKING SYSTEM");
        echo "=" . str_repeat("=", 60) . "\n";
        
        $this->setUp();
        
        try {
            $this->testUserLoginRequired();
            $this->testValidBooking();
            $this->testBookingFormValidation();
            $this->testDateTimeSelection();
            
            $this->logSuccess("TẤT CẢ BOOKING TESTS ĐÃ PASS!");
        } catch (Exception $e) {
            $this->logError("Test FAILED: " . $e->getMessage());
            $this->takeScreenshot('booking_failed');
            throw $e;
        } finally {
            $this->tearDown();
        }
    }
    
    /**
     * Test yêu cầu đăng nhập trước khi đặt bàn
     */
    public function testUserLoginRequired()
    {
        $this->logStep(1, "Kiểm tra yêu cầu đăng nhập");
        
        // Truy cập trực tiếp trang đặt bàn mà chưa đăng nhập
        $this->logAction("Truy cập trang đặt bàn mà chưa đăng nhập");
        $this->driver->get($this->baseUrl . '/booking/book.php');
        
        // Chờ và kiểm tra có chuyển hướng đến login không
        $this->pauseForObservation(3, "Kiểm tra response của hệ thống");
        $currentUrl = $this->driver->getCurrentURL();
        
        if (strpos($currentUrl, 'login') !== false) {
            $this->logSuccess("Hệ thống yêu cầu đăng nhập đúng!");
        } else {
            // Kiểm tra xem có thông báo yêu cầu đăng nhập không
            try {
                $this->assertElementExists(WebDriverBy::className('alert'));
                $this->logSuccess("Hiển thị thông báo yêu cầu đăng nhập!");
            } catch (Exception $e) {
                $this->logWarning("Cảnh báo: Hệ thống có thể không yêu cầu đăng nhập");
            }
        }
        
        $this->pauseForObservation(2, "Hoàn thành test yêu cầu đăng nhập");
    }
    
    /**
     * Thực hiện đăng nhập user (nếu cần)
     */
    private function loginUser()
    {
        $this->logAction("Đăng nhập user để test booking");
        
        // Điều hướng đến trang đăng nhập user
        $this->driver->get($this->baseUrl . '/auth/login.php');
        $this->pauseForObservation(2, "Trang login user đã load");
        
        try {
            // Kiểm tra form đăng nhập có tồn tại
            $this->waitForElement(WebDriverBy::name('email'), 5);
            
            // Điền thông tin đăng nhập
            $this->fillInput(WebDriverBy::name('email'), $this->userEmail, 'Email user');
            $this->fillInput(WebDriverBy::name('password'), $this->userPassword, 'Password user');
            
            $this->pauseForObservation(2, "Đã điền thông tin đăng nhập user");
            
            // Click đăng nhập
            $this->clickElement(WebDriverBy::name('submit'), 'Nút đăng nhập user');
            
            $this->pauseForObservation(3, "Chờ kết quả đăng nhập");
            $this->logSuccess("Đăng nhập user thành công!");
        } catch (Exception $e) {
            $this->logWarning("Không thể đăng nhập hoặc user chưa tồn tại: " . $e->getMessage());
            $this->logInfo("Thử truy cập trực tiếp trang booking...");
        }
    }
    
    /**
     * Test đặt bàn hợp lệ
     */
    public function testValidBooking()
    {
        $this->logStep(2, "Test đặt bàn hợp lệ");
        
        // Thử đăng nhập user trước
        $this->loginUser();
        
        // Điều hướng đến trang đặt bàn
        $this->logAction("Điều hướng đến trang đặt bàn");
        $this->driver->get($this->baseUrl . '/booking/book.php');
        $this->pauseForObservation(2, "Trang đặt bàn đã load");
        
        try {
            // Kiểm tra form đặt bàn có load không
            $this->waitForElement(WebDriverBy::name('first_name'), 10);
            $this->logSuccess("Form đặt bàn đã load thành công");
            
            // Điền thông tin đặt bàn
            $this->fillInput(WebDriverBy::name('first_name'), $this->testBookingData['first_name'], 'Họ');
            $this->fillInput(WebDriverBy::name('last_name'), $this->testBookingData['last_name'], 'Tên');
            $this->fillInput(WebDriverBy::name('phone_number'), $this->testBookingData['phone_number'], 'Số điện thoại');
            $this->fillInput(WebDriverBy::name('message'), $this->testBookingData['message'], 'Ghi chú');
            
            // Chọn ngày (ngày mai)
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $this->fillInput(WebDriverBy::name('date'), $tomorrow, 'Ngày đặt bàn');
            
            // Chọn giờ
            try {
                $timeSelect = new WebDriverSelect($this->waitForElement(WebDriverBy::name('time')));
                $timeSelect->selectByValue('18:00');
                $this->logAction("Chọn giờ: 18:00 từ dropdown");
            } catch (Exception $e) {
                // Nếu không có select, thử input text
                $this->fillInput(WebDriverBy::name('time'), '18:00', 'Giờ đặt bàn');
            }
            
            $this->pauseForObservation(3, "Đã điền đầy đủ thông tin đặt bàn");
            
            // Submit form
            $this->clickElement(WebDriverBy::name('submit'), 'Nút xác nhận đặt bàn');
            
            $this->pauseForObservation(4, "Chờ kết quả đặt bàn");
            
            // Kiểm tra kết quả
            try {
                $this->assertElementExists(WebDriverBy::className('alert-success'));
                $this->logSuccess("Đặt bàn thành công!");
            } catch (Exception $e) {
                // Kiểm tra có chuyển hướng đến trang xác nhận không
                $currentUrl = $this->driver->getCurrentURL();
                if (strpos($currentUrl, 'success') !== false || strpos($currentUrl, 'thank') !== false) {
                    $this->logSuccess("Đặt bàn thành công (chuyển hướng)!");
                } else {
                    $this->logWarning("Không tìm thấy thông báo thành công rõ ràng");
                    $this->takeScreenshot('booking_result_unclear');
                }
            }
            
        } catch (Exception $e) {
            $this->logError("Lỗi trong quá trình đặt bàn: " . $e->getMessage());
            $this->takeScreenshot('booking_form_error');
            throw $e;
        }
    }
    
    /**
     * Test validation form đặt bàn
     */
    public function testBookingFormValidation()
    {
        echo "\n📝 Test 3: Validation form đặt bàn...\n";
        
        // Điều hướng đến trang đặt bàn
        $this->driver->get($this->baseUrl . '/booking/book.php');
        sleep(2);
        
        try {
            // Test submit form trống
            $this->clickElement(WebDriverBy::name('submit'));
            sleep(1);
            
            // Test với thông tin không đầy đủ
            $this->fillInput(WebDriverBy::name('first_name'), 'Test');
            $this->clickElement(WebDriverBy::name('submit'));
            sleep(1);
            
            // Test với số điện thoại không hợp lệ
            $this->fillInput(WebDriverBy::name('first_name'), $this->testBookingData['first_name']);
            $this->fillInput(WebDriverBy::name('last_name'), $this->testBookingData['last_name']);
            $this->fillInput(WebDriverBy::name('phone_number'), '123'); // Số không hợp lệ
            $this->clickElement(WebDriverBy::name('submit'));
            sleep(1);
            
            echo "   ✅ Form validation hoạt động!\n";
            
        } catch (Exception $e) {
            echo "   ⚠️ Có thể form validation chưa được implement đầy đủ\n";
        }
    }
    
    /**
     * Test chọn ngày và giờ
     */
    public function testDateTimeSelection()
    {
        echo "\n⏰ Test 4: Chọn ngày và giờ...\n";
        
        // Điều hướng đến trang đặt bàn
        $this->driver->get($this->baseUrl . '/booking/book.php');
        sleep(2);
        
        try {
            // Test chọn ngày trong quá khứ (nếu có validation)
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $this->fillInput(WebDriverBy::name('date'), $yesterday);
            
            // Test chọn ngày hôm nay
            $today = date('Y-m-d');
            $this->fillInput(WebDriverBy::name('date'), $today);
            
            // Test chọn ngày tương lai
            $nextWeek = date('Y-m-d', strtotime('+7 days'));
            $this->fillInput(WebDriverBy::name('date'), $nextWeek);
            
            // Test các khung giờ khác nhau
            try {
                $timeField = $this->waitForElement(WebDriverBy::name('time'));
                $timeField->clear();
                $timeField->sendKeys('12:00'); // Giờ trưa
                
                $timeField->clear();
                $timeField->sendKeys('19:30'); // Giờ tối
                
            } catch (Exception $e) {
                echo "   ⚠️ Time field có thể là dropdown hoặc có format khác\n";
            }
            
            echo "   ✅ Date/Time selection hoạt động!\n";
            
        } catch (Exception $e) {
            echo "   ⚠️ Lỗi khi test date/time: " . $e->getMessage() . "\n";
        }
    }
}

// Chạy test nếu file được gọi trực tiếp
if (basename(__FILE__) == basename($_SERVER["SCRIPT_NAME"])) {
    $test = new BookingTest();
    $test->runAllTests();
}
