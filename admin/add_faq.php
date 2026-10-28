<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['user_id']) || (int)$_SESSION['role'] !== 1) {
    header('Location: ../login.php');
    exit();
}
include_once 'data_access/fetch_files.php';
/** @var array $fetchFiles */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <title>Add FAQ</title>

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
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
                                <!-- <p class="eyebrow mb-1">Overview</p> -->
                                <h1 class="h3 mb-1">Add FAQ</h1>
                                <!-- <p class="text-muted mb-0">Reusable form controls, validation states, and field layouts.</p> -->
                            </div>
                        </div>

                    </div>

                    <section class="row g-3 justify-content-center">
                        <div class="col-12">
                            <form class="panel needs-validation form-add" id="addFaqForm" method="post" novalidate>
                                <div class="panel-header">
                                    <div>
                                        <p class="text-muted mb-0">
                                            <span class="text-danger">*</span>
                                            Required fields
                                        </p>
                                    </div>
                                </div>
                                <div class="row g-3">

                                    <div class="col-md-12">
                                        <label for="chooseFile" class="form-label">File</label>
                                        <span class="text-danger">*</span>
                                        <select name="file_id" class="form-select" id="chooseFile" required>
                                            <option selected disabled value="">--Choose a file--</option>
                                            <?php foreach ($fetchFiles as $files) { ?>
                                                <option value="<?php echo (int)$files['id']; ?>">
                                                    <?php echo htmlspecialchars($files['file_number'] . " - " . $files['subject'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <div class="invalid-feedback">File is required.</div>
                                    </div>


                                    <div class="col-md-12">
                                        <label class="form-label" for="formQuestion">Question</label>
                                        <span class="text-danger">*</span>
                                        <input class="form-control allow-alphanumeric" name="question" maxlength="300" id="formQuestion" type="text" required>
                                        <div class="invalid-feedback">Question is required.</div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label" for="formAnswer">Answer</label>
                                        <span class="text-danger">*</span>
                                        <textarea class="form-control allow-alphanumeric" name="answer" maxlength="1000" id="formAnswer" rows="4" required></textarea>
                                        <div class="invalid-feedback">Answer is required.</div>
                                    </div>

                                </div>


                                <!-- Documents based on file select -->
                                <div class="col-12 d-none" id="docsSection">
                                    <label class="form-label">Documents of this file</label>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="docsTable">
                                            <thead>
                                                <tr>
                                                    <th style="width:50px">
                                                        <input type="checkbox" class="form-check-input" id="checkAll" title="Select all">
                                                    </th>
                                                    <th style="width:70px">S.No.</th>
                                                    <th>Document Type</th>
                                                    <th>Document Name</th>
                                                    <th>Document Date</th>
                                                    <!-- <th>Remarks</th> -->
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <p class="fw-bold mt-2 mb-0" id="selectedCount"></p>
                                </div>

                                <div class="d-flex justify-content-center mt-4">
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                </div>
                            </form>
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
    <script src="../assets/js/allowed_characters.js"></script>
    <script src="../assets/js/add_faq.js?v=2"></script>

</body>

</html>