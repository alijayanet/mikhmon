<?php 
if(substr($_SERVER["REQUEST_URI"], -10) == "config.php"){header("Location:./");}; 
$data['mikhmon'] = array ('1'=>'mikhmon<|<mikhmon','mikhmon>|>aWNlbA==');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'user');
define('DB_PASS', 'password');
define('DB_NAME', 'mikhmon');

// Harga voucher
define('VOUCHER_PRICES', [
    '2jam' => 3000,
    '5jam' => 5000,
    '1hari' => 10000
]);
