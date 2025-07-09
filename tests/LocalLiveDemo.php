<?php
require_once __DIR__ . '/LocalChromeTest.php';

/**
 * Live Demo using Local ChromeDriver - Pure Browser Watching
 */
class LocalLiveDemo extends LocalChromeTest
{
    public function runLiveDemo()
    {
        echo "\n=== 🎭 LOCAL CHROME LIVE DEMO ===\n";
        echo "🎯 Using LOCAL ChromeDriver (not Docker)\n";
        echo "📍 Chrome will open on your desktop\n";
        echo "👁️ WATCH the browser perform automation!\n\n";
        
        $this->setUp();
        
        // Give user time to position browser window
        echo "📺 Position the Chrome window where you can see it, then press Enter...\n";
        fgets(STDIN);
        
        // Demo Step 1: Homepage
        $this->log("📍 Step 1: Opening homepage...");
        $this->driver->get($this->baseUrl);
        $this->waitForPageFullyLoaded();
        
        $title = $this->driver->getTitle();
        echo "   ✓ Page title: $title\n";
        echo "   ⏸️ Observing homepage for 5 seconds...\n";
        sleep(5);
        
        // Demo Step 2: Smooth scrolling
        $this->log("📍 Step 2: Smooth scrolling demonstration...");
        echo "   → Scrolling down slowly...\n";
        $this->smoothScrollTo(500);
        echo "   → Continuing scroll...\n";
        $this->smoothScrollTo(1000);
        echo "   → Scrolling back to top...\n";
        $this->smoothScrollTo(0);
        
        // Demo Step 3: Navigation
        $this->log("📍 Step 3: Navigating to About page...");
        $this->driver->get($this->baseUrl . '/about.php');
        $this->waitForPageFullyLoaded();
        echo "   ✓ About page loaded\n";
        echo "   ⏸️ Observing About page for 5 seconds...\n";
        sleep(5);
        
        // Demo Step 4: Menu page
        $this->log("📍 Step 4: Navigating to Menu page...");
        $this->driver->get($this->baseUrl . '/menu.php');
        $this->waitForPageFullyLoaded();
        echo "   ✓ Menu page loaded\n";
        echo "   → Scrolling through menu items...\n";
        $this->smoothScrollTo(600);
        sleep(3);
        $this->smoothScrollTo(1200);
        sleep(3);
        $this->smoothScrollTo(0);
        
        // Demo Step 5: Services page
        $this->log("📍 Step 5: Navigating to Services page...");
        $this->driver->get($this->baseUrl . '/services.php');
        $this->waitForPageFullyLoaded();
        echo "   ✓ Services page loaded\n";
        echo "   ⏸️ Observing Services page for 5 seconds...\n";
        sleep(5);
        
        // Demo Step 6: Window resizing
        $this->log("📍 Step 6: Testing responsive design...");
        echo "   → Resizing to tablet size...\n";
        $this->driver->manage()->window()->setSize(new \Facebook\WebDriver\WebDriverDimension(768, 1024));
        sleep(3);
        
        echo "   → Resizing to mobile size...\n";
        $this->driver->manage()->window()->setSize(new \Facebook\WebDriver\WebDriverDimension(375, 667));
        sleep(3);
        
        echo "   → Restoring to desktop size...\n";
        $this->driver->manage()->window()->setSize(new \Facebook\WebDriver\WebDriverDimension(1920, 1080));
        sleep(2);
        
        // Demo Step 7: Interactive elements
        $this->log("📍 Step 7: Testing interactions...");
        $this->driver->get($this->baseUrl);
        $this->waitForPageFullyLoaded();
        
        try {
            // Try to find and hover over navigation elements
            $navItems = $this->driver->findElements(\Facebook\WebDriver\WebDriverBy::cssSelector('.nav-link, .navbar-nav a'));
            if (!empty($navItems)) {
                echo "   → Hovering over navigation items...\n";
                $actions = new \Facebook\WebDriver\Interactions\WebDriverActions($this->driver);
                
                foreach (array_slice($navItems, 0, 3) as $item) {
                    $actions->moveToElement($item)->perform();
                    sleep(1);
                }
                echo "   ✓ Navigation hover effects demonstrated\n";
            }
        } catch (Exception $e) {
            echo "   ⚠ Navigation interaction skipped\n";
        }
        
        // Demo Step 8: Final showcase
        $this->log("📍 Step 8: Final showcase...");
        echo "   → Quick tour of all pages...\n";
        
        $pages = [
            '/' => 'Homepage',
            '/about.php' => 'About',
            '/menu.php' => 'Menu',
            '/services.php' => 'Services',
            '/contact.php' => 'Contact'
        ];
        
        foreach ($pages as $path => $name) {
            echo "   → Loading $name...\n";
            $this->driver->get($this->baseUrl . $path);
            sleep(2);
        }
        
        // Final return to homepage
        $this->driver->get($this->baseUrl);
        $this->waitForPageFullyLoaded();
        
        echo "\n✅ LIVE DEMO COMPLETED!\n";
        echo "🎭 You just watched local Chrome automation!\n";
        echo "📍 No Docker required - pure local ChromeDriver\n";
        echo "👁️ Browser will stay open for 10 more seconds to admire...\n\n";
        
        sleep(10);
    }
}

// Run the live demo
echo "🎬 STARTING LOCAL CHROME LIVE DEMO\n";
echo "===================================\n";
echo "🖥️ Using local ChromeDriver from:\n";
echo "   D:\\App\\Selenium\\chromedriver-win64\\\n";
echo "🌐 Connecting to local XAMPP:\n";
echo "   http://localhost/Coffee-Shop\n";
echo "👁️ NO screenshots - just pure live automation!\n";
echo "===================================\n\n";

try {
    $demo = new LocalLiveDemo();
    $demo->runLiveDemo();
    $demo->tearDown();
    echo "🎉 Local Chrome demo completed successfully!\n";
} catch (Exception $e) {
    echo "❌ Demo failed: " . $e->getMessage() . "\n";
    echo "💡 Make sure:\n";
    echo "   - ChromeDriver exists at D:\\App\\Selenium\\chromedriver-win64\\\n";
    echo "   - XAMPP is running\n";
    echo "   - Website accessible at http://localhost/Coffee-Shop\n";
}
?>
