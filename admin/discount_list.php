<?php
session_start();

require '../database/db.php';
require '../database/central_function.php';

$success = isset($_GET['success']) ? $_GET['success'] : '';

$row = select_data('discount_details', $conn, '*');

$delete_id = isset($_GET['delete_id']) ? $_GET['delete_id'] : '';
if ($delete_id !== '') {
    $res = deleteData('brand', $conn, "brand_id=$delete_id");

    if ($res) {
        header("Location: discount_list.php?success=Brand deleted successfully");
        exit;
    } else {
        header("Location: discount_list.php?error=Failed to delete brand");
        exit;
    }
}
include '../admin/layouts/header.php';
?>


<!-- Page content -->
<div class="dashboard-wrapper">
    <!-- Page Header -->
    <div class="welcome-section">
        <div class="welcome-content">
            <h1 class="welcome-title">Discount Information 🏷️</h1>
            <p class="welcome-subtitle">Manage Discount detail and their information</p>
        </div>
        <div class="welcome-actions">
            <a href="discount_create.php" class="btn btn-primary btn-modern">
                <i class="bi bi-plus-circle me-2"></i>Create New Discount
            </a>
            <a href="index.php" class="btn btn-outline-light btn-modern">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($success !== ''): ?>
        <div class="alert alert-success alert-modern alert-dismissible fade show" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <i class="bi bi-check-circle me-2"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-modern alert-dismissible fade show" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-card-info">
            <div class="stat-icon">
                <i class="bi bi-award"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number"><?= $row->num_rows ?></h3>
                <p class="stat-label">Total Discount Lists</p>
            </div>
            <div class="stat-trend">
                <span class="trend-up">Active</span>
            </div>
        </div>
    </div>

    <!-- product Table Card -->
    <div class="dashboard-card">
        <div class="card-header-modern">
            <div class="card-title">
                <i class="bi bi-list-ul me-2"></i>
                <span>Discount List Details</span>
            </div>
            <div class="">
                <!-- d-flex gap-2 -->
                <button class="btn btn-sm btn-outline-primary" onclick="exportBrands()">
                    <i class="bi bi-download me-1"></i>Export
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="printBrands()">
                    <i class="bi bi-printer me-1"></i>Print
                </button>
            </div>
        </div>
        <div class="card-body-modern">
            <?php if ($row->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-center" style="width: 80px;">
                                    <i class="bi bi-hash me-1"></i>ID
                                </th>
                                <th scope="col">
                                    Discount Name
                                </th>
                                <th scope="col">
                                    Percentage
                                </th>
                                <th scope="col">
                                    Start Date
                                </th>
                                <th scope="col">
                                    End Date
                                </th>
                                <th scope="col" class="text-center" style="width: 200px;">
                                    <i class="bi bi-gear me-1"></i>Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($show = $row->fetch_assoc()):
                                // Fetch discount name 
                                $discount_sql = "SELECT * FROM discount WHERE discount_id = " . $show['discount_id'];
                                $discount_sql = $conn->query($discount_sql);
                                $discount_row = $discount_sql->fetch_assoc();
                                // var_dump($discount_row);
                                // die();
                                $discount_name = $discount_row['name_of_package'];
                            ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-primary rounded-pill">
                                            <?= $show['discount_details_id'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="">
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($discount_name) ?></h6>
                                                <small class="text-muted">Discount ID: <?= $show['discount_id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="">
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($show['percentage']) ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="">
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($show['start_date']) ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="">
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($show['end_date']) ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href=""
                                                class="btn btn-sm btn-outline-primary"
                                                title="Edit Brand">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button data-id="<?= $show['discount_id'] ?>"
                                                class="btn btn-sm btn-outline-danger delete_btn"
                                                title="Delete Brand">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <a href="#"
                                                class="btn btn-sm btn-outline-info"
                                                title="View Products">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state text-center py-5">
                    <i class="bi bi-award display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">No Discount Found</h4>
                    <p class="text-muted">Get started by creating your first discount.</p>
                    <a href="brand_create.php" class="btn btn-primary btn-modern">
                        <i class="bi bi-plus-circle me-2"></i>Create First Discount
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <div class="dashboard-card">
            <div class="card-header-modern">
                <div class="card-title">
                    <i class="bi bi-lightning me-2"></i>
                    <span>Quick Actions</span>
                </div>
            </div>
            <div class="card-body-modern">
                <div class="actions-grid">
                    <a href="brand_create.php" class="action-card action-primary">
                        <div class="action-icon">
                            <i class="bi bi-plus-circle"></i>
                        </div>
                        <span>Add Brand</span>
                    </a>
                    <a href="products.php" class="action-card action-success">
                        <div class="action-icon">
                            <i class="bi bi-box"></i>
                        </div>
                        <span>Manage Products</span>
                    </a>
                    <a href="category.php" class="action-card action-info">
                        <div class="action-icon">
                            <i class="bi bi-tags"></i>
                        </div>
                        <span>Categories</span>
                    </a>
                    <a href="index.php" class="action-card action-warning">
                        <div class="action-icon">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <span>Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<!-- footer -->
<?php include '../admin/layouts/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        // Delete confirmation with SweetAlert2
        $('.delete_btn').click(function() {
            const id = $(this).data('id');
            const brandName = $(this).closest('tr').find('h6').text();

            Swal.fire({
                title: 'Delete Brand?',
                html: `Are you sure you want to delete <strong>${brandName}</strong>?<br><br>
                           <small class="text-muted">This action cannot be undone.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash me-1"></i>Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'brand_list.php?delete_id=' + id;
                }
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });

    // Export brands function
    function exportBrands() {
        Swal.fire({
            title: 'Export Brands',
            text: 'This feature will be available soon!',
            icon: 'info',
            confirmButtonText: 'OK'
        });
    }

    // Print brands function
    function printBrands() {
        window.print();
    }
</script>
</body>

</html>