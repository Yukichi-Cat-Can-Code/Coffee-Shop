<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeDriverService;

/**
 * Local ChromeDriver Test Base Class
 * Uses local ChromeDriver instead of Docker Selenium
 */
abstract class LocalChromeTest
{
    protected $driver;
    protected $wait;
    protected $baseUrl;
    protected $service;
    
    public function __construct()
    {
        $this->baseUrl = 'http://localhost/Coffee-Shop'; // Local XAMPP URL
    }
    
    public function setUp()
    {
        echo "🚀 Starting local ChromeDriver...\n";
        
        // Path to local ChromeDriver
        $chromeDriverPath = 'D:\\App\\Selenium\\chromedriver-win64\\chromedriver.exe';
        
        // Check if ChromeDriver exists
        if (!file_exists($chromeDriverPath)) {
            throw new Exception("ChromeDriver not found at: $chromeDriverPath");
        }
        
        // Configure Chrome options
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments([
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--window-size=1920,1080',
            '--start-maximized',
            '--disable-web-security',
            '--disable-features=VizDisplayCompositor',
            '--enable-automation',
            '--disable-background-timer-throttling',
            '--disable-renderer-backgrounding',
            '--disable-backgrounding-occluded-windows',
            '--force-color-profile=srgb',
            '--disable-extensions',
            '--disable-default-apps'
        ]);
        
        // Set ChromeDriver path as system property
        putenv("webdriver.chrome.driver=$chromeDriverPath");
        
        // Create capabilities
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
        $capabilities->setCapability('webdriver.chrome.driver', $chromeDriverPath);
        
        try {
            // Start ChromeDriver service manually
            $port = 9515;
            $cmd = "\"$chromeDriverPath\" --port=$port";
            
            // Start ChromeDriver in background
            if (PHP_OS_FAMILY === 'Windows') {
                pclose(popen("start /B $cmd", "r"));
            } else {
                exec("$cmd > /dev/null 2>&1 &");
            }
            
            // Wait for ChromeDriver to start
            echo "⏳ Waiting for ChromeDriver to start...\n";
            sleep(3);
            
            // Connect to ChromeDriver
            $this->driver = RemoteWebDriver::create(
                "http://localhost:$port",
                $capabilities
            );
            
            $this->driver->manage()->timeouts()->implicitlyWait(10);
            $this->wait = new WebDriverWait($this->driver, 15);
            
            echo "✅ Chrome browser opened locally!\n";
            echo "🌐 Using URL: {$this->baseUrl}\n\n";
            
        } catch (Exception $e) {
            echo "❌ Failed to start Chrome: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    public function tearDown()
    {
        if ($this->driver) {
            $this->driver->quit();
            echo "🔒 Chrome browser closed\n";
        }
        
        // Kill ChromeDriver process
        if (PHP_OS_FAMILY === 'Windows') {
            exec('taskkill /F /IM chromedriver.exe 2>nul');
            echo "⏹️ ChromeDriver process stopped\n";
        }
    }
    
    /**
     * Wait for page to fully load with all resources
     */
    protected function waitForPageFullyLoaded($timeout = 30) {
        echo "   → Waiting for page to fully load...\n";
        
        // Wait for document ready state
        $this->wait->until(function($driver) {
            $readyState = $driver->executeScript('return document.readyState');
            return $readyState === 'complete';
        });
        
        // Wait for images to load
        try {
            $this->wait->until(function($driver) {
                $script = "
                    var images = document.getElementsByTagName('img');
                    for (var i = 0; i < images.length; i++) {
                        if (!images[i].complete) {
                            return false;
                        }
                    }
                    return true;
                ";
                return $driver->executeScript($script);
            });
            echo "   ✓ All images loaded\n";
        } catch (Exception $e) {
            echo "   ⚠ Some images may still be loading\n";
        }
        
        // Allow CSS animations to settle
        sleep(2);
        echo "   ✓ Page fully loaded\n";
    }
    
    /**
     * Smooth scroll to position
     */
    protected function smoothScrollTo($position, $duration = 1000) {
        $script = "
            const start = window.pageYOffset;
            const target = $position;
            const distance = target - start;
            const startTime = performance.now();
            
            function step(currentTime) {
                const elapsedTime = currentTime - startTime;
                const progress = Math.min(elapsedTime / $duration, 1);
                const ease = 0.5 * (1 - Math.cos(progress * Math.PI));
                window.scrollTo(0, start + distance * ease);
                
                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            }
            
            requestAnimationFrame(step);
        ";
        
        $this->driver->executeScript($script);
        sleep(2); // Wait for scroll to complete
    }
    
    /**
     * Log message with timestamp
     */
    protected function log($message) {
        echo date('[H:i:s] ') . $message . "\n";
    }
}
?>
