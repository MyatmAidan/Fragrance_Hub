<?php
require_once '../auth.php';

// Require login for this page
$auth->requireLogin();

// Get current user data
$currentUser = $auth->getCurrentUser();

// Get search parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$brand_filter = isset($_GET['brand']) ? $_GET['brand'] : '';
$gender_filter = isset($_GET['gender']) ? $_GET['gender'] : '';

// Build query for products
$where_conditions = ["pb.deleted_at IS NULL"];
$params = [];
$param_types = "";

if (!empty($search)) {
    $where_conditions[] = "(p.product_name LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "ss";
}

if (!empty($brand_filter)) {
    $where_conditions[] = "b.brand_id = ?";
    $params[] = $brand_filter;
    $param_types .= "i";
}

if (!empty($gender_filter)) {
    $where_conditions[] = "p.gender = ?";
    $params[] = $gender_filter;
    $param_types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

$query = "
    SELECT pb.product_id, pb.brand_id, pb.price, pb.Qty, 
           p.product_name, p.description, p.gender,
           b.brand_name, i.img
    FROM product_brand pb
    JOIN product p ON pb.product_id = p.product_id
    JOIN brand b ON pb.brand_id = b.brand_id
    LEFT JOIN image i ON pb.product_id = i.target_id AND i.type = 'product'
    WHERE $where_clause
    ORDER BY pb.created_at DESC
";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Get brands for filter
$brands_query = "SELECT brand_id, brand_name FROM brand WHERE deleted_at IS NULL ORDER BY brand_name";
$brands_result = $conn->query($brands_query);

include './layout/user_header.php';
?>



<div class="container-fluid py-4">
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Filter Products</h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search products...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" name="brand">
                                <option value="">All Brands</option>
                                <?php while ($brand = $brands_result->fetch_assoc()) { ?>
                                    <option value="<?= $brand['brand_id'] ?>" <?= $brand_filter == $brand['brand_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($brand['brand_name']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" name="gender">
                                <option value="">All</option>
                                <option value="Male" <?= $gender_filter == 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= $gender_filter == 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Unisex" <?= $gender_filter == 'Unisex' ? 'selected' : '' ?>>Unisex</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn bg-gradient-dark"><i class="material-symbols-rounded me-1" style="font-size:1rem;">search</i>Filter</button>
                            <a href="products.php" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row">
        <?php if ($products->num_rows > 0) {
            while ($product = $products->fetch_assoc()) { ?>
                <div class="col-lg-3 col-md-4 mb-4">
                    <div class="card product-card">
                        <!-- Image -->
                        <div class="position-relative" style="height:160px;overflow:hidden;border-radius:12px 12px 0 0;">
                            <?php if ($product['img']) { ?>
                                <img src="../admin/upload/<?= htmlspecialchars($product['img']) ?>" class="card-img-top h-100" style="object-fit:cover;" alt="<?= htmlspecialchars($product['product_name']) ?>">
                            <?php } else { ?>
                                <div class="d-flex align-items-center justify-content-center h-100 bg-gradient-light">
                                    <i class="material-symbols-rounded text-muted" style="font-size:4rem;">image</i>
                                </div>
                            <?php } ?>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-gradient-success"><?= $product['Qty'] ?> in stock</span>
                            </div>
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-gradient-info"><?= htmlspecialchars($product['gender']) ?></span>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="card-body p-3">
                            <div class="mb-1"><span class="text-primary text-sm fw-bold"><?= htmlspecialchars($product['brand_name']) ?></span></div>
                            <h6 class="mb-1" style="font-weight:600;color:#2c3e50;"><?= htmlspecialchars($product['product_name']) ?></h6>
                            <p class="card-text text-muted mb-1" style="font-size:.85rem;"><?= htmlspecialchars(substr($product['description'], 0, 60)) ?>...</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="h6 text-success mb-0">$<?= number_format($product['price'], 2) ?></span>
                                <small class="text-muted">Per bottle</small>
                            </div>

                            <!-- Actions: side by side -->
                            <div class="d-flex gap-2">
                                <button class="btn bg-gradient-primary text-white flex-fill" style="border-radius:8px;padding:.55rem;"
                                    onclick="addToCart(<?= $product['product_id'] ?>, <?= $product['brand_id'] ?>, '<?= htmlspecialchars($product['product_name']) ?>', <?= $product['price'] ?>, '<?= htmlspecialchars($product['img'] ?? '') ?>', '<?= htmlspecialchars($product['brand_name']) ?>', <?= $product['Qty'] ?>)">
                                    <i class="material-symbols-rounded me-1" style="font-size:1rem;">shopping_cart</i>
                                </button>
                                <button class="btn btn-outline-primary flex-fill"
                                    onclick="viewProduct(<?= $product['product_id'] ?>, <?= $product['brand_id'] ?>)">
                                    <i class="material-symbols-rounded me-1" style="font-size:1rem;">visibility</i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php }
        } else { ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="material-symbols-rounded text-muted mb-3" style="font-size:4rem;">search_off</i>
                        <h5 class="text-muted">No products found</h5>
                        <p class="text-muted">Try adjusting your search criteria or browse all products.</p>
                        <a href="products.php" class="btn bg-gradient-dark"><i class="material-symbols-rounded me-1" style="font-size:1rem;">refresh</i>View All Products</a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
</main>

<!-- Wix-style Cart Modal -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-product">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="cartModalLabel">Add to Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-0">
                <form id="cartForm">
                    <input type="hidden" id="cartProductId" name="product_id">
                    <input type="hidden" id="cartBrandId" name="brand_id">

                    <div class="row g-4 align-items-start">
                        <!-- Left: Image -->
                        <div class="col-md-6">
                            <div class="product-media rounded-3 d-flex align-items-center justify-content-center">
                                <img id="productImage" src="" class="img-fluid" alt="Product Image">
                            </div>
                        </div>

                        <!-- Right: Details -->
                        <div class="col-md-6">
                            <h4 class="mb-1" id="cartProductName"></h4>
                            <p class="text-secondary mb-2" id="cartBrandName"></p>

                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="h5 text-success mb-0" id="cartPrice"></span>
                                <span class="text-muted">•</span>
                                <span class="badge bg-gradient-info" id="stockInfo"></span>
                            </div>

                            <div class="mb-3">
                                <label class="form-label d-block mb-1">Quantity</label>
                                <div class="input-group" style="max-width:220px;">
                                    <button class="btn btn-outline-secondary" type="button" onclick="decreaseQuantity()">−</button>
                                    <input type="number" class="form-control text-center" id="cartQuantity" name="quantity" min="1" value="1" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="increaseQuantity()">+</button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <span class="text-muted me-2">Total:</span>
                                <strong id="cartTotalText">$0.00</strong>
                                <input type="hidden" id="cartTotal" name="total">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-dark btn-lg" onclick="addToCartConfirm()">Add to Cart</button>
                                <a href="#" id="productDetailLink" class="link-secondary text-decoration-underline">View More Details</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Product Detail Modal (READ-ONLY) -->
<div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-product">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="productDetailModalLabel">Product Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-0">
                <div id="pdLoader" class="text-center py-4" style="display:none;">
                    <div class="spinner-border" role="status"></div>
                    <div class="mt-2">Loading...</div>
                </div>

                <div id="pdContent" style="display:none;">
                    <div class="row g-4 align-items-start">
                        <!-- Left: Gallery -->
                        <div class="col-md-6">
                            <div id="pdGallery" class="position-relative rounded-3 d-flex align-items-center justify-content-center" style="background:#f6f7f9; min-height:360px; overflow:hidden;">
                                <!-- main image injected here -->
                            </div>
                            <div id="pdThumbs" class="d-flex gap-2 mt-2 flex-wrap">
                                <!-- thumbnails injected here -->
                            </div>
                        </div>
                        <!-- Right: Detail (read-only) -->
                        <div class="col-md-6">
                            <h4 class="mb-1" id="pdName"></h4>
                            <p class="text-secondary mb-2" id="pdBrand"></p>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-gradient-info" id="pdGender"></span>
                                <span class="badge" id="pdStock"></span>
                            </div>
                            <div class="h5 text-success mb-3" id="pdPrice"></div>
                            <p class="text-muted" id="pdDesc"></p>

                            <div class="mt-4 d-flex gap-2">
                                <a href="#" id="pdOpenFull" class="btn btn-outline-secondary">Open full page</a>
                                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="pdError" class="alert alert-danger" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>



<!-- Core JS -->
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/core/bootstrap.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>

<style>
    .product-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        cursor: pointer;
        height: 100%;
    }

    .product-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    }

    .product-card .card-img-top {
        transition: transform 0.3s ease;
    }

    .product-card:hover .card-img-top {
        transform: scale(1.05);
    }

    .badge {
        font-size: .75rem;
        padding: .45rem .75rem;
    }

    .modal-product .modal-body {
        padding-top: .25rem;
    }

    .product-media {
        background: #f6f7f9;
        min-height: 360px;
        overflow: hidden;
    }

    .product-media img {
        max-height: 90%;
        object-fit: contain;
    }

    @media (max-width: 768px) {
        .product-media {
            min-height: 260px;
        }

        .product-card:hover {
            transform: translateY(-3px);
        }
    }
</style>

<script>
    // Smooth scrollbar for Windows
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), {
            damping: '0.5'
        });
    }

    // Utilities
    const money = v => '$' + Number(v).toFixed(2);
    let currentProductPrice = 0;
    let currentStock = 0;

    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            fetch('../auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=logout'
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    window.location.href = '../login.php';
                } else {
                    window.location.href = '../login.php';
                }
            }).catch(() => window.location.href = '../login.php');
        }
    }

    // Search submit on Enter
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const searchTerm = this.value.trim();
            const url = new URL(window.location);
            if (searchTerm) url.searchParams.set('search', searchTerm);
            else url.searchParams.delete('search');
            window.location.href = url.toString();
        }
    });

    // ====== CART LOGIC (Wix-style modal) ======
    function addToCart(productId, brandId, productName, price, image, brandName, stock = 100) {
        currentProductPrice = price;
        currentStock = stock;

        // Fill fields
        document.getElementById('cartProductId').value = productId;
        document.getElementById('cartBrandId').value = brandId;
        document.getElementById('cartProductName').textContent = productName;
        document.getElementById('cartBrandName').textContent = brandName;
        document.getElementById('cartPrice').textContent = money(price);
        document.getElementById('stockInfo').textContent = stock + ' in stock';

        // Quantity & totals
        const qtyInput = document.getElementById('cartQuantity');
        qtyInput.value = 1;
        qtyInput.max = stock;
        document.getElementById('cartTotal').value = price.toFixed(2);
        document.getElementById('cartTotalText').textContent = money(price);

        // Image
        const productImage = document.getElementById('productImage');
        if (image && image.trim() !== '') {
            productImage.src = '../admin/upload/' + image;
            productImage.style.display = 'block';
        } else {
            productImage.style.display = 'none';
        }

        // Detail link
        document.getElementById('productDetailLink').href = `product_detail.php?id=${productId}&brand=${brandId}`;

        // Show modal
        const cartModal = new bootstrap.Modal(document.getElementById('cartModal'));
        cartModal.show();
    }

    function increaseQuantity() {
        const quantityInput = document.getElementById('cartQuantity');
        const currentQty = parseInt(quantityInput.value) || 1;
        const maxQty = parseInt(quantityInput.max) || currentStock;
        if (currentQty < maxQty) {
            quantityInput.value = currentQty + 1;
            updateTotal();
        }
    }

    function decreaseQuantity() {
        const quantityInput = document.getElementById('cartQuantity');
        const currentQty = parseInt(quantityInput.value) || 1;
        if (currentQty > 1) {
            quantityInput.value = currentQty - 1;
            updateTotal();
        }
    }

    function updateTotal() {
        const quantity = parseInt(document.getElementById('cartQuantity').value) || 1;
        const total = currentProductPrice * quantity;
        document.getElementById('cartTotal').value = total.toFixed(2);
        document.getElementById('cartTotalText').textContent = money(total);
    }

    // Update total when quantity changes manually
    document.addEventListener('input', function(e) {
        if (e.target && e.target.id === 'cartQuantity') {
            updateTotal();
        }
    });

    function addToCartConfirm() {
        const formData = new FormData(document.getElementById('cartForm'));
        formData.append('action', 'add_to_cart');

        fetch('cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Product added to cart successfully!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('cartModal')).hide();
                } else {
                    showNotification('Error: ' + (data.message || 'Unable to add to cart'), 'error');
                }
            })
            .catch(() => showNotification('An error occurred. Please try again.', 'error'));
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;
        document.body.appendChild(notification);
        setTimeout(() => {
            notification?.remove();
        }, 3000);
    }

    function viewProduct(productId, brandId) {
        const modalEl = document.getElementById('productDetailModal');
        const modal = new bootstrap.Modal(modalEl);

        // Reset UI
        document.getElementById('pdLoader').style.display = 'block';
        document.getElementById('pdContent').style.display = 'none';
        document.getElementById('pdError').style.display = 'none';

        modal.show();

        fetch(`product_detail_api.php?id=${encodeURIComponent(productId)}&brand=${encodeURIComponent(brandId)}`, {
                credentials: 'same-origin'
            })
            .then(r => r.json())
            .then(d => {
                document.getElementById('pdLoader').style.display = 'none';

                if (!d.success) {
                    document.getElementById('pdError').textContent = d.message || 'Unable to load product.';
                    document.getElementById('pdError').style.display = 'block';
                    return;
                }

                const p = d.data;

                // Fill basics
                document.getElementById('pdName').textContent = p.product_name;
                document.getElementById('pdBrand').textContent = p.brand_name;
                document.getElementById('pdGender').textContent = p.gender || '—';
                const stockEl = document.getElementById('pdStock');
                const inStock = (p.stock_qty ?? 0) > 0;
                stockEl.textContent = (p.stock_qty ?? 0) + (inStock ? ' in stock' : ' out of stock');
                stockEl.className = 'badge ' + (inStock ? 'bg-gradient-success' : 'bg-gradient-secondary');
                document.getElementById('pdPrice').textContent = '$' + Number(p.price).toFixed(2);
                document.getElementById('pdDesc').textContent = p.description || '';

                // Build gallery
                const gallery = document.getElementById('pdGallery');
                const thumbs = document.getElementById('pdThumbs');
                gallery.innerHTML = '';
                thumbs.innerHTML = '';

                const images = (p.images && p.images.length) ? p.images : [];
                const mainImg = document.createElement('img');
                mainImg.className = 'img-fluid';
                mainImg.style.maxHeight = '90%';
                mainImg.style.objectFit = 'contain';

                if (images.length) {
                    mainImg.src = '../admin/upload/' + images[0];
                } else {
                    mainImg.style.display = 'none';
                }
                gallery.appendChild(mainImg);

                images.forEach((im) => {
                    const t = document.createElement('img');
                    t.src = '../admin/upload/' + im;
                    t.className = 'rounded';
                    t.style.width = '62px';
                    t.style.height = '62px';
                    t.style.objectFit = 'cover';
                    t.style.cursor = 'pointer';
                    t.onclick = () => {
                        mainImg.src = '../admin/upload/' + im;
                        mainImg.style.display = 'block';
                    };
                    thumbs.appendChild(t);
                });

                // Open full page
                const openFull = document.getElementById('pdOpenFull');
                openFull.href = `product_detail.php?id=${p.product_id}&brand=${p.brand_id}`;

                // Show content
                document.getElementById('pdContent').style.display = 'block';
            })
            .catch(() => {
                document.getElementById('pdLoader').style.display = 'none';
                document.getElementById('pdError').textContent = 'Error loading product.';
                document.getElementById('pdError').style.display = 'block';
            });
    }
</script>
</body>

</html>