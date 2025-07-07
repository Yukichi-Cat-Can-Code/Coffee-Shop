<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;

echo "🔧 QUICK CHROMEDRIVER TEST\n";
echo "==========================\n\n";

try {
    echo "📍 Setting up ChromeDriver...\n";
    $chromeDriverPath = 'D:\\App\\Selenium\\chromedriver-win64\\chromedriver.exe';
    
    if (!file_exists($chromeDriverPath)) {
        throw new Exception("ChromeDriver not found at: $chromeDriverPath");
    }
    
    putenv("webdriver.chrome.driver=$chromeDriverPath");
    echo "   ✅ ChromeDriver path set\n";
    
    echo "\n📍 Configuring Chrome options...\n";
    $chromeOptions = new ChromeOptions();
    $chromeOptions->addArguments([
        '--no-sandbox',
        '--disable-dev-shm-usage',
        '--start-maximized'
    ]);
    
    $capabilities = DesiredCapabilities::chrome();
    $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
    echo "   ✅ Chrome options configured\n";
    
    echo "\n📍 Starting Chrome browser...\n";
    $driver = ChromeDriver::start($capabilities);
    echo "   ✅ Chrome browser opened!\n";
    
    echo "\n📍 Navigating to Coffee Shop...\n";
    $driver->get('http://localhost/Coffee-Shop');
    echo "   ✅ Navigation successful!\n";
    
    $title = $driver->getTitle();
    echo "   📄 Page title: $title\n";
    
    echo "\n📍 Test will close in 10 seconds...\n";
    echo "   👁️ Check the browser window now!\n";
    
    for ($i = 10; $i >= 1; $i--) {
        echo "   ⏱️ Closing in $i seconds...\r";
        sleep(1);
    }
    
    echo "\n\n📍 Closing browser...\n";
    $driver->quit();
    echo "   ✅ Browser closed\n";
    
    echo "\n🎉 QUICK TEST COMPLETED SUCCESSFULLY!\n";
    echo "✅ ChromeDriver is working perfectly with your setup.\n";
    
} catch (Exception $e) {
    echo "\n❌ TEST FAILED: " . $e->getMessage() . "\n";
    echo "\n🔧 Troubleshooting:\n";
    echo "   1. Make sure ChromeDriver exists at: $chromeDriverPath\n";
    echo "   2. Make sure XAMPP is running\n";
    echo "   3. Make sure Coffee-Shop is accessible at http://localhost/Coffee-Shop\n";
}
?>
