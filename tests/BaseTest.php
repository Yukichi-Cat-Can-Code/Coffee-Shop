<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/TestConfig.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Chrome\ChromeDriver;

/**
 * Base test class cho tất cả các test cases
 */
abstract class BaseTest
{
    protected $driver;
    protected $wait;
    protected $baseUrl;
    
    // Cấu hình từ TestConfig
    protected $seleniumHost;
    protected $implicitWait;
    protected $explicitWait;
    protected $showUI;
    protected $slowMode;
    protected $useLocalChromeDriver;
    protected $chromeDriverPath;
    
    public function __construct()
    {
        $config = TestConfig::getTestConfig();
        $this->seleniumHost = $config['seleniumHost'];
        $this->implicitWait = $config['implicitWait'];
        $this->explicitWait = $config['explicitWait'];
        $this->baseUrl = $config['baseUrl'];
        $this->showUI = $config['showUI'];
        $this->slowMode = $config['slowMode'];
        $this->useLocalChromeDriver = $config['useLocalChromeDriver'];
        $this->chromeDriverPath = $config['chromeDriverPath'];
    }
    
    public function setUp()
    {
        // Cấu hình Chrome options từ TestConfig
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments(TestConfig::getChromeOptions());
        
        // Tạo desired capabilities
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
        
        try {
            if ($this->useLocalChromeDriver) {
                // Sử dụng ChromeDriver local
                $this->logInfo("Using Local ChromeDriver: " . $this->chromeDriverPath);
                
                // Kiểm tra ChromeDriver có tồn tại không
                if (!file_exists($this->chromeDriverPath)) {
                    throw new Exception("ChromeDriver not found at: " . $this->chromeDriverPath);
                }
                
                // Thiết lập executable path cho ChromeDriver
                putenv('webdriver.chrome.driver=' . $this->chromeDriverPath);
                
                // Khởi tạo WebDriver với ChromeDriver local
                $this->driver = ChromeDriver::start($capabilities);
            } else {
                // Sử dụng Docker Selenium Server
                $this->logInfo("Using Docker Selenium Server: " . $this->seleniumHost);
                $this->driver = RemoteWebDriver::create($this->seleniumHost, $capabilities);
            }
            
            $this->driver->manage()->timeouts()->implicitlyWait($this->implicitWait);
            
            // Khởi tạo WebDriverWait
            $this->wait = new WebDriverWait($this->driver, $this->explicitWait);
            
            $this->logSuccess("WebDriver initialized successfully");
            if ($this->showUI) {
                $this->logInfo("UI Mode: Browser will show test steps");
            } else {
                $this->logInfo("Headless Mode: Browser runs in background");
            }
        } catch (Exception $e) {
            $this->logError("WebDriver initialization error: " . $e->getMessage());
            if ($this->useLocalChromeDriver) {
                $this->logInfo("Make sure ChromeDriver is available at: " . $this->chromeDriverPath);
            } else {
                $this->logInfo("Make sure Selenium Server is running at: " . $this->seleniumHost);
            }
            throw $e;
        }
    }
    
    public function tearDown()
    {
        if ($this->driver) {
            $this->driver->quit();
            $this->logInfo("WebDriver closed successfully");
        }
    }
    
    /**
     * Logging utilities với Unicode support
     */
    protected function logSuccess($message)
    {
        echo "✅ " . $this->encodeMessage($message) . "\n";
    }
    
    protected function logError($message)
    {
        echo "❌ " . $this->encodeMessage($message) . "\n";
    }
    
    protected function logInfo($message)
    {
        echo "ℹ️ " . $this->encodeMessage($message) . "\n";
    }
    
    protected function logWarning($message)
    {
        echo "⚠️ " . $this->encodeMessage($message) . "\n";
    }
    
    protected function logStep($stepNumber, $message)
    {
        echo "🔸 Step {$stepNumber}: " . $this->encodeMessage($message) . "\n";
    }
    
    protected function logAction($message)
    {
        echo "🎯 " . $this->encodeMessage($message) . "\n";
        if ($this->slowMode) {
            sleep(TestConfig::PAUSE_AFTER_ACTION); // Pause để có thể quan sát
        }
    }
    
    protected function encodeMessage($message)
    {
        // Đảm bảo encoding đúng cho Windows console
        if (PHP_OS_FAMILY === 'Windows') {
            return mb_convert_encoding($message, 'Windows-1252', 'UTF-8');
        }
        return $message;
    }
    
    /**
     * Chờ element xuất hiện và có thể click
     */
    protected function waitForElementClickable($locator, $timeout = null)
    {
        $timeout = $timeout ?: $this->explicitWait;
        $wait = new WebDriverWait($this->driver, $timeout);
        return $wait->until(WebDriverExpectedCondition::elementToBeClickable($locator));
    }
    
    /**
     * Chờ element xuất hiện
     */
    protected function waitForElement($locator, $timeout = null)
    {
        $timeout = $timeout ?: $this->explicitWait;
        $wait = new WebDriverWait($this->driver, $timeout);
        return $wait->until(WebDriverExpectedCondition::presenceOfElementLocated($locator));
    }
    
    /**
     * Chờ text xuất hiện trong element
     */
    protected function waitForText($locator, $text, $timeout = null)
    {
        $timeout = $timeout ?: $this->explicitWait;
        $wait = new WebDriverWait($this->driver, $timeout);
        return $wait->until(WebDriverExpectedCondition::textToBePresentInElement($locator, $text));
    }
    
    /**
     * Điền form một cách an toàn
     */
    protected function fillInput($locator, $value, $description = '')
    {
        $element = $this->waitForElement($locator);
        $element->clear();
        $element->sendKeys($value);
        
        if ($description) {
            $this->logAction("Điền '{$description}': {$value}");
        }
        
        if ($this->slowMode) {
            sleep(0.5);
        }
    }
    
    /**
     * Click element một cách an toàn
     */
    protected function clickElement($locator, $description = '')
    {
        $element = $this->waitForElementClickable($locator);
        $element->click();
        
        if ($description) {
            $this->logAction("Click: {$description}");
        }
        
        if ($this->slowMode) {
            sleep(1);
        }
    }
    
    /**
     * Chụp screenshot khi test fail
     */
    protected function takeScreenshot($testName)
    {
        $screenshotDir = __DIR__ . '/screenshots';
        if (!is_dir($screenshotDir)) {
            mkdir($screenshotDir, 0777, true);
        }
        
        $filename = $screenshotDir . '/' . $testName . '_' . date('Y-m-d_H-i-s') . '.png';
        $this->driver->takeScreenshot($filename);
        $this->logInfo("Screenshot saved: {$filename}");
        return $filename;
    }
    
    /**
     * Pause có thể điều khiển được
     */
    protected function pauseForObservation($seconds = null, $message = '')
    {
        if ($this->showUI) {
            $pauseTime = $seconds ?: TestConfig::PAUSE_FOR_OBSERVATION;
            if ($message) {
                $this->logInfo($message . " (Pause {$pauseTime}s for observation)");
            }
            sleep($pauseTime);
        }
    }
    
    /**
     * Kiểm tra current URL
     */
    protected function assertCurrentUrl($expectedUrl)
    {
        $currentUrl = $this->driver->getCurrentURL();
        if (strpos($currentUrl, $expectedUrl) === false) {
            throw new Exception("Expected URL to contain '{$expectedUrl}', but got '{$currentUrl}'");
        }
    }
    
    /**
     * Kiểm tra element tồn tại
     */
    protected function assertElementExists($locator)
    {
        try {
            $this->waitForElement($locator, 5);
            return true;
        } catch (Exception $e) {
            throw new Exception("Element not found: " . $locator->getValue());
        }
    }
    
    /**
     * Kiểm tra text trong element
     */
    protected function assertElementContainsText($locator, $expectedText)
    {
        $element = $this->waitForElement($locator);
        $actualText = $element->getText();
        if (strpos($actualText, $expectedText) === false) {
            throw new Exception("Expected text '{$expectedText}' not found. Actual text: '{$actualText}'");
        }
    }
    
    /**
     * Random string generator
     */
    protected function generateRandomString($length = 8)
    {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyz', ceil($length/strlen($x)))), 1, $length);
    }
    
    /**
     * Random email generator
     */
    protected function generateRandomEmail()
    {
        return 'test_' . $this->generateRandomString(8) . '@example.com';
    }
    
    /**
     * Random phone number generator
     */
    protected function generateRandomPhone()
    {
        return '09' . rand(10000000, 99999999);
    }
}
