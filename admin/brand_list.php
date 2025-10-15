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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                            <h6 class="text-white text-capitalize ps-3">Brand List</h6>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2"> Name</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Created_at</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Updated_at</th>
                                        <th class="text-secondary opacity-7">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($res->num_rows > 0) {
                                        while ($row = $res->fetch_assoc()) { ?>
                                            <tr>
                                                <td>
                                                    <p class="text-xs text-secondary mb-0"><?= $row['brand_id'] ?></p>
                                                </td>
                                                <td>
                                                    <p class="text-xs text-secondary mb-0"><?= $row['brand_name'] ?></p>
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    <span class="text-secondary text-xs font-weight-bold"><?= $row['created_at'] ?></span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-secondary text-xs font-weight-bold"><?= $row['updated_at'] ?></span>
                                                </td>
                                                <td class="align-middle">
                                                    <a href="javascript:void(0)"
                                                        class="btn btn-sm btn-primary edit_btn"
                                                        data-id="<?= $row['brand_id'] ?>"
                                                        data-name="<?= htmlspecialchars($row['brand_name'], ENT_QUOTES) ?>">
                                                        Edit
                                                    </a>
                                                    <button data-id="<?= $row['brand_id'] ?>" class="btn btn-sm btn-danger delete_btn">Delete</button>
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
    <!-- #/ container -->

    <!-- Edit Brand Modal -->
    <div class="modal fade" id="brandEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="brandEditForm">
                    <div class="modal-body">
                        <input type="hidden" name="brand_id" id="brand_id">
                        <div class="mb-3">
                            <label for="brand_name" class="form-label">Brand Name</label>
                            <input type="text" class="form-control" id="brand_name" name="brand_name" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="save-text">Save changes</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
<script>
    $(document).ready(function() {
        // OPEN modal & prefill
        $(document).on('click', '.edit_btn', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            $('#brand_id').val(id);
            $('#brand_name').val(name);

            const modalEl = document.getElementById('brandEditModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        });

        // SUBMIT update
        $('#brandEditForm').on('submit', function(e) {
            e.preventDefault();

            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true);
            $btn.find('.save-text').addClass('d-none');
            $btn.find('.spinner-border').removeClass('d-none');

            $.ajax({
                url: 'brand_update.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    brand_id: $('#brand_id').val(),
                    brand_name: $('#brand_name').val()
                },
                success: function(resp) {
                    const modalEl = document.getElementById('brandEditModal');
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.hide();

                    if (resp && resp.success) {
                        // Update the row in-place
                        const id = $('#brand_id').val();
                        const newName = $('#brand_name').val();

                        // Find the row by brand_id (first column text)
                        const $row = $('table tbody tr').filter(function() {
                            return $(this).find('td p').first().text().trim() === id.toString();
                        });

                        // Update the name cell
                        $row.find('td').eq(1).find('p').text(newName);

                        // Update the Edit button's data-name
                        $row.find('.edit_btn').data('name', newName);

                        Swal.fire({
                            icon: 'success',
                            title: 'Updated',
                            text: 'Brand updated successfully.',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Update failed',
                            text: (resp && resp.message) ? resp.message : 'Unknown error',
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Server error',
                        text: xhr.responseText || 'Please try again.'
                    });
                },
                complete: function() {
                    const $btn = $('#brandEditForm').find('button[type="submit"]');
                    $btn.prop('disabled', false);
                    $btn.find('.save-text').removeClass('d-none');
                    $btn.find('.spinner-border').addClass('d-none');
                }
            });
        });
    });
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
