<?php

/**
 * Cấu hình cho Selenium Tests
 */
class TestConfig
{
    // Cấu hình hiển thị và tốc độ
    const SHOW_UI = true;           // true = show browser, false = headless
    const SLOW_MODE = true;         // true = slow for observation, false = fast
    const PAUSE_BETWEEN_STEPS = 2;  // Seconds to pause between steps
    const PAUSE_FOR_OBSERVATION = 3; // Seconds to pause for UI observation
    const PAUSE_AFTER_ACTION = 1;   // Seconds to pause after each action
    
    // Cấu hình ChromeDriver (Local)
    const USE_LOCAL_CHROMEDRIVER = true;  // true = local ChromeDriver, false = Docker Selenium
    const CHROMEDRIVER_PATH = 'D:\\App\\Selenium\\chromedriver-win64\\chromedriver.exe';
    const SELENIUM_HOST = 'http://localhost:4444';  // Only used if USE_LOCAL_CHROMEDRIVER = false
    const IMPLICIT_WAIT = 10;       // Giây chờ element tự động
    const EXPLICIT_WAIT = 15;       // Giây chờ điều kiện cụ thể
    
    // Cấu hình ứng dụng
    const BASE_URL = 'http://localhost/Coffee-Shop';  // Local URL for ChromeDriver
    
    // Thông tin test data
    const ADMIN_EMAIL = 'maiminh123@gmail.com';
    const ADMIN_PASSWORD = 'maiminh123';
    const USER_EMAIL = 'abcdef@gmail.com';
    const USER_PASSWORD = 'abcdefgh';
    
    // Cấu hình browser
    const BROWSER_WIDTH = 1920;
    const BROWSER_HEIGHT = 1080;
    
    // Cấu hình console output
    const ENABLE_COLORS = true;     // Màu sắc trong console
    const ENABLE_UNICODE = true;    // Unicode icons
    const ENCODING = 'UTF-8';       // Encoding cho output
    
    /**
     * Lấy thông tin cấu hình cho BaseTest
     */
    public static function getTestConfig()
    {
        return [
            'showUI' => self::SHOW_UI,
            'slowMode' => self::SLOW_MODE,
            'useLocalChromeDriver' => self::USE_LOCAL_CHROMEDRIVER,
            'chromeDriverPath' => self::CHROMEDRIVER_PATH,
            'seleniumHost' => self::SELENIUM_HOST,
            'implicitWait' => self::IMPLICIT_WAIT,
            'explicitWait' => self::EXPLICIT_WAIT,
            'baseUrl' => self::BASE_URL,
            'browserSize' => [self::BROWSER_WIDTH, self::BROWSER_HEIGHT]
        ];
    }
    
    /**
     * Lấy Chrome options
     */
    public static function getChromeOptions()
    {
        $options = [
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--disable-web-security',
            '--disable-features=VizDisplayCompositor',
            '--window-size=' . self::BROWSER_WIDTH . ',' . self::BROWSER_HEIGHT,
            '--start-maximized',
            // Options for better resource loading
            '--enable-automation',
            '--disable-background-timer-throttling',
            '--disable-renderer-backgrounding',
            '--disable-backgrounding-occluded-windows',
            '--disable-ipc-flooding-protection',
            '--force-color-profile=srgb',
            '--enable-features=NetworkService,NetworkServiceLogging',
            '--disable-extensions',
            '--disable-default-apps',
            '--disable-sync',
            // Enable image loading
            '--disable-image-loading=false',
            // Better font rendering
            '--font-render-hinting=none',
            '--disable-font-subpixel-positioning'
        ];
        
        if (!self::SHOW_UI) {
            $options[] = '--headless';
            $options[] = '--disable-gpu';
            $options[] = '--run-all-compositor-stages-before-draw';
            $options[] = '--virtual-time-budget=25000';
        }
        
        return $options;
    }
    
    /**
     * Kiểm tra có nên hiển thị màu sắc không
     */
    public static function shouldUseColors()
    {
        return self::ENABLE_COLORS && (PHP_OS_FAMILY !== 'Windows' || getenv('ANSICON') !== false);
    }
    
    /**
     * Lấy encoding phù hợp với hệ điều hành
     */
    public static function getOutputEncoding()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return 'Windows-1252';
        }
        return self::ENCODING;
    }
}
