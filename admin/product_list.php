<?php
require '../database/dbrequire.php';
require '../plugins/common.php';
require '../database/central_function.php';

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
$delete_id = isset($_GET['delete_id']) ? $_GET['delete_id'] : '';

// Only look up the product if we actually have a delete id
if ($delete_id > 0) {
    $product_res = $conn->query("SELECT product_id FROM product_brand WHERE product_brand_id = {$delete_id} LIMIT 1");
    $get = $product_res ? $product_res->fetch_assoc() : null;
    $delete_product_id = $get['product_id'] ?? null;

    // perform deletes
    $res_product_brand = deleteData('product_brand', $conn, "product_brand_id={$delete_id}");
    $res_product_delete = $delete_product_id ? deleteData('product', $conn, "product_id={$delete_product_id}") : true;
    $res_img = deleteData('image', $conn, "type='product' and target_id = $delete_product_id");

    if ($res_product_brand && $res_product_delete) {
        header("Location: {$admin_base_url}product_list.php?success=Product deleted successfully.");
        exit;
    } else {
        header("Location: {$admin_base_url}product_list.php?error=Delete failed!");
        exit;
    }
}

$brandRes = $conn->query("SELECT brand_id, brand_name FROM brand WHERE deleted_at IS NULL");

$query = "
    SELECT 
        pd.product_brand_id,
        pd.product_id,
        pd.brand_id,
        pd.price,
        pd.Qty AS qty,
        pd.created_at,
        p.product_name,
        p.description,
        p.gender,
        b.brand_name,
        i.img
    FROM product_brand pd
    JOIN product p ON pd.product_id = p.product_id
    JOIN brand b   ON pd.brand_id = b.brand_id
    LEFT JOIN image i ON i.target_id = p.product_id AND i.type = 'product'
    ORDER BY pd.product_brand_id DESC
";
$res = $conn->query($query);

// require './layout/header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.14);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, .25);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 18px;
        border: 1px solid rgba(255, 255, 255, 0.22);
    }

    .glass-header {
        background: linear-gradient(135deg, rgba(25, 25, 35, .6), rgba(55, 55, 75, .35));
        color: #fff;
        border-top-left-radius: 18px;
        border-top-right-radius: 18px;
    }

    .table-thumb {
        width: 46px;
        height: 46px;
        object-fit: cover;
    }

    .img-preview {
        width: 100%;
        max-height: 220px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px dashed rgba(0, 0, 0, .1);
    }

    .form-label small {
        opacity: .7;
    }
</style>

<?php
require_once './layout/header.php';
?>
<div class="content-body">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <h1>Products List</h1>
            <div class="">
                <a href="<?= $admin_base_url . 'product_create.php' ?>" class="btn btn-primary">
                    Create New Product
                </a>
            </div>
        </div>
        <div class="col-md-4 offset-md-8 col-sm-6 offset-sm-6">

            <?php if ($success !== '') { ?>
                <div class="alert alert-success">
                    <?= $success ?>
                </div>
            <?php } ?>
            <?php if ($error !== '') { ?>
                <div class="alert alert-danger">
                    <?= $error ?>
                </div>
            <?php } ?>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                            <h6 class="text-white text-capitalize ps-3">Product List</h6>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">

                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="productTable">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Price</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Stock</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                        <th class="text-secondary opacity-7">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($res && $res->num_rows > 0): while ($row = $res->fetch_assoc()): ?>
                                            <tr data-row-product-brand-id="<?= (int)$row['product_brand_id'] ?>">
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div>
                                                            <img src="./upload/<?= htmlspecialchars($row['img'] ?? 'no-image.png') ?>" class="avatar avatar-sm me-3 border-radius-lg table-thumb" alt="product">
                                                        </div>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm product-name"><?= htmlspecialchars($row['product_name']) ?></h6>
                                                            <p class="text-xs text-secondary mb-0 brand-name"><?= htmlspecialchars($row['brand_name']) ?></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs text-secondary mb-0 price-val"><?= htmlspecialchars($row['price']) ?></p>
                                                    <p class="text-xs font-weight-bold mb-0">SGD</p>
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    <span class="badge badge-sm text-secondary qty-val"><?= (int)$row['qty'] ?></span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-secondary text-xs font-weight-bold created-at"><?= htmlspecialchars($row['created_at']) ?></span>
                                                </td>
                                                <td class="align-middle">
                                                    <button
                                                        class="btn btn-sm btn-primary edit-btn"
                                                        data-product-brand-id="<?= (int)$row['product_brand_id'] ?>"
                                                        data-product-id="<?= (int)$row['product_id'] ?>"
                                                        data-brand-id="<?= (int)$row['brand_id'] ?>"
                                                        data-product-name="<?= htmlspecialchars($row['product_name']) ?>"
                                                        data-description="<?= htmlspecialchars($row['description']) ?>"
                                                        data-gender="<?= htmlspecialchars($row['gender']) ?>"
                                                        data-brand-name="<?= htmlspecialchars($row['brand_name']) ?>"
                                                        data-price="<?= htmlspecialchars($row['price']) ?>"
                                                        data-qty="<?= (int)$row['qty'] ?>"
                                                        data-img="<?= htmlspecialchars($row['img'] ?? '') ?>">Edit</button>

                                                    <button data-id="<?= $row['product_brand_id'] ?>" class="btn btn-sm btn-danger delete_btn">Delete</button>
                                                </td>
                                            </tr>
                                    <?php endwhile;
                                    endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="editProductForm" class="modal-content glass-panel" enctype="multipart/form-data">
            <div class="modal-header glass-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="product_brand_id" id="edit_product_brand_id">
                <input type="hidden" name="product_id" id="edit_product_id">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="edit_product_name" name="product_name" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Brand</label>
                        <select class="form-select" id="edit_brand_id" name="brand_id" required>
                            <option value="" disabled>Choose brand</option>
                            <?php if (isset($brandRes) && $brandRes && $brandRes->num_rows > 0): ?>
                                <?php while ($b = $brandRes->fetch_assoc()): ?>
                                    <option value="<?= (int)$b['brand_id'] ?>"><?= htmlspecialchars($b['brand_name']) ?></option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" placeholder="Product details..."></textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Gender <small>(Male/Female/Unisex)</small></label>
                        <select class="form-select" id="edit_gender" name="gender">
                            <option value="">—</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Unisex">Unisex</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Price (SGD)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="edit_price" name="price" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Stock (Qty)</label>
                        <input type="number" min="0" class="form-control" id="edit_qty" name="qty" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Current Image</label>
                        <img id="old_image_preview" class="img-preview" src="" alt="Current image">
                        <input type="hidden" id="old_img_name" name="old_img_name">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">New Image <small>(optional)</small></label>
                        <input type="file" class="form-control" id="new_image" name="new_image" accept=".jpg,.jpeg,.png,.webp">
                        <div class="mt-2">
                            <img id="new_image_preview" class="img-preview" src="" alt="New image preview" style="display:none;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
    </div>
</div>



<!-- SweetAlert success/error messages -->
<?php if ($success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?= $success ?>',
            confirmButtonColor: '#3085d6'
        });
    </script>
<?php endif; ?>
<?php if ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?= $error ?>',
            confirmButtonColor: '#d33'
        });
    </script>
<?php endif; ?>

<!-- keep ONE jQuery, ONE SweetAlert, ONE Bootstrap bundle -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(function() {
        // DELETE (single binding, single handler)
        $(document).on('click', '.delete_btn', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'product_list.php?delete_id=' + encodeURIComponent(id);
                }
            });
        });

        const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));

        // OPEN MODAL (unchanged)
        $(document).on('click', '.edit-btn', function() {
            const b = $(this).data();
            $('#edit_product_brand_id').val(b.productBrandId);
            $('#edit_product_id').val(b.productId);
            $('#edit_product_name').val(b.productName);
            $('#edit_description').val(b.description || '');
            $('#edit_gender').val(b.gender || '');
            $('#edit_brand_id').val(b.brandId);
            $('#edit_price').val(b.price);
            $('#edit_qty').val(b.qty);

            const oldImg = b.img ? ('./upload/' + b.img) : '';
            $('#old_image_preview').attr('src', oldImg || 'data:image/gif;base64,R0lGODlhAQABAAAAACw=');
            $('#old_img_name').val(b.img || '');

            $('#new_image').val('');
            $('#new_image_preview').hide().attr('src', '');

            editModal.show();
        });

        // LIVE PREVIEW (unchanged)
        $('#new_image').on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return $('#new_image_preview').hide();
            const url = URL.createObjectURL(file);
            $('#new_image_preview').attr('src', url).show();
        });

        // SUBMIT via AJAX (unchanged)
        $('#editProductForm').on('submit', function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            $.ajax({
                url: 'product_full_update.php',
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                dataType: 'json'
            }).done(function(resp) {
                if (resp && resp.success) {
                    const row = $('tr[data-row-product-brand-id="' + resp.data.product_brand_id + '"]');
                    row.find('.product-name').text(resp.data.product_name);
                    row.find('.brand-name').text(resp.data.brand_name);
                    row.find('.price-val').text(resp.data.price);
                    row.find('.qty-val').text(resp.data.qty);
                    if (resp.data.img) row.find('img.table-thumb').attr('src', './upload/' + resp.data.img);
                    editModal.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: 'Saved successfully.'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Update failed',
                        text: (resp && resp.error) ? resp.error : 'Unknown error'
                    });
                }
            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Network error',
                    text: 'Could not update product.'
                });
            });
        });
    });
</script>




<?php require './layout/footer.php'; ?>