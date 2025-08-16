<?php
session_start();
header('Content-Type: application/json'); // JSON only

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['cart'])) {
    $_SESSION['cart'] = $data['cart'];

    echo json_encode([
        "success" => true,
        "message" => "Cart saved successfully",
        "items"   => $_SESSION['cart']
    ]);
    exit;
}

echo json_encode(["success" => false, "message" => "No cart data received"]);
exit;
