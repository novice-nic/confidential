<?php
require_once __DIR__ . '/../../include/conn.php';

// Read the filters (all optional)
$fType    = (int)($_GET['doc_type_id'] ?? 0);
$fFile    = (int)($_GET['file_id'] ?? 0);
$fName    = trim($_GET['rule_name'] ?? '');
$fRemarks = trim($_GET['remarks'] ?? '');
$fFrom    = trim($_GET['from_date'] ?? '');
$fTo      = trim($_GET['to_date'] ?? '');

// Ignore dates that are not valid Y-m-d
function validDate(string $d): string
{
    $x = DateTime::createFromFormat('Y-m-d', $d);
    return ($x && $x->format('Y-m-d') === $d) ? $d : '';
}
$fFrom = validDate($fFrom);
$fTo   = validDate($fTo);

// Build the query: only add a condition if the field is filled
$where  = ["fd.is_deleted = FALSE", "fm.is_deleted = FALSE"];
$params = [];

if ($fType > 0) {
    $where[] = "fd.doc_type_id = :type";
    $params[':type'] = $fType;
}
if ($fFile > 0) {
    $where[] = "fd.file_id = :file";
    $params[':file'] = $fFile;
}
if ($fName !== '') {
    $where[] = "fd.rule_name ILIKE :name";
    $params[':name'] = '%' . $fName . '%';
}
if ($fRemarks !== '') {
    $where[] = "fd.remarks ILIKE :remarks";
    $params[':remarks'] = '%' . $fRemarks . '%';
}
if ($fFrom !== '') {
    $where[] = "fd.doc_date >= :from";
    $params[':from'] = $fFrom;
}
if ($fTo !== '') {
    $where[] = "fd.doc_date <= :to";
    $params[':to'] = $fTo;
}

$sql = "
    SELECT
        fd.id, fd.file_id, fm.file_number, fm.subject AS file_subject,
        fd.doc_type_id, dt.name AS doc_type_name,
        fd.rule_name, fd.doc_date, fd.remarks, fd.doc_pdf
    FROM public.file_document AS fd
    INNER JOIN public.file_master AS fm ON fd.file_id = fm.id
    INNER JOIN public.doc_type AS dt ON fd.doc_type_id = dt.id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY fd.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$fetchDocuments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dropdown lists (always show ALL options)
$docTypes = $conn->query("SELECT id, name FROM public.doc_type ORDER BY id ASC")
    ->fetchAll(PDO::FETCH_ASSOC);

$files = $conn->query(
    "SELECT id, file_number, subject FROM public.file_master
     WHERE is_deleted = FALSE ORDER BY id ASC"
)->fetchAll(PDO::FETCH_ASSOC);
