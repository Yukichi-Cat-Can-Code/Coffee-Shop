<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverWait;

/**
 * Enhanced Local Chrome Live Demo
 */
class EnhancedChromeDemo
{
    private $driver;
    private $wait;
    private $baseUrl = 'http://localhost/Coffee-Shop';
    
    public function startChrome()
    {
        echo "🚀 Starting Chrome with local ChromeDriver...\n";
        
        $chromeDriverPath = 'D:\\App\\Selenium\\chromedriver-win64\\chromedriver.exe';
        putenv("webdriver.chrome.driver=$chromeDriverPath");
        
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments([
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--start-maximized',
            '--disable-web-security'
        ]);
        
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
        
        try {
            echo "⚡ Starting ChromeDriver service...\n";
            $cmd = "start /B \"\" \"$chromeDriverPath\" --port=9515";
            pclose(popen($cmd, "r"));
            sleep(5);
            
            $this->driver = RemoteWebDriver::create('http://localhost:9515', $capabilities);
            $this->wait = new WebDriverWait($this->driver, 15);
            
            echo "✅ Chrome browser opened on your desktop!\n";
            return true;
            
        } catch (Exception $e) {
            echo "❌ Failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function waitForPageLoad() {
        $this->wait->until(function($driver) {
            return $driver->executeScript('return document.readyState') === 'complete';
        });
        sleep(1); // Extra wait for rendering
    }
    
    private function smoothScroll($position, $duration = 1500) {
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
        sleep(2);
    }
    
    public function runEnhancedDemo()
    {
        if (!$this->startChrome()) {
            return;
        }
        
        echo "\n🎭 ENHANCED LOCAL CHROME LIVE DEMO\n";
        echo "👁️ Watch the Chrome browser perform automation!\n";
        echo "📍 Position the browser window where you can see it clearly\n";
        echo "\nPress Enter when ready to start the show...\n";
        fgets(STDIN);
        
        try {
            // Act 1: Homepage Exploration
            echo "\n🎬 Act 1: Homepage Exploration\n";
            echo "📍 Loading Coffee Shop homepage...\n";
            $this->driver->get($this->baseUrl);
            $this->waitForPageLoad();
            
            $title = $this->driver->getTitle();
            echo "   ✓ Page loaded: $title\n";
            echo "   ⏸️ Observing layout for 5 seconds...\n";
            sleep(5);
            
            echo "   → Smooth scrolling through content...\n";
            $this->smoothScroll(500);
            $this->smoothScroll(1000);
            $this->smoothScroll(1500);
            $this->smoothScroll(0);
            
            // Act 2: Page Navigation
            echo "\n🎬 Act 2: Page Navigation\n";
            
            $pages = [
                '/about.php' => 'About Us',
                '/menu.php' => 'Menu & Products',
                '/services.php' => 'Our Services',
                '/contact.php' => 'Contact Information'
            ];
            
            foreach ($pages as $path => $name) {
                echo "📍 Navigating to $name...\n";
                $this->driver->get($this->baseUrl . $path);
                $this->waitForPageLoad();
                echo "   ✓ $name page loaded\n";
                echo "   ⏸️ Observing content...\n";
                sleep(4);
                
                // Quick scroll on each page
                $this->smoothScroll(600);
                $this->smoothScroll(0);
            }
            
            // Act 3: Responsive Design Test
            echo "\n🎬 Act 3: Responsive Design Testing\n";
            echo "📱 Testing different screen sizes...\n";
            
            // Desktop
            echo "   → Desktop view (1920x1080)...\n";
            $this->driver->manage()->window()->setSize(new \Facebook\WebDriver\WebDriverDimension(1920, 1080));
            $this->driver->get($this->baseUrl);
            $this->waitForPageLoad();
            sleep(3);
            
            // Tablet
            echo "   → Tablet view (768x1024)...\n";
            $this->driver->manage()->window()->setSize(new \Facebook\WebDriver\WebDriverDimension(768, 1024));
            sleep(3);
            
            // Mobile
            echo "   → Mobile view (375x667)...\n";
            $this->driver->manage()->window()->setSize(new \Facebook\WebDriver\WebDriverDimension(375, 667));
            sleep(3);
            
            // Back to desktop
            echo "   → Back to desktop view...\n";
            $this->driver->manage()->window()->setSize(new \Facebook\WebDriver\WebDriverDimension(1920, 1080));
            sleep(2);
            
            // Act 4: Interactive Elements
            echo "\n🎬 Act 4: Interactive Elements\n";
            echo "🖱️ Testing hover effects and interactions...\n";
            
            try {
                $navItems = $this->driver->findElements(WebDriverBy::cssSelector('.nav-link, .navbar-nav a, .menu-item'));
                if (!empty($navItems)) {
                    echo "   → Hovering over navigation elements...\n";
                    $actions = new \Facebook\WebDriver\Interactions\WebDriverActions($this->driver);
                    
                    foreach (array_slice($navItems, 0, 4) as $i => $item) {
                        $actions->moveToElement($item)->perform();
                        sleep(1);
                        echo "   ✓ Hover effect " . ($i + 1) . "\n";
                    }
                }
            } catch (Exception $e) {
                echo "   ⚠️ Interactive elements test skipped\n";
            }
            
            // Act 5: Performance Showcase
            echo "\n🎬 Act 5: Performance Showcase\n";
            echo "⚡ Rapid page switching demonstration...\n";
            
            $quickPages = ['/', '/about.php', '/menu.php', '/services.php', '/'];
            foreach ($quickPages as $i => $page) {
                echo "   → Quick load " . ($i + 1) . "/5...\n";
                $this->driver->get($this->baseUrl . $page);
                sleep(1.5);
            }
            
            // Final Act: Return to Homepage
            echo "\n🎬 Final Act: Grand Finale\n";
            echo "🏠 Returning to homepage for finale...\n";
            $this->driver->get($this->baseUrl);
            $this->waitForPageLoad();
            
            echo "   → Final smooth scroll showcase...\n";
            $this->smoothScroll(800);
            $this->smoothScroll(1600);
            $this->smoothScroll(800);
            $this->smoothScroll(0);
            
            echo "\n✅ ENHANCED DEMO COMPLETED SUCCESSFULLY!\n";
            echo "🎭 You witnessed local ChromeDriver automation:\n";
            echo "   ✓ Automatic page navigation\n";
            echo "   ✓ Smooth scrolling animations\n";
            echo "   ✓ Responsive design testing\n";
            echo "   ✓ Interactive element testing\n";
            echo "   ✓ Performance demonstrations\n";
            echo "\n🎉 No Docker required - pure local automation!\n";
            
        } catch (Exception $e) {
            echo "❌ Demo error: " . $e->getMessage() . "\n";
        } finally {
            echo "\nBrowser will close in 8 seconds...\n";
            sleep(8);
            
            if ($this->driver) {
                $this->driver->quit();
            }
            
            exec('taskkill /F /IM chromedriver.exe 2>nul');
            echo "🔒 Chrome closed and ChromeDriver stopped\n";
        }
    }
}

// Run enhanced demo
echo "🎬 ENHANCED LOCAL CHROME LIVE DEMO\n";
echo "===================================\n";
echo "🖥️ ChromeDriver: D:\\App\\Selenium\\chromedriver-win64\\\n";
echo "🌐 Website: http://localhost/Coffee-Shop\n";
echo "⏱️ Duration: ~5-6 minutes of pure automation\n";
echo "👁️ NO screenshots - just live browser control!\n";
echo "===================================\n\n";

$demo = new EnhancedChromeDemo();
$demo->runEnhancedDemo();
?>
