<?php
require_once '../auth.php';
$auth->requireLogin();
require_once '../database/dbrequire.php';

header('Content-Type: application/json');

// Toggle this to false in production
define('APP_DEBUG', true);

// Let mysqli throw exceptions for easier handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$brand_id   = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;

if ($product_id <= 0 || $brand_id <= 0) {
    http_response_code(400);
    echo json_encode(
        ['success' => false, 'message' => 'Invalid parameters'],
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );
    exit;
}

try {
    // Base details (make sure pb.deleted_at exists; if not, remove that predicate)
    $sql = "
      SELECT pb.product_brand_id, pb.price, pb.Qty AS stock_qty,
             p.product_name, p.description, p.gender,
             b.brand_name
      FROM product_brand pb
      JOIN product p ON pb.product_id = p.product_id
      JOIN brand b   ON pb.brand_id = b.brand_id
      WHERE pb.product_id = ? AND pb.brand_id = ? AND (pb.deleted_at IS NULL OR pb.deleted_at IS NULL)
      LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $product_id, $brand_id);
    $stmt->execute();
    $detail = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$detail) {
        http_response_code(404);
        echo json_encode(
            ['success' => false, 'message' => 'Product not found'],
            JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );
        exit;
    }

    // Images — DO NOT order by image_id unless you're sure it exists. Use img (or created_at) or no order.
    $imgs = [];
    $img_sql = "
        SELECT img
        FROM image
        WHERE type='product' AND target_id = ?
        ORDER BY img ASC
        LIMIT 8
    ";
    $stmt2 = $conn->prepare($img_sql);
    $stmt2->bind_param("i", $product_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($r = $res2->fetch_assoc()) {
        if (!empty($r['img'])) $imgs[] = $r['img'];
    }
    $stmt2->close();

    echo json_encode([
        'success' => true,
        'data' => [
            'product_id'       => $product_id,
            'brand_id'         => $brand_id,
            'product_brand_id' => (int)$detail['product_brand_id'],
            'product_name'     => $detail['product_name'],
            'brand_name'       => $detail['brand_name'],
            'description'      => $detail['description'],
            'gender'           => $detail['gender'],
            'price'            => (float)$detail['price'],
            'stock_qty'        => (int)$detail['stock_qty'],
            'images'           => $imgs,
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $e) {
    // Log server-side
    error_log('product_detail_api error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => APP_DEBUG ? ('Server error: ' . $e->getMessage()) : 'Server error while loading product.'
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
}
