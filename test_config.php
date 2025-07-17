<?php
require __DIR__ . '/vendor/autoload.php';

echo "<h2>Config Function Test</h2>";

// Test if config function exists
if (function_exists('config')) {
    echo "✅ config() function is loaded<br>";
} else {
    echo "❌ config() function is NOT loaded<br>";
}

// Test VAT config
echo "<h3>VAT Configuration Test:</h3>";
$vatDefault = config('vat.default');
echo "VAT Default: " . ($vatDefault ? $vatDefault : 'NULL/EMPTY') . "<br>";

$vatRates = config('vat.rates');
echo "VAT Rates: " . (is_array($vatRates) ? 'Array loaded with ' . count($vatRates) . ' items' : 'NULL/EMPTY') . "<br>";

// Test database config
echo "<h3>Database Configuration Test:</h3>";
$dbHost = config('idb.host');
echo "DB Host: " . ($dbHost ? $dbHost : 'NULL/EMPTY') . "<br>";

// Show file paths for debugging
echo "<h3>Debug Info:</h3>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Config directory should be: " . __DIR__ . "/config/<br>";
echo "VAT config file path: " . __DIR__ . "/config/vat.php<br>";
echo "VAT file exists: " . (file_exists(__DIR__ . "/config/vat.php") ? 'YES' : 'NO') . "<br>";

// Show error log if any
if (function_exists('error_get_last')) {
    $lastError = error_get_last();
    if ($lastError) {
        echo "<h3>Last Error:</h3>";
        echo "<pre>" . print_r($lastError, true) . "</pre>";
    }
}
?>