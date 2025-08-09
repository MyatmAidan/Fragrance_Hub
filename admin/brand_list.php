<?php
session_start();

require '../database/db.php';
require '../database/central_function.php';

$success = $_GET['success'] ? $_GET['success']  : '';

$row = select_data('brand', $conn, '*');

$delete_id = isset($_GET['delete_id']) ?  $_GET['delete_id'] : '';
if ($delete_id !== '') {
    $res = deleteData('member', $conn, "member_id=$delete_id");

    if ($res) {
        header("Location: ../admin/member_list.php?success=Successfully deleted");
        exit;
    }
}

include '../includes/header.php';
include '../includes/nav.php';

?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container mt-4 fade-in-up">

    <div class="card text-center">
        <div class="card-header">
            <h3><i class="fas fa-users me-2"></i>Brand List</h3>
        </div>
        <div class="card-body">
            <!-- Add table-responsive class -->
            <div class="table-responsive">
                <?php if ($success !== '') { ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?= $success ?>
                    </div>
                <?php } ?>
                <table class="table table-sm table-striped table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Brand Name</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($row->num_rows > 0) {
                            while ($show = $row->fetch_assoc()) { ?>
                                <tr>
                                    <td><?= $show['brand_id'] ?></td>
                                    <td><?= $show['brand_name'] ?></td>
                                    <td>
                                        <a href="<?= '../admin/brand_edit.php?id=' . $show['brand_id'] ?>" class="btn btn-sm btn-primary">
                                            <!-- <i class="fas fa-edit me-1"></i> -->Edit
                                        </a>
                                        <button data-id="<?= $show['brand_id'] ?>" class="btn btn-sm btn-danger delete_btn">
                                            <!-- <i class="fas fa-trash me-1"></i> -->Delete
                                        </button>
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

<?php
include '../includes/footer.php';
?>

<script>
    $(document).ready(function() {
        $('.delete_btn').click(function() {
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
                    window.location.href = 'member_list.php?delete_id=' + id
                }
            });
        })
    })
</script>