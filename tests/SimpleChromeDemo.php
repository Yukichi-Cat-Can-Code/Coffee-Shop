<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;

/**
 * Simple Local Chrome Test - Direct ChromeDriver approach
 */
class SimpleChromeDemo
{
    private $driver;
    private $baseUrl = 'http://localhost/Coffee-Shop';
    
    public function startChrome()
    {
        echo "🚀 Starting Chrome with local ChromeDriver...\n";
        
        // Set ChromeDriver path
        $chromeDriverPath = 'D:\\App\\Selenium\\chromedriver-win64\\chromedriver.exe';
        putenv("webdriver.chrome.driver=$chromeDriverPath");
        
        // Configure Chrome options
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments([
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--start-maximized'
        ]);
        
        // Create capabilities
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
        
        try {
            // Start ChromeDriver manually first
            echo "⚡ Starting ChromeDriver service...\n";
            $cmd = "start /B \"\" \"$chromeDriverPath\" --port=9515";
            pclose(popen($cmd, "r"));
            
            // Wait for service to start
            sleep(5);
            
            // Connect to ChromeDriver
            $this->driver = RemoteWebDriver::create(
                'http://localhost:9515',
                $capabilities
            );
            
            echo "✅ Chrome browser opened!\n";
            return true;
            
        } catch (Exception $e) {
            echo "❌ Failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function runSimpleDemo()
    {
        if (!$this->startChrome()) {
            return;
        }
        
        echo "\n👁️ WATCH the Chrome browser now!\n";
        echo "Press Enter when you're ready to start automation...\n";
        fgets(STDIN);
        
        try {
            // Demo 1: Homepage
            echo "📍 Opening homepage...\n";
            $this->driver->get($this->baseUrl);
            sleep(5);
            
            // Demo 2: About page
            echo "📍 Opening About page...\n";
            $this->driver->get($this->baseUrl . '/about.php');
            sleep(5);
            
            // Demo 3: Menu page
            echo "📍 Opening Menu page...\n";
            $this->driver->get($this->baseUrl . '/menu.php');
            sleep(5);
            
            // Demo 4: Scroll demo
            echo "📍 Scrolling demo...\n";
            $this->driver->executeScript('window.scrollTo(0, 500);');
            sleep(3);
            $this->driver->executeScript('window.scrollTo(0, 1000);');
            sleep(3);
            $this->driver->executeScript('window.scrollTo(0, 0);');
            sleep(3);
            
            echo "✅ Demo completed!\n";
            
        } catch (Exception $e) {
            echo "❌ Demo error: " . $e->getMessage() . "\n";
        } finally {
            echo "Browser will close in 5 seconds...\n";
            sleep(5);
            
            if ($this->driver) {
                $this->driver->quit();
            }
            
            // Kill ChromeDriver
            exec('taskkill /F /IM chromedriver.exe 2>nul');
            echo "🔒 Chrome closed and ChromeDriver stopped\n";
        }
    }
}

// Run demo
echo "🎬 SIMPLE LOCAL CHROME DEMO\n";
echo "============================\n";
echo "This uses local ChromeDriver directly\n";
echo "============================\n\n";

$demo = new SimpleChromeDemo();
$demo->runSimpleDemo();
?>
