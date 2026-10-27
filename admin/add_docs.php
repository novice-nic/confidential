<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['user_id']) || (int)$_SESSION['role'] !== 1) {
    header('Location: ../login.php');
    exit();
}
require_once __DIR__ . '/../include/conn.php';
include_once 'data_access/fetch_files.php';
include_once 'data_access/fetch_docTypes.php';
/** @var array $fetchFiles */
/** @var array $fetchDocTypes */

// Edit mode: the Edit button in the report sends the document id with POST
$editId = (int)($_POST['id'] ?? 0);
$record = null;

if ($editId > 0) {
    $stmt = $conn->prepare(
        "SELECT id, file_id, doc_type_id, doc_name, doc_date, remarks, doc_pdf
         FROM public.file_document
         WHERE id = :id AND is_deleted = FALSE"
    );
    $stmt->execute([':id' => $editId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    // Wrong or deleted id: go back to the report
    if (!$record) {
        header('Location: rpt_documents.php');
        exit();
    }
}

$isEdit = $record !== null;

// Safe text for the form fields
function val(?array $r, string $k): string
{
    return htmlspecialchars((string)($r[$k] ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <title>Add Documents</title>

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/vendors/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="../assets/vendors/flatpickr/flatpickr.darkTheme.css" id="fpDark" media="not all">
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
                                <i class="bi bi-plus"></i>
                            </span>
                            <div>
                                <!-- <p class="eyebrow mb-1">Inputs</p> -->
                                <h1 class="h3 mb-1"><?= $isEdit ? 'Edit Document' : 'Add Documents' ?></h1>
                                <!-- <p class="text-muted mb-0">Reusable form controls, validation states, and field layouts.</p> -->
                            </div>
                        </div>
                    </div>

                    <section class="row g-3 justify-content-center">
                        <div class="col-12">
                            <form class="panel needs-validation form-add" id="addRulesForm" method="post" enctype="multipart/form-data" novalidate>
                                <input type="hidden" name="id" value="<?= $isEdit ? (int)$record['id'] : 0 ?>">
                                <div class="panel-header">
                                    <div>
                                        <!-- <h2 class="h5 mb-1 section-title">
                                            <i class="bi bi-plus" aria-hidden="true"></i>
                                            <span>Validation Form</span>
                                        </h2> -->
                                        <p class="text-muted mb-0">
                                            <span class="text-danger">*</span>
                                            Required fields
                                        </p>
                                    </div>
                                </div>
                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label for="chooseFile" class="form-label">File</label>
                                        <span class="text-danger">*</span>
                                        <select name="file_id" class="form-select" id="chooseFile" <?= $isEdit ? 'disabled' : 'required' ?>>
                                            <option <?= $isEdit ? '' : 'selected' ?> disabled value="">--Choose a file--</option>
                                            <?php foreach ($fetchFiles as $files) { ?>
                                                <option value="<?php echo $files['id']; ?>"
                                                    <?= $isEdit && (int)$record['file_id'] === (int)$files['id'] ? 'selected' : '' ?>>
                                                    <?php echo $files['file_number'] . " - "  . $files['subject'] ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <div class="invalid-feedback">File is required.</div>

                                    </div>
                                    <div class="col-md-6">
                                        <label for="fileType" class="form-label">Document Type</label>
                                        <span class="text-danger">*</span>
                                        <select name="doc_type_id" id="fileType" class="form-select" required>
                                            <option <?= $isEdit ? '' : 'selected' ?> disabled value="">--Choose a Type--</option>
                                            <?php foreach ($fetchDocTypes as $docs) { ?>
                                                <option value="<?php echo $docs['id']; ?>"
                                                    <?= $isEdit && (int)$record['doc_type_id'] === (int)$docs['id'] ? 'selected' : '' ?>>
                                                    <?php echo $docs['name']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <div class="invalid-feedback">Document Type is required.</div>

                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="formName">Document Name</label>
                                        <span class="text-danger">*</span>
                                        <input class="form-control allow-alphanumeric" name="doc_name" maxlength="200" id="formName" type="text"
                                            value="<?= val($record, 'doc_name') ?>" required>
                                        <div class="invalid-feedback">Document Name is required.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="formDate">Document Date</label>
                                        <span class="text-danger">*</span>
                                        <input class="form-control" id="formDate" type="text" name="doc_date" placeholder="Select date"
                                            value="<?= val($record, 'doc_date') ?>" required>
                                        <div class="invalid-feedback">Document date is required.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="file" class="form-label">Pdf Upload</label>
                                        <?php if (!$isEdit) { ?><span class="text-danger">*</span><?php } ?>
                                        <input name="doc_pdf" type="file" id="file" class="form-control" accept=".pdf,application/pdf"
                                            <?= $isEdit ? '' : 'required' ?>>
                                        <div class="invalid-feedback">Select a pdf.</div>

                                        <?php if ($isEdit && !empty($record['doc_pdf'])) { ?>
                                            <div class="form-text">
                                                Current file: <strong><?= htmlspecialchars(basename($record['doc_pdf']), ENT_QUOTES, 'UTF-8') ?></strong>
                                                <a href="serve_document.php?id=<?= (int)$record['id'] ?>" target="_blank" rel="noopener noreferrer">View PDF</a>
                                                <br>Leave empty to keep the current file.
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <!-- <div class="col-md-6"><label class="form-label" for="formPlan">Plan</label><select class="form-select" id="formPlan" required>
                                            <option value="">Choose plan</option>
                                            <option>Starter</option>
                                            <option>Business</option>
                                            <option>Enterprise</option>
                                        </select>
                                        <div class="invalid-feedback">Choose a plan.</div>
                                    </div> -->
                                    <!-- <div class="col-md-6"><label class="form-label" for="formBudget">Remark</label><input class="form-control" id="formBudget" type="text" min="1" required>
                                        <div class="invalid-feedback">Enter a valid budget.</div>
                                    </div> -->
                                    <div class="col-12">
                                        <label class="form-label" for="formRemarks">Remarks</label>
                                        <span class="text-danger">*</span>
                                        <textarea class="form-control allow-alphanumeric" name="remarks" maxlength="500" id="formRemarks" rows="4" required><?= val($record, 'remarks') ?></textarea>
                                        <div class="invalid-feedback">Remarks is required.</div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Update' : 'Submit Form' ?></button>
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

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/vendors/flatpickr/flatpickr.min.js"></script>
    <script src="../assets/js/sweetalert2.js"></script>
    <script src="../assets/js/allowed_characters.js"></script>
    <script src="../assets/js/add_docs_insert.js?v=2"></script>
    <script>
        flatpickr("#formDate", {
            altInput: true, // shows a friendly date to the user
            altFormat: "d-m-Y", // what the user sees: 31-12-2026
            dateFormat: "Y-m-d", // what is sent to PHP: 2026-12-31
            allowInput: true,
            maxDate: "today"
        });

        const fpDark = document.getElementById("fpDark");

        function applyPickerTheme() {
            const html = document.documentElement;
            const theme = html.getAttribute("data-bs-theme") ||
                (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");

            fpDark.media = theme === "dark" ? "all" : "not all";
        }

        applyPickerTheme();

        // Update when the theme toggle button is clicked
        new MutationObserver(applyPickerTheme).observe(document.documentElement, {
            attributes: true,
            attributeFilter: ["data-bs-theme"]
        });
    </script>
</body>

</html>