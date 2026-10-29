<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['user_id']) || (int)$_SESSION['role'] !== 1) {
    header('Location: ../login.php');
    exit();
}
include_once 'data_access/fetch_faqs.php';
/** @var array $fetchFaqs */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <title>FAQ Report</title>

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/vendors/datatables/datatables.min.css">
</head>

<body>
    <div class="admin-shell">
        <div class="sidebar-backdrop" data-sidebar-close></div>

        <?php include '../include/sidebar.php'; ?>

        <div class="admin-main">
            <?php include '../include/navbar.php'; ?>

            <main class="dashboard-content">
                <div class="container-fluid px-3 px-lg-4 py-4">
                    <div class="page-heading">
                        <div class="page-heading-copy">
                            <span class="page-icon">
                                <i class="bi bi-question-circle"></i>
                            </span>
                            <div>
                                <h1 class="h3 mb-1">FAQ Report</h1>
                            </div>
                        </div>
                    </div>

                    <section class="row g-3 justify-content-center">
                        <div class="col-12">
                            <div class="panel">
                                <div class="table-responsive">
                                    <table id="faqTable" class="table table-striped table-bordered" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>S.No.</th>
                                                <th>File</th>
                                                <th>Question</th>
                                                <th>Answer</th>
                                                <th>Documents</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($fetchFaqs as $i => $row) { ?>
                                                <tr>
                                                    <td><?php echo $i + 1; ?></td>
                                                    <td>
                                                        <?php
                                                        echo htmlspecialchars(
                                                            ($row['file_number'] ?? '') . ' - ' . ($row['file_subject'] ?? ''),
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        );
                                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['question'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($row['answer'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo (int)$row['total_docs']; ?></td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <form method="post" action="add_faq.php" class="d-inline">
                                                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                                                <button type="submit" title="Edit" class="btn btn-sm btn-outline-primary">
                                                                    <i class="bi bi-pencil-square"></i> Edit
                                                                </button>
                                                            </form>
                                                            <button type="button" title="Delete" class="btn btn-sm btn-outline-danger btn-delete"
                                                                data-id="<?php echo (int)$row['id']; ?>">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </main>

            <?php include '../include/footer.php'; ?>
        </div>
    </div>

    <script src="../assets/js/sweetalert2.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/vendors/jQuery/jquery.min.js"></script>
    <script src="../assets/vendors/datatables/jszip.min.js"></script>
    <script src="../assets/vendors/datatables/pdfmake.min.js"></script>
    <script src="../assets/vendors/datatables/vfs_fonts.js"></script>
    <script src="../assets/vendors/datatables/datatables.min.js"></script>
    <script>
        DataTable.Buttons.defaults.dom.button.className = 'btn btn-outline-primary btn-sm';

        // Export settings: leave out the Action column and number the rows 1, 2, 3...
        const exportOpts = {
            columns: [0, 1, 2, 3, 4],
            format: {
                body: function(data, row, column) {
                    return column === 0 ? row + 1 : data;
                }
            }
        };

        const table = new DataTable('#faqTable', {
            paging: true,
            searching: true,
            stateSave: true,
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            columnDefs: [{
                    targets: 0,
                    orderable: false,
                    searchable: false
                }, // S.No.
                {
                    targets: -1,
                    orderable: false,
                    searchable: false
                } // Action
            ],
            // Numbers all rows 1, 2, 3... across all pages
            drawCallback: function() {
                this.api().column(0, {
                        search: 'applied',
                        order: 'applied'
                    })
                    .nodes().each(function(cell, i) {
                        cell.innerHTML = i + 1;
                    });
            },
            layout: {
                topStart: {
                    buttons: [{
                            extend: 'copyHtml5',
                            exportOptions: exportOpts
                        },
                        {
                            extend: 'csvHtml5',
                            exportOptions: exportOpts
                        },
                        {
                            extend: 'excelHtml5',
                            exportOptions: exportOpts
                        },
                        {
                            extend: 'pdfHtml5',
                            orientation: 'landscape',
                            pageSize: 'A4',
                            title: 'FAQ_Report',
                            exportOptions: exportOpts,
                            customize: function(doc) {
                                doc.styles.tableHeader.fillColor = '#1e3a5f';
                                doc.styles.tableHeader.color = '#e2e8f0';
                                doc.styles.tableHeader.alignment = 'center';

                                doc.content[1].table.body.forEach(function(row) {
                                    row.forEach(function(cell) {
                                        cell.alignment = 'center';
                                    });
                                });

                                doc.content[1].layout = {
                                    hLineWidth: function() {
                                        return 0.5;
                                    },
                                    vLineWidth: function() {
                                        return 0.5;
                                    },
                                    hLineColor: function() {
                                        return '#adb5bd';
                                    },
                                    vLineColor: function() {
                                        return '#adb5bd';
                                    }
                                };

                                doc.content[1].table.widths = Array(
                                    doc.content[1].table.body[0].length
                                ).fill('*');

                                doc.content[0].alignment = 'center';
                            }
                        }
                    ]
                },
                topEnd: 'search',
                bottomStart: 'info',
                bottomEnd: 'paging'
            }
        });
    </script>
    <script src="../assets/js/faq_report_delete.js?v=1"></script>
</body>

</html>