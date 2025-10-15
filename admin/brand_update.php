<?php
header('Content-Type: application/json');
require '../database/dbrequire.php';
require '../database/central_function.php';

$brand_id   = isset($_POST['brand_id']) ? (int)$_POST['brand_id'] : 0;
$brand_name = isset($_POST['brand_name']) ? trim($_POST['brand_name']) : '';

if ($brand_id <= 0 || $brand_name === '') {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

// Optional: uniqueness check (if brand_name must be unique)
$check = $conn->prepare("SELECT brand_id FROM brand WHERE brand_name = ? AND brand_id <> ?");
$check->bind_param('si', $brand_name, $brand_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Brand name already exists']);
    exit;
}
$check->close();

$stmt = $conn->prepare("UPDATE brand SET brand_name = ?, updated_at = NOW() WHERE brand_id = ?");
$stmt->bind_param('si', $brand_name, $brand_id);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    echo json_encode(['success' => false, 'message' => 'DB update failed']);
    exit;
}

// get the new updated_at
$q = $conn->prepare("SELECT DATE_FORMAT(updated_at, '%Y-%m-%d %H:%i:%s') AS updated_at FROM brand WHERE brand_id = ?");
$q->bind_param('i', $brand_id);
$q->execute();
$q->bind_result($updated_at);
$q->fetch();
$q->close();

echo json_encode(['success' => true, 'updated_at' => $updated_at ?: null]);
