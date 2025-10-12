<?php
require '../database/dbrequire.php';
require '../database/central_function.php';
require_once '../plugins/common.php';

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

$res = select_data('brand', $conn, "*", "", "");

$delete_id = isset($_GET['delete_id']) ?  $_GET['delete_id'] : '';
if ($delete_id !== '') {
    $res = deleteData('brand', $conn, "brand_id=$delete_id");
    if ($res) {
        $url = $admin_base_url . "brand_list.php?success=Deleted Brand Success";
        header("Location: $url");
    }
}
require './layout/header.php';
?>

<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.18);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-radius: 18px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .glass-table th,
    .glass-table td {
        background: rgba(255, 255, 255, 0.10) !important;
        border: 1px solid rgba(255, 255, 255, 0.18) !important;
    }
</style>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="content-body">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <h1>Brand Lists</h1>
            <div class="">
                <a href="<?= $admin_base_url . 'brand_create.php' ?>" class="btn btn-primary">
                    Create Brand
                </a>
            </div>
        </div>

        <div class="row">
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
            <div class="col-12">

                <div class="card">
                    <div class="glass-panel card-body">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th class="col-2">No.</th>
                                    <th class="col-4">Name</th>
                                    <th class="col-2">Updated At</th>
                                    <th class="col-2">Created At</th>
                                    <th class="col-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($res->num_rows > 0) {
                                    while ($row = $res->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?= $row['brand_id'] ?></td>
                                            <td><?= $row['brand_name'] ?></td>
                                            <td><?= date("Y-m-d g:i:s A", strtotime($row['updated_at'])) ?></td>
                                            <td><?= date("Y-m-d g:i:s A", strtotime($row['created_at'])) ?></td>
                                            <td>
                                                <a href="<?= $admin_base_url . 'brand_edit.php?id=' . $row['brand_id'] ?>" class="btn btn-sm btn-primary"><i class="fa-solid fa-pen-to-square"></i></a>
                                                <button data-id="<?= $row['brand_id'] ?>" class="btn btn-sm btn-danger delete_btn"><i class="fa-solid fa-trash"></i></button>
                                            </td>
                                        </tr>
                                <?php }
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- #/ container -->
</div>
<!--**********************************
            Content body end
        ***********************************-->
<script>
    $(document).ready(function() {
        $('.delete_btn').click(function() {
            console.log('click');
            const id = $(this).data('id')
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
                    window.location.href = 'brand_list.php?delete_id=' + id
                }
            });
        })

    })
</script>
<?php if ($success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: ' Successfully',
            text: '<?= $success ?>',
            confirmButtonColor: '#3085d6'
        });
    </script>
<?php endif; ?>
<?php
require './layouts/footer.php';
