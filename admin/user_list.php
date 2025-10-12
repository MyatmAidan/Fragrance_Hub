<?php
require '../database/dbrequire.php';
require '../plugins/common.php';
require '../database/central_function.php';


$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
$delete_id = isset($_GET['delete_id']) ? $_GET['delete_id'] : '';

if ($delete_id !== '') {
    $res = deleteData('user', $conn, "user_id=$delete_id");
    if ($res) {
        header("Location: {$admin_base_url}user_list.php?success=Product deleted successfully.");
        exit;
    } else {
        header("Location: {$admin_base_url}user_list.php?error=Delete failed!");
        exit;
    }
}
// var_dump('heluuuulo');
// die;

$res = select_data('user', $conn, '*', '', '');

require './layout/header.php';
?>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="content-body">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <h1>User List</h1>
            <div class="">
                <!-- <a href="<?= $admin_base_url . 'product_create.php' ?>" class="btn btn-primary">
                    Create New Product
                </a> -->
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
                            <h6 class="text-white text-capitalize ps-3">User List</h6>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Email</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Role</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                        <th class="text-secondary opacity-7">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($res->num_rows > 0) {
                                        while ($row = $res->fetch_assoc()) { ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div><?php
                                                                if ($row['img'] == null) {
                                                                    echo '<i class="material-symbols-rounded text-primary me-3">account_circle</i>';
                                                                } else {
                                                                    echo '<img src="./upload/' . $row['img'] . '" class="avatar avatar-sm me-3 border-radius-lg" alt="user1">';
                                                                }
                                                                ?>
                                                        </div>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm"><?= $row['user_name'] ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs text-secondary mb-0"><?= $row['email'] ?></p>
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    <span class="badge badge-sm text-secondary"><?php if ($row['role'] == 1) {
                                                                                                    echo 'Admin';
                                                                                                } else {
                                                                                                    echo 'User';
                                                                                                }
                                                                                                ?></span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-secondary text-xs font-weight-bold"><?= $row['created_at'] ?></span>
                                                </td>
                                                <td class="align-middle">
                                                    <a href="<?= $admin_base_url . 'user_edit.php?id=' . $row['user_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                                    <button data-id="<?= $row['user_id'] ?>" class="btn btn-sm btn-danger delete_btn">Delete</button>
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

<script>
    $(function() {
        $('.delete_btn').click(function() {
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
                    window.location.href = 'product_detail_list.php?delete_id=' + id
                }
            });
        });
    });
</script>

<?php require './layout/footer.php'; ?>