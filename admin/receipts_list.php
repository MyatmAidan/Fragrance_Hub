<?php
require '../database/dbrequire.php';
require '../plugins/common.php';
require '../database/central_function.php';

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // 5 orders per page
$offset = ($page - 1) * $limit;

// Count total orders (for total pages)
$countQuery = "SELECT COUNT(*) AS total FROM recepties";
$countRes = $conn->query($countQuery);
$totalOrders = $countRes->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $limit);

// Your query with LIMIT and OFFSET
$query = "
   SELECT 
    o.order_id,
    r.recepties_id, 
    r.date, 
    r.total, 
    u.user_name,
    COUNT(o.order_id) AS item_count,
    GROUP_CONCAT(DISTINCT CONCAT(p.product_name, ' (', b.brand_name, ')') SEPARATOR ', ') AS products
FROM `order` o
INNER JOIN `recepties` r ON o.recepties_id = r.recepties_id
INNER JOIN `user` u ON r.user_id = u.user_id
LEFT JOIN product_brand pb ON o.product_brand_id = pb.product_brand_id
LEFT JOIN product p ON pb.product_id = p.product_id
LEFT JOIN brand b ON pb.brand_id = b.brand_id
GROUP BY r.recepties_id, r.date, r.total, u.user_name
ORDER BY r.date DESC;

";

$res = $conn->query($query);

require './layout/header.php';
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(to right, #f7f7f7, #e6e6e6);
        margin: 0;
        padding: 20px;
        color: #222;
    }

    /* Receipt Container */
    .receipt {
        max-width: 900px;
        margin: auto;
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(8px);
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.08);
        border-radius: 16px;
        padding: 30px 40px;
        font-size: 15px;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    /* Header */
    .inv-header {
        text-align: center;
        font-size: 26px;
        font-weight: 700;
        padding-bottom: 20px;
        border-bottom: 2px solid #333;
        color: #222;
    }

    /* Section Titles */
    .inv-section-title {
        background: linear-gradient(90deg, #483C32 0%, #5a4d40 100%);
        color: #fff;
        padding: 10px 15px;
        margin: 30px 0 15px;
        font-size: 16px;
        font-weight: bold;
        border-radius: 6px;
    }

    /* Customer Info */
    .info {
        display: flex;
        flex-wrap: wrap;
        gap: 40px;
        justify-content: space-between;
    }

    .info .box {
        flex: 1 1 45%;
        background: #f9f9f9;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.05);
    }

    .info p {
        margin: 6px 0;
        color: #333;
        font-weight: 500;
    }

    .info strong {
        font-weight: 600;
        color: #000;
    }

    /* Table */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        font-size: 14px;
    }

    table thead {
        background: #e0c185;
        color: #222;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    table th,
    table td {
        padding: 10px 8px;
        text-align: center;
        border: 1px solid #ddd;
    }

    table tbody tr:nth-child(even) {
        background: #fafafa;
    }

    /* Totals */
    .totals {
        width: 300px;
        float: right;
        margin-top: 25px;
        font-size: 15px;
        background: #f5f5f5;
        padding: 15px 20px;
        border-radius: 8px;
        border: 1px solid #ddd;
    }

    .totals p {
        display: flex;
        justify-content: space-between;
        margin: 8px 0;
        font-weight: 500;
    }

    .totals p:last-child {
        font-size: 16px;
        font-weight: bold;
        border-top: 2px solid #ccc;
        padding-top: 10px;
    }

    /* Footer */
    .inv-footer {
        clear: both;
        text-align: center;
        margin-top: 40px;
        padding: 10px;
        font-size: 13px;
        color: #666;
        border-top: 1px solid #ccc;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .receipt {
            padding: 20px;
        }

        .info {
            flex-direction: column;
            gap: 20px;
        }

        .totals {
            float: none;
            width: 100%;
            margin-top: 30px;
        }

        table th,
        table td {
            font-size: 13px;
            padding: 8px;
        }
    }



    .status-selector {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
    }

    .status-option {
        flex: 1;
        padding: 15px 20px;
        background: rgba(255, 255, 255, 0.15);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        backdrop-filter: blur(8px);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .status-option.active {
        border-color: #007bff;
        background: #007bff;
        color: #fff;
    }

    .status-option i {
        display: block;
        font-size: 18px;
        margin-bottom: 4px;
        color: #000;
    }

    .status-option span {
        font-weight: 600;
        color: #000;
        font-size: 14px;
    }

    .status-option.active i {
        color: #0d6efd;
    }

    .status-option.active span {
        color: #0d6efd;
    }

    .status-option:hover {
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .status-option.active {
        background: rgba(13, 110, 253, 0.2);
        border-color: rgba(13, 110, 253, 0.5);
        box-shadow: 0 0 15px rgba(13, 110, 253, 0.3);
    }


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

    .pagination {
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(12px);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.25);
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.5);
        padding: 12px 20px;
    }

    .page-link {
        background-color: rgba(255, 255, 255, 0.15);
        color: #0e0e0e;
        border: none;
        padding: 6px 14px;
        margin: 0 6px;
        border-radius: 10px;
        font-weight: 600;
        box-shadow: 0 0 6px rgba(255, 255, 255, 0.25);
        transition: 0.3s ease-in-out;
    }

    .page-item {
        background-color: transparent;
        color: #666;
        border: none;
        padding: 6px 8px;
        margin: 0 2px;
        font-weight: 600;
    }
</style>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="content-body">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <h1>Order List</h1>
            <div class="">
                <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 offset-md-8 col-sm-6 offset-sm-6">
                <?php if ($success !== '') { ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php } ?>
                <?php if ($error !== '') { ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php } ?>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="glass-panel card-body">
                        <table class="table table-hover table-sm table-bordered align-middle mb-0 glass-table">
                            <thead>
                                <tr>
                                    <th class="col-1">No.</th>
                                    <th class="col-2">Customer Name</th>
                                    <th class="col-2">Product Name</th>
                                    <th class="col-2">Date</th>
                                    <th class="col-1">Total</th>
                                    <th class="col-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($res->num_rows > 0) {
                                    while ($row = $res->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?= $row['order_id'] ?></td>
                                            <td><?= $row['user_name'] ?></td>
                                            <td><?= $row['products'] ?></td>
                                            <td><?= $row['date'] ?></td>
                                            <td><?= $row['total'] ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-warning receipt-btn"
                                                    data-val="<?= $row['recepties_id'] ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#exampleModalCenter">
                                                    <i class="fa-regular fa-file-lines"></i>
                                                </button>
                                            </td>
                                        </tr>
                                <?php }
                                } ?>
                            </tbody>
                        </table>
                        <!-- Pagination -->
                        <div class="pagination mt-3">
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a></li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Next</a></li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Modal -->
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">INVOICE</h5>
                    <button type="button" class="btn btn-sm" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body p-2">
                    <div class="receipt" id="invoice">
                        <div class="inv-header">Fragrance Hub Receipt</div>

                        <div class="inv-section-title">Customer Information</div>
                        <div class="info">
                            <div class="box">
                                <p>Receipt #: <strong id="inv_receipt">00</strong></p>
                                <p>Date: <strong id="inv_date"></strong></p>
                            </div>
                            <div class="box">
                                <p>Name: <strong id="inv_name"></strong> </p>
                                <p>Email:<strong id="inv_email"></strong></p>
                            </div>
                        </div>

                        <div class="inv-section-title">Invoice Details</div>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody id="invoice-items">
                                <!-- js will fill  -->
                            </tbody>
                        </table>
                        <div class="totals" id="invoice-summary">
                            <!-- js will fill -->
                        </div>

                        <div class="inv-footer mt-5">
                            No-137, San Yeik Nyein-5 street, Kamayut Township | (+95) 9400000170 | info@fragrancehub.com
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="printInvoice()">Print</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function printInvoice() {
        var content = document.querySelector('.receipt').innerHTML;
        var printWindow = window.open('', '', 'height=600,width=800');

        printWindow.document.write(`
        <html>
            <head>
                <title>Print Invoice</title>
                <style>
                    @media print {
                        @page {
                            size: A5;
                            margin: 10mm;
                        }
                        body {
    font-family: 'Poppins', 'Segoe UI', sans-serif;
    background: #f8f9fa;
    color: #212529;
    font-size: 13px;
    margin: 0;
    padding: 0;
}

.receipt {
    max-width: 480px;
    margin: 10px auto;
    background: #ffffff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 0 12px rgba(0, 0, 0, 0.08);
    font-size: 13px;
    border: 1px solid #ddd;
}

.inv-header {
    background: #212529;
    color: #fff;
    padding: 12px;
    text-align: center;
    font-size: 18px;
    font-weight: bold;
    border-radius: 8px;
    margin-bottom: 15px;
}

.inv-section-title {
    background: #4b4b4b;
    color: #fff;
    padding: 6px 10px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 5px;
    margin: 15px 0 10px 0;
}

.info {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 10px;
}

.info .box {
    flex: 1 1 100%;
    background: #f1f1f1;
    padding: 6px 10px;
    border-radius: 6px;
}

.info p {
    margin: 3px 0;
    font-size: 11px;
    color: #333;
}

.info strong {
    font-weight: 600;
    color: #000;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
    font-size: 12.5px;
}

table thead {
    background: #e0c185;
    color: #000;
    font-weight: bold;
}

table th,
table td {
    border: 1px solid #ccc;
    padding: 6px 5px;
    text-align: center;
}

table tbody tr:nth-child(even) {
    background: #f9f9f9;
}

.totals {
    margin-top: 12px;
    width: 100%;
    background: #f8f8f8;
    padding: 10px;
    border-radius: 6px;
    font-size: 13px;
}

.totals p {
    display: flex;
    justify-content: space-between;
    margin: 5px 0;
    font-weight: 500;
}

.totals p:last-child {
    border-top: 1px solid #ccc;
    padding-top: 6px;
    font-weight: 700;
    font-size: 13.5px;
}

.inv-footer {
    text-align: center;
    font-size: 11px;
    color: #666;
    margin-top: 20px;
    padding-top: 10px;
    border-top: 1px solid #ccc;
}

@media print {
    body {
        background: white;
    }

    .receipt {
        box-shadow: none;
        border: none;
        margin: 0;
        padding: 10px;
    }

    .inv-header {
        font-size: 16px;
    }

    .inv-footer {
        font-size: 10px;
    }
}


                    }           
                </style>
            </head>
            <body>${invoice.outerHTML}</body>
        </html>
    `);

        printWindow.document.close();
        printWindow.focus();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 300);

    }



    $(document).ready(function() {
        $('.receipt-btn').click(function() {
            const recepties_id = $(this).data('val');
            const invoice_summary = $('#invoice-summary');
            // Fetch invoice data using AJAX
            $.ajax({
                url: 'get_receipts.php',
                type: 'GET',
                // dataType: 'json',
                data: {
                    recepties_id: recepties_id
                },
                success: function(response) {
                    console.log(response.order_info);

                    let responseData = response.order;
                    let response_recepties = response.recepties;

                    console.log(responseData);



                    // format created_at to "Month Day, Year"
                    const createdAt = new Date(response_recepties.created_at);
                    const options = {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    };
                    const formattedDate = createdAt.toLocaleDateString('en-US', options);

                    // format order id like 005
                    const receiptNo = String(response_recepties.recepties_id).padStart(3, '0');

                    $('#inv_receipt').text(receiptNo);
                    $('#inv_name').text(response_recepties.user_name);
                    $('#inv_email').text(response_recepties.email);
                    $('#inv_date').text(formattedDate);
                    $('#invoice-items').html('');
                    $('#invoice-summary').html('');

                    $('#invoice-items').html('');
                    responseData.forEach((item, index) => {
                        $('#invoice-items').append(`
                                <tr>
                                    <td>${index+1}</td>
                                    <td>${item.product_name}</td>
                                    <td>${item.qty}</td>
                                    <td>${item.price}</td>
                                    <td>${item.qty * item.price}</td>
                                </tr>
                            `);
                    });
                    $('#invoice-items').append(`
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    `);

                    $('#invoice-summary').append(`
                        <p><span>Discount :</span> <span>$0.00</span></p>
                        <p><span>Subtotal :</span> <span>$${response_recepties.total}</span></p>
                        <p><span>Tax (5%):</span> <span>$${(response_recepties.total * 0.05).toFixed(2)}</span></p>
                        <p><span>Grand Total :</span> <span>$${(response_recepties.total * 1.10).toFixed(2)}</span></p>
                        <p><span>Paid By :</span> <span> Cash</span></p>
                    `);
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php require './layouts/footer.php'; ?>