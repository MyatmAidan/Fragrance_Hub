<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../database/dbrequire.php';
select_db($conn);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['recepties_id'])) {
    $recepties_id = (int) $_GET['recepties_id'];

    $order_sql = "
    SELECT 
        p.product_name,
        b.brand_name,
        o.qty,
        pb.price,
        o.recepties_id,
        (o.qty * pb.price) AS total
    FROM `order` o
    JOIN product_brand pb ON o.product_brand_id = pb.product_brand_id
    JOIN product p ON pb.product_id = p.product_id
    JOIN brand b ON pb.brand_id = b.brand_id
    WHERE o.recepties_id = ?
";

    $stmt1 = $conn->prepare($order_sql);

    if (!$stmt1) {
        http_response_code(500);
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt1->bind_param('i', $recepties_id);
    $stmt1->execute();
    $order_res = $stmt1->get_result();

    $order = [];
    while ($row = $order_res->fetch_assoc()) {
        $order[] = $row;
    }
    $stmt1->close();

    if (empty($order)) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        exit;
    }

    // Query 2: get order items
    $items_sql = "SELECT 
    u.user_name AS user_name,
    u.email,
    r.recepties_id,
    r.total,
    r.date AS created_at
FROM recepties r
JOIN user u ON r.user_id = u.user_id
WHERE r.recepties_id = ?
";

    $stmt2 = $conn->prepare($items_sql);
    if (!$stmt2) {
        http_response_code(500);
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt2->bind_param('i', $recepties_id);
    $stmt2->execute();
    $recepties_res = $stmt2->get_result();

    $recepties = $recepties_res->fetch_assoc();
    $stmt2->close();

    echo json_encode([
        'status' => 'success',
        'order' => $order,
        'recepties' => $recepties
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
exit;
