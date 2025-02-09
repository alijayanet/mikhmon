<?php
echo "<h2>Checking Installation Requirements</h2>";

// Check PHP version
echo "PHP Version: " . PHP_VERSION . " ";
if (version_compare(PHP_VERSION, '7.4.0') >= 0) {
    echo "✅";
} else {
    echo "❌ (Need PHP 7.4 or higher)";
}
echo "<br>";

// Check Node.js
$node_version = shell_exec('node -v');
echo "Node.js Version: $node_version ";
if ($node_version) {
    echo "✅";
} else {
    echo "❌ (Node.js not installed)";
}
echo "<br>";

// Check MySQL
$mysql = @new mysqli("localhost", "user", "password", "mikhmon");
echo "MySQL Connection: ";
if (!$mysql->connect_error) {
    echo "✅";
} else {
    echo "❌ (" . $mysql->connect_error . ")";
}
echo "<br>";

// Check PM2
$pm2_status = shell_exec('pm2 list | grep wa_bot');
echo "WhatsApp Bot Status: ";
if (strpos($pm2_status, 'wa_bot') !== false) {
    echo "✅ (Running)";
} else {
    echo "❌ (Not running)";
}
echo "<br>";

// Check required directories
$dirs = ['auth_info_baileys', 'uploads'];
foreach ($dirs as $dir) {
    echo "Directory /$dir: ";
    if (is_dir($dir) && is_writable($dir)) {
        echo "✅";
    } else {
        echo "❌ (Not exists or not writable)";
    }
    echo "<br>";
}
?> 