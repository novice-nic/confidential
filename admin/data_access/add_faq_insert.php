<?php
session_start();
header("Content-Type: application/json");

function checkMaxLength(string $value, int $max, string $label): void
{
    if ($value !== '' && mb_strlen($value) > $max) {
        throw new Exception("$label must not be more than $max characters.");
    }
}

try {
    require_once __DIR__ . '/../../include/conn.php';

    // 1. Only logged-in admins
    if (!isset($_SESSION['user_id']) || (int)$_SESSION['role'] !== 1) {
        throw new Exception("You are not allowed to do this.");
    }

    // 2. Only POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // 3. Read the values
    $id       = (int)($_POST['id'] ?? 0);   // 0 = new FAQ, above 0 = update
    $isEdit   = $id > 0;
    $fileId   = (int)($_POST['file_id'] ?? 0);
    $question = trim($_POST['question'] ?? '');
    $answer   = trim($_POST['answer'] ?? '');

    // Ticked documents: keep only whole numbers above 0, and remove repeats
    $rawIds = $_POST['document_ids'] ?? [];
    if (!is_array($rawIds)) {
        $rawIds = [];
    }
    $docIds = array_values(array_unique(array_filter(
        array_map('intval', $rawIds),
        function ($v) { return $v > 0; }
    )));

    // Edit: the file cannot be changed, so read it from the saved FAQ
    if ($isEdit) {
        $stmt = $conn->prepare(
            "SELECT file_id FROM public.faq WHERE id = :id AND is_deleted = FALSE"
        );
        $stmt->execute([':id' => $id]);
        $savedFileId = $stmt->fetchColumn();
        if ($savedFileId === false) {
            throw new Exception("FAQ not found.");
        }
        $fileId = (int)$savedFileId;
    }

    // Required checks
    if ($fileId <= 0)         throw new Exception("Please choose a file.");
    if ($question === '')     throw new Exception("Question is required.");
    if ($answer === '')       throw new Exception("Answer is required.");
    if (count($docIds) === 0) throw new Exception("Please select at least one document.");

    // Length checks
    checkMaxLength($question, 300, "Question");
    checkMaxLength($answer, 1000, "Answer");

    // The file must exist and not be deleted
    $stmt = $conn->prepare(
        "SELECT 1 FROM public.file_master WHERE id = :id AND is_deleted = FALSE"
    );
    $stmt->execute([':id' => $fileId]);
    if (!$stmt->fetch()) {
        throw new Exception("Selected file does not exist.");
    }

    // Every ticked document must exist, not be deleted, and belong to this file
    $marks = implode(',', array_fill(0, count($docIds), '?'));
    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM public.file_document
         WHERE file_id = ? AND is_deleted = FALSE AND id IN ($marks)"
    );
    $stmt->execute(array_merge([$fileId], $docIds));
    if ((int)$stmt->fetchColumn() !== count($docIds)) {
        throw new Exception("One or more selected documents are not valid for this file.");
    }

    // 4. Save the FAQ and its links together
    $conn->beginTransaction();

    if ($isEdit) {
        // ---------- UPDATE ----------
        $stmt = $conn->prepare(
            "UPDATE public.faq
             SET question = :question, answer = :answer,
                 total_docs = :total_docs, modified_at = :modified_at
             WHERE id = :id AND is_deleted = FALSE"
        );
        $stmt->execute([
            ':question'    => $question,
            ':answer'      => $answer,
            ':total_docs'  => count($docIds),
            ':modified_at' => date('Y-m-d H:i:s'),
            ':id'          => $id,
        ]);

        // Remove the old links: the new ticks are added below
        $stmt = $conn->prepare("DELETE FROM public.faq_document WHERE faq_id = :id");
        $stmt->execute([':id' => $id]);

        $faqId = $id;
        $message = "FAQ updated successfully.";
    } else {
        // ---------- INSERT ----------
        $stmt = $conn->prepare(
            "INSERT INTO public.faq (file_id, question, answer, total_docs, created_at)
             VALUES (:file_id, :question, :answer, :total_docs, :created_at)
             RETURNING id"
        );
        $stmt->execute([
            ':file_id'    => $fileId,
            ':question'   => $question,
            ':answer'     => $answer,
            ':total_docs' => count($docIds),
            ':created_at' => date('Y-m-d H:i:s'),
        ]);
        $faqId = (int)$stmt->fetchColumn();
        $message = "FAQ added successfully.";
    }

    // One row for each ticked document
    $link = $conn->prepare(
        "INSERT INTO public.faq_document (faq_id, document_id) VALUES (:faq_id, :document_id)"
    );
    foreach ($docIds as $docId) {
        $link->execute([':faq_id' => $faqId, ':document_id' => $docId]);
    }

    $conn->commit();

    echo json_encode([
        "status"  => "Success",
        "message" => $message,
        "mode"    => $isEdit ? "update" : "insert"
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
    http_response_code(500);
    echo json_encode(["status" => "Error", "message" => "Database error. Please try again."]);
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
    http_response_code(400);
    echo json_encode(["status" => "Error", "message" => $e->getMessage()]);
}