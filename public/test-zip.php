<?php
// Test if ZipArchive class is available
if (class_exists('ZipArchive')) {
    echo "✅ ZipArchive class is available\n";
} else {
    echo "❌ ZipArchive class NOT found\n";
}

// Check if zip extension is loaded
if (extension_loaded('zip')) {
    echo "✅ zip extension is loaded\n";
} else {
    echo "❌ zip extension NOT loaded\n";
}

// Check if gd extension is loaded
if (extension_loaded('gd')) {
    echo "✅ gd extension is loaded\n";
} else {
    echo "❌ gd extension NOT loaded\n";
}

echo "\nLoaded extensions:\n";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    if (in_array(strtolower($ext), ['zip', 'gd', 'zlib'])) {
        echo "- $ext\n";
    }
}
