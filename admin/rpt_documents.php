<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['user_id']) || (int)$_SESSION['role'] !== 1) {
    header('Location: ../login.php');
    exit();
}

include_once 'data_access/fetch_documents.php';
/** @var array $fetchDocuments */
/** @var array $docTypes */
/** @var array $files */
/** @var int $fType */
/** @var int $fFile */
/** @var string $fName */
/** @var string $fRemarks */
/** @var string $fFrom */
/** @var string $fTo */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <title>Document Report</title>

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/vendors/datatables/datatables.min.css">
    <link rel="stylesheet" href="../assets/vendors/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="../assets/vendors/flatpickr/flatpickr.darkTheme.css">
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
                                <i class="bi bi-clipboard2-check"></i>
                            </span>
                            <div>
                                <!-- <p class="eyebrow mb-1">Overview</p> -->
                                <h1 class="h3 mb-1">Document Report</h1>
                                <!-- <p class="text-muted mb-0">Reusable form controls, validation states, and field layouts.</p> -->
                            </div>
                        </div>

                    </div>

                    <section class="row g-3 justify-content-center">
                        <div class="col-12">
                            <!-- In this place -->
                            <div class="panel">
                                <!-- Filter Form -->
                                <div class="filterForm p-4">
                                    <!-- <h4 class="pb-4">Filter</h4> -->
                                    <form method="get" id="filterForm" class="row g-3 mb-4">
                                        <div class="col-12 col-md-6 col-lg-3">
                                            <label class="form-label" for="fType">Document Type</label>
                                            <select class="form-select" name="doc_type_id" id="fType">
                                                <option value="" selected>All</option>
                                                <?php foreach ($docTypes as $t) { ?>
                                                    <option value="<?= (int)$t['id'] ?>" <?= $fType === (int)$t['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-3">
                                            <label class="form-label" for="fFile">File Name</label>
                                            <select class="form-select" name="file_id" id="fFile">
                                                <option value="" selected>All</option>
                                                <?php foreach ($files as $f) { ?>
                                                    <option value="<?= (int)$f['id'] ?>" <?= $fFile === (int)$f['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($f['file_number'] . ' - ' . $f['subject'], ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-3">
                                            <label class="form-label" for="fName">Document Name</label>
                                            <input type="text" class="form-control" name="rule_name" id="fName"
                                                value="<?= htmlspecialchars($fName, ENT_QUOTES, 'UTF-8') ?>">
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-3">
                                            <label class="form-label" for="fRemarks">Remarks</label>
                                            <input type="text" class="form-control" name="remarks" id="fRemarks"
                                                value="<?= htmlspecialchars($fRemarks, ENT_QUOTES, 'UTF-8') ?>">
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-3">
                                            <label class="form-label" for="fFrom">From Date</label>
                                            <input type="text" class="form-control date-filter" name="from_date" id="fFrom"
                                                value="<?= htmlspecialchars($fFrom, ENT_QUOTES, 'UTF-8') ?>">
                                        </div>

                                        <div class="col-12 col-md-6 col-lg-3">
                                            <label class="form-label" for="fTo">To Date</label>
                                            <input type="text" class="form-control date-filter" name="to_date" id="fTo"
                                                value="<?= htmlspecialchars($fTo, ENT_QUOTES, 'UTF-8') ?>">
                                        </div>

                                        <div class="col-12 col-lg-6 d-flex align-items-end gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-funnel"></i> Search
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" id="btnReset">
                                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <!-- Result Table  -->
                                <div class="table-responsive">
                                    <table id="docTable" class="table table-striped table-bordered" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>S.No.</th>
                                                <th>File</th>
                                                <th>Document Type</th>
                                                <th>Document Name</th>
                                                <th>Document Date</th>
                                                <th>Remarks</th>
                                                <th>View Document</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($fetchDocuments as $i => $row) { ?>
                                                <tr>
                                                    <!-- Serial Number -->
                                                    <td>
                                                        <?php echo $i + 1; ?>
                                                    </td>

                                                    <!-- File Number -->
                                                    <td>
                                                        <?php
                                                        echo htmlspecialchars(
                                                            ($row['file_number'] ?? '') . ' - ' . ($row['file_subject'] ?? ''),
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        );
                                                        ?>
                                                    </td>

                                                    <!-- Document Type -->
                                                    <td>
                                                        <?php
                                                        echo htmlspecialchars(
                                                            $row['doc_type_name'] ?? '',
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        );
                                                        ?>
                                                    </td>

                                                    <!-- Document Name -->
                                                    <td>
                                                        <?php
                                                        echo htmlspecialchars(
                                                            $row['doc_name'] ?? '',
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        );
                                                        ?>
                                                    </td>

                                                    <!-- Document Date -->
                                                    <td data-order="<?php echo htmlspecialchars($row['doc_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                        <?php
                                                        echo !empty($row['doc_date'])
                                                            ? date('d-m-Y', strtotime($row['doc_date']))
                                                            : '';
                                                        ?>
                                                    </td>

                                                    <!-- Remarks -->
                                                    <td>
                                                        <?php
                                                        echo htmlspecialchars(
                                                            $row['remarks'] ?? '',
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        );
                                                        ?>
                                                    </td>

                                                    <!-- View Document -->
                                                    <td>
                                                        <?php if (!empty($row['doc_pdf'])) { ?>
                                                            <a
                                                                href="serve_document.php?id=<?php echo (int)$row['id']; ?>"
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                class="btn btn-sm btn-primary">
                                                                <i class="bi bi-file-earmark-pdf"></i>
                                                                View PDF
                                                            </a>
                                                        <?php } else { ?>
                                                            <span class="text-muted">No document available</span>
                                                        <?php } ?>
                                                    </td>

                                                    <!-- Action Buttons -->
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <form method="post" action="add_docs.php" class="d-inline">
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
                        <!-- <div class="col-12 col-xl-5">
                            <div class="panel h-100">
                                <h2 class="h5 mb-3 section-title"><i class="bi bi-input-cursor-text" aria-hidden="true"></i><span>Input States</span></h2><input class="form-control mb-3" value="Default input"><input class="form-control is-valid mb-3" value="Valid input"><input class="form-control is-invalid mb-3" value="Invalid input">
                                <div class="form-check"><input class="form-check-input" type="checkbox" id="sampleCheck" checked><label class="form-check-label" for="sampleCheck">Sample checkbox</label></div>
                            </div>
                        </div> -->
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
    <script src="../assets/vendors/flatpickr/flatpickr.min.js"></script>
    <script>
        DataTable.Buttons.defaults.dom.button.className = 'btn btn-outline-primary btn-sm';
        // Export settings: leave out the Action column and number the rows 1, 2, 3...
        const exportOpts = {
            columns: [0, 1, 2, 3, 4, 5],
            format: {
                body: function(data, row, column) {
                    return column === 0 ? row + 1 : data;
                }
            }
        };
        const options = {
            paging: true,
            searching: true,
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
            // Runs after every draw: numbers all rows 1, 2, 3... across all pages
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
                            title: 'Document_Report',
                            exportOptions: exportOpts,
                            customize: function(doc) {
                                // Header colour and center alignment
                                doc.styles.tableHeader.fillColor = '#1e3a5f';
                                doc.styles.tableHeader.color = '#e2e8f0';
                                doc.styles.tableHeader.alignment = 'center';

                                // center align all body cells
                                doc.content[1].table.body.forEach(function(row) {
                                    row.forEach(function(cell) {
                                        cell.alignment = 'center';
                                    });
                                });

                                // Add borders
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

                                // Make columns equal width
                                doc.content[1].table.widths = Array(
                                    doc.content[1].table.body[0].length
                                ).fill('*');

                                // center align the title
                                doc.content[0].alignment = 'center';
                            }
                        }
                    ]
                },
                // topStart: 'pageLength',
                topEnd: 'search',
                bottomStart: 'info',
                bottomEnd: 'paging'
            }
        };
        let table = new DataTable('#docTable', options);
    </script>
    <!-- Filter Form -->
    <script src="../assets/js/rpt_documents_Filter_Form.js"></script>
    <!-- Document Report Delete -->
    <script src="../assets/js/document_report_delete.js?v=1"></script>
</body>

</html>