<?php
session_start();
if (!isset($_SESSION['user_id']) || (int)$_SESSION['role'] !== 1) {
    header('Location: ../login.php');
    exit();
}

require_once __DIR__ . '/../include/conn.php';

// Edit mode: the link is file_master.php?id=12
$editId = (int)($_POST['id'] ?? 0);
$record = null;

if ($editId > 0) {
    $stmt = $conn->prepare(
        "SELECT id, file_number, subject, remarks
         FROM public.file_master
         WHERE id = :id AND is_deleted = FALSE"
    );
    $stmt->execute([':id' => $editId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    // Wrong or deleted id: go back to the report
    if (!$record) {
        header('Location: rpt_files.php');
        exit();
    }
}

$isEdit = $record !== null;
function val(?array $r, string $k): string
{
    return htmlspecialchars($r[$k] ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <title>File Master</title>

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
                                <i class="bi bi-file-earmark"></i>
                            </span>
                            <div>
                                <!-- <p class="eyebrow mb-1">Inputs</p> -->
                                <h1 class="h3 mb-1"><?= $isEdit ? 'Edit File' : 'File Master' ?></h1>
                                <!-- <p class="text-muted mb-0">Fill in the details below to create a new master file record.</p> -->
                            </div>
                        </div>

                    </div>

                    <section class="row g-3 justify-content-center">
                        <div class="col-12">
                            <form class="panel needs-validation form-file" id="fileMasterForm" novalidate>
                                <!-- hidden id -->
                                <input type="hidden" name="id" value="<?= $isEdit ? (int)$record['id'] : 0 ?>">
                                <div class="panel-header">
                                    <div>
                                        <!-- <h2 class="h5 mb-1 section-title">
                                            <i class="bi bi-file-earmark" aria-hidden="true"></i>
                                            <span>Validation Form</span>
                                        </h2> -->
                                        <p class="text-muted mb-0">
                                            <span class="text-danger">*</span>
                                            Required Fields
                                        </p>
                                    </div>
                                </div>
                                <!-- <div class="panel-header">
                                    <div>
                                        <h2 class="h5 mb-1 section-title"><i class="bi bi-ui-checks-grid" aria-hidden="true"></i><span>Validation Form</span></h2>
                                        <p class="text-muted mb-0">Bootstrap-ready fields with custom validation feedback.</p>
                                    </div>
                                </div> -->
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="formNumber">File Number</label>
                                        <span class="text-danger">*</span>
                                        <input class="form-control allow-slash-hyphen" maxlength="15" id="formNumber"
                                            name="fileNumber" value="<?= val($record, 'file_number') ?>" pattern="[A-Za-z0-9\/\-]+" required>
                                        <div class="invalid-feedback">
                                            File number is required. Only letters, numbers, - and / are allowed.
                                        </div>
                                        <div class="valid-feedback">Looks good!</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="subject">Subject</label>
                                        <span class="text-danger">*</span>
                                        <input class="form-control allow-alphanumeric" maxlength="200" id="subject"
                                            name="subject" value="<?= val($record, 'subject') ?>" type="text" required>
                                        <div class="invalid-feedback">Subject is required.</div>
                                        <div class="valid-feedback">Looks good!</div>
                                    </div>
                                    <!-- <div class="col-md-6">
                                        <label class="form-label" for="formPlan">Plan</label>
                                        <select class="form-select" id="formPlan" required>
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
                                    <div class="col-md-12">
                                        <label class="form-label" for="remark">Remarks</label>
                                        <span class="text-danger">*</span>
                                        <textarea class="form-control allow-alphanumeric" maxlength="500" id="remark" name="remark" rows="4" required><?= val($record, 'remarks') ?></textarea>
                                        <div class="invalid-feedback">Remarks is required.</div>
                                        <div class="valid-feedback">Looks good!</div>
                                    </div>
                                    <!-- <div class="col-md-6">
                                        <label class="form-label" for="created_at">Created At</label>
                                        <input class="form-control" id="created_at" name="created_at" type="date" required>
                                        <div class="invalid-feedback">Created At is required.</div>
                                        <div class="valid-feedback">Looks good!</div>
                                    </div> -->
                                </div>
                                <div class="d-flex justify-content-center mt-4">
                                    <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Update' : 'Submit' ?></button>
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
    <script src="../assets/js/file_master_insert.js"></script>

</body>

</html>