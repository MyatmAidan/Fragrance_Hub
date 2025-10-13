<?php
// product_full_update.php
header('Content-Type: application/json');

require '../database/dbrequire.php';

// (optional but helpful for debugging)
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function fail($msg)
{
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}
function inpost($k, $d = '')
{
    return isset($_POST[$k]) ? trim($_POST[$k]) : $d;
}

$product_brand_id = (int) inpost('product_brand_id');
$product_id       = (int) inpost('product_id');
$brand_id         = (int) inpost('brand_id');
$product_name     = inpost('product_name');
$description      = inpost('description');
$gender           = inpost('gender');
$price_raw        = inpost('price');
$qty_raw          = inpost('qty');
$old_img_name     = inpost('old_img_name');

if ($product_brand_id <= 0 || $product_id <= 0 || $brand_id <= 0) fail('Invalid IDs.');
if ($product_name === '') fail('Product name is required.');
if (!is_numeric($price_raw) || !is_numeric($qty_raw)) fail('Price/Qty must be numeric.');

$price = (float)$price_raw;
$qty   = (int)$qty_raw;

// handle optional upload
$new_img_filename = '';
if (!empty($_FILES['new_image']['name'])) {
    $f = $_FILES['new_image'];
    if ($f['error'] !== UPLOAD_ERR_OK) fail('File upload error.');

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $f['tmp_name']);
    finfo_close($finfo);
    if (!isset($allowed[$mime])) fail('Only JPG, PNG, WEBP allowed.');
    if ($f['size'] > 5 * 1024 * 1024) fail('File too large (max 5MB).');

    $ext = $allowed[$mime];
    $new_img_filename = 'prod_' . $product_id . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;

    // adjust path to your upload directory
    $destDir = __DIR__ . '/upload';
    if (!is_dir($destDir)) {
        @mkdir($destDir, 0775, true);
    }
    $dest = $destDir . '/' . $new_img_filename;
    if (!move_uploaded_file($f['tmp_name'], $dest)) fail('Failed to save image.');
}

$conn->begin_transaction();
try {
    // 1) product
    $stmt1 = $conn->prepare("UPDATE product SET product_name=?, description=?, gender=? WHERE product_id=?");
    $stmt1->bind_param('sssi', $product_name, $description, $gender, $product_id);
    $stmt1->execute();
    $stmt1->close();

    // 2) product_brand (price, Qty, brand_id)
    $stmt2 = $conn->prepare("UPDATE product_brand SET price=?, Qty=?, brand_id=? WHERE product_brand_id=? AND product_id=?");
    $stmt2->bind_param('diiii', $price, $qty, $brand_id, $product_brand_id, $product_id);
    $stmt2->execute();
    $stmt2->close();

    // 3) image (type='product', target_id = product_id as string)
    if ($new_img_filename !== '') {
        $pid_str = (string)$product_id;

        // check existence
        $stmt3 = $conn->prepare("SELECT 1 FROM image WHERE type='product' AND target_id=? LIMIT 1");
        $stmt3->bind_param('s', $pid_str);
        $stmt3->execute();
        $stmt3->store_result();
        $exists = $stmt3->num_rows > 0;
        $stmt3->close();

        if ($exists) {
            $stmt4 = $conn->prepare("UPDATE image SET img=?, updated_at=CURRENT_TIMESTAMP WHERE type='product' AND target_id=?");
            $stmt4->bind_param('ss', $new_img_filename, $pid_str);
            $stmt4->execute();
            $stmt4->close();
        } else {
            $stmt4b = $conn->prepare("INSERT INTO image (type, target_id, img) VALUES ('product', ?, ?)");
            $stmt4b->bind_param('ss', $pid_str, $new_img_filename);
            $stmt4b->execute();
            $stmt4b->close();
        }
    }

    // 4) fetch brand name for response
    $stmt5 = $conn->prepare("SELECT brand_name FROM brand WHERE brand_id=?");
    $stmt5->bind_param('i', $brand_id);
    $stmt5->execute();
    $stmt5->bind_result($brand_name);
    $stmt5->fetch();
    $stmt5->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'data' => [
            'product_brand_id' => $product_brand_id,
            'product_id'       => $product_id,
            'brand_id'         => $brand_id,
            'brand_name'       => $brand_name ?? '',
            'product_name'     => $product_name,
            'price'            => number_format($price, 2, '.', ''),
            'qty'              => $qty,
            'img'              => ($new_img_filename !== '' ? $new_img_filename : $old_img_name)
        ]
    ]);
} catch (Throwable $e) {
    $conn->rollback();
    fail($e->getMessage());
}
