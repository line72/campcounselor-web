<?php

require_once 'vendor/autoload.php';

use App\Services\BandcampService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = new BandcampService();

echo "=== Bandcamp Fan ID Finder ===\n\n";

if ($argc < 2) {
    echo "Usage: php find_fan_id.php <bandcamp_username>\n";
    echo "Example: php find_fan_id.php myusername\n";
    echo "\nTo find your Bandcamp username:\n";
    echo "1. Go to your Bandcamp profile page\n";
    echo "2. Look at the URL: https://bandcamp.com/myusername\n";
    echo "3. Your username is the part after the last slash\n\n";
    exit(1);
}

$username = $argv[1];

echo "Looking up fan ID for username: $username\n";
echo "Checking URL: https://bandcamp.com/$username\n\n";

try {
    $fanId = $service->getFanIdFromUsername($username);
    
    if ($fanId) {
        echo "✅ Found fan ID: $fanId\n\n";
        
        echo "Testing this fan ID with your collection...\n";
        $collection = $service->fetchCollection($fanId);
        echo "Collection albums: " . count($collection) . "\n";
        
        echo "Testing this fan ID with your wishlist...\n";
        $wishlist = $service->fetchWishlist($fanId);
        echo "Wishlist albums: " . count($wishlist) . "\n\n";
        
        if (count($collection) > 0 || count($wishlist) > 0) {
            echo "🎉 Success! Use fan ID '$fanId' in the Camp Counselor app.\n";
            
            if (count($collection) > 0) {
                echo "\nSample collection album:\n";
                $sample = $collection[0];
                echo "- Artist: " . $sample['artist'] . "\n";
                echo "- Album: " . $sample['album'] . "\n";
                echo "- URL: " . $sample['url'] . "\n";
            }
            
            if (count($wishlist) > 0) {
                echo "\nSample wishlist album:\n";
                $sample = $wishlist[0];
                echo "- Artist: " . $sample['artist'] . "\n";
                echo "- Album: " . $sample['album'] . "\n";
                echo "- URL: " . $sample['url'] . "\n";
            }
        } else {
            echo "⚠️  Fan ID found but no albums in collection or wishlist.\n";
            echo "Make sure you have some albums in your Bandcamp collection or wishlist.\n";
        }
        
    } else {
        echo "❌ Could not find fan ID for username '$username'\n";
        echo "\nTroubleshooting:\n";
        echo "1. Make sure the username is correct\n";
        echo "2. Check that your Bandcamp profile is public\n";
        echo "3. Try visiting https://bandcamp.com/$username in your browser\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";
