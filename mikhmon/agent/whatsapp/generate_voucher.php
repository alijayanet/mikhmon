<?php
require_once('../include/routeros_api.class.php');
require_once('wa_gateway.php');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

// Inisialisasi router
$router = new RouterosAPI();
$gateway = new WhatsAppGateway($router);

$result = $gateway->handleMessage($input['message'], $input['sender']);

echo json_encode($result); 