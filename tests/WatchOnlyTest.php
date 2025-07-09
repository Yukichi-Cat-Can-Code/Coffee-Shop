<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/BaseTest.php';

/**
 * WATCH ONLY DEMO - No screenshots, just watch browser actions
 */
class WatchOnlyTest extends BaseTest
{
    public function runWatchDemo()
    {
        echo "\n=== 👁️ WATCH SELENIUM LIVE DEMO ===\n";
        echo "This demo is for WATCHING ONLY - no screenshots!\n";
        echo "Focus on the browser window to see automation in action.\n\n";
        
        // Setup (no screenshots directory needed)
        $this->setUp();
        
        echo "✅ Chrome browser opened!\n";
        echo "👁️ FOCUS ON THE BROWSER WINDOW NOW!\n\n";
        
        // Wait for user to position browser
        echo "Position the browser window where you can see it clearly.\n";
        echo "Press ENTER when ready to start watching...\n";
        $handle = fopen("php://stdin", "r");
        fgets($handle);
        fclose($handle);
        
        echo "\n🎬 STARTING LIVE DEMO - WATCH THE BROWSER!\n\n";
        
        // Step 1: Homepage
        echo "📍 Step 1: Opening Coffee Shop homepage...\n";
        echo "   👀 Watch the browser navigate to the homepage\n";
        $this->driver->get($this->baseUrl);
        echo "   ⏱️ Waiting 8 seconds for you to observe the homepage...\n";
        sleep(8);
        
        $title = $this->driver->getTitle();
        echo "   ✓ Current page: $title\n\n";
        
        // Step 2: Smooth scrolling
        echo "📍 Step 2: Scrolling through the homepage...\n";
        echo "   👀 Watch the smooth scrolling animation\n";
        for ($i = 0; $i <= 1000; $i += 200) {
            echo "   → Scrolling to position $i pixels...\n";
            $this->driver->executeScript("window.scrollTo(0, $i);");
            sleep(2); // 2 seconds per scroll step
        }
        
        echo "   ⏱️ Pausing 5 seconds at bottom of page...\n";
        sleep(5);
        
        // Scroll back to top slowly
        echo "   → Scrolling back to top...\n";
        for ($i = 1000; $i >= 0; $i -= 200) {
            $this->driver->executeScript("window.scrollTo(0, $i);");
            sleep(1);
        }
        
        // Step 3: Navigate to About
        echo "\n📍 Step 3: Navigating to About page...\n";
        echo "   👀 Watch the URL change and page load\n";
        $this->driver->get($this->baseUrl . '/about.php');
        echo "   ⏱️ Waiting 8 seconds to explore About page...\n";
        sleep(8);
        
        // Step 4: Navigate to Menu
        echo "\n📍 Step 4: Navigating to Menu page...\n";
        echo "   👀 Watch the menu items load\n";
        $this->driver->get($this->baseUrl . '/menu.php');
        echo "   ⏱️ Waiting 8 seconds to browse menu...\n";
        sleep(8);
        
        // Step 5: Scroll through menu
        echo "\n📍 Step 5: Exploring menu items...\n";
        echo "   👀 Watch scrolling through menu products\n";
        for ($i = 0; $i <= 800; $i += 300) {
            echo "   → Viewing menu section at $i pixels...\n";
            $this->driver->executeScript("window.scrollTo(0, $i);");
            sleep(3); // 3 seconds to read menu items
        }
        
        // Step 6: Navigate to Services
        echo "\n📍 Step 6: Checking Services page...\n";
        echo "   👀 Watch services information load\n";
        $this->driver->get($this->baseUrl . '/services.php');
        echo "   ⏱️ Waiting 8 seconds to read services...\n";
        sleep(8);
        
        // Step 7: Return to homepage
        echo "\n📍 Step 7: Returning to homepage...\n";
        echo "   👀 Watch navigation back to start\n";
        $this->driver->get($this->baseUrl);
        echo "   ⏱️ Final 10 seconds on homepage...\n";
        sleep(10);
        
        // Step 8: Window interactions
        echo "\n📍 Step 8: Testing window interactions...\n";
        echo "   👀 Watch window resize effects\n";
        
        // Make window smaller
        echo "   → Resizing to tablet view...\n";
        $this->driver->manage()->window()->setSize(new \Facebook\WebDriver\WebDriverDimension(768, 1024));
        sleep(4);
        
        // Make window mobile size
        echo "   → Resizing to mobile view...\n";
        $this->driver->manage()->window()->setSize(new \Facebook\WebDriver\WebDriverDimension(375, 667));
        sleep(4);
        
        // Back to desktop
        echo "   → Resizing back to desktop view...\n";
        $this->driver->manage()->window()->setSize(new \Facebook\WebDriver\WebDriverDimension(1200, 800));
        sleep(4);
        
        // Final scroll demo
        echo "\n📍 Final Demo: Smooth scrolling showcase...\n";
        echo "   👀 Watch final smooth scrolling demonstration\n";
        for ($i = 0; $i <= 600; $i += 50) {
            $this->driver->executeScript("window.scrollTo(0, $i);");
            usleep(500000); // 0.5 second per step for very smooth scrolling
        }
        
        echo "\n✅ LIVE DEMO COMPLETED!\n";
        echo "🎭 You have witnessed Selenium automation in action!\n";
        echo "👁️ No screenshots needed - you saw it all live!\n\n";
        
        // Keep browser open for final observation
        echo "🔔 Browser will stay open for 15 seconds for final observation...\n";
        echo "   You can interact with the page manually if you want!\n";
        sleep(15);
        
        echo "\n🎬 Demo finished! Browser will close now.\n";
    }
}

// Run watch-only demo
echo "🎬 STARTING WATCH-ONLY SELENIUM DEMO\n";
echo "====================================\n";
echo "🎯 Purpose: Watch browser automation LIVE\n";
echo "📵 No screenshots - just pure observation\n";
echo "👁️ Focus on browser window for best experience\n";
echo "====================================\n\n";

try {
    $demo = new WatchOnlyTest();
    $demo->runWatchDemo();
    echo "🎉 Watch demo completed successfully!\n";
} catch (Exception $e) {
    echo "❌ Demo failed: " . $e->getMessage() . "\n";
}
?>
