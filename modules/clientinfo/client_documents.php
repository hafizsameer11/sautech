<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);


    $db_host = "localhost";
    $db_user = "client_zone";
    $db_pass = "S@utech2024!";
    $db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
if ($id === 0) {
    die("Invalid Client ID");
}

$client = $conn->query("SELECT client_name FROM clients WHERE id = $id")->fetch_assoc();
$client_name = $client ? htmlspecialchars($client['client_name']) : 'Unknown Client';

// Handle document upload
if (isset($_POST['upload_doc']) && isset($_FILES['doc_file'])) {
    if ($_FILES['doc_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['message'] = [
            'type' => 'danger',
            'text' => "Error uploading file: " . $_FILES['doc_file']['error']
        ];
    } else {
        $docName = $conn->real_escape_string($_POST['doc_name']);
        $fileName = time() . '_' . basename($_FILES['doc_file']['name']);
        $targetDir = __DIR__ . "/uploads/";
        $targetFile = $targetDir . $fileName;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (file_exists($_FILES['doc_file']['tmp_name'])) {
            if (move_uploaded_file($_FILES['doc_file']['tmp_name'], $targetFile)) {
                $conn->query("INSERT INTO client_documents (client_id, name, filename) VALUES ($id, '$docName', '$fileName')");
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => "File uploaded successfully."
                ];
            } else {
                $_SESSION['message'] = [
                    'type' => 'danger',
                    'text' => "Failed to upload file. Please check file permissions or disk space."
                ];
            }
        } else {
            $_SESSION['message'] = [
                'type' => 'danger',
                'text' => "Temporary file not found. Possible upload error."
            ];
        }
    }
    header("Location: client_documents.php?client_id=$id");
    exit;
}

// Handle document delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $docId = intval($_GET['delete']);
    $file = $conn->query("SELECT filename FROM client_documents WHERE id = $docId AND client_id = $id")->fetch_assoc();
    if ($file) {
        @unlink(__DIR__ . "/uploads/" . $file['filename']);
    }
    $conn->query("DELETE FROM client_documents WHERE id = $docId AND client_id = $id");
    $_SESSION['message'] = [
        'type' => 'success',
        'text' => "Document deleted successfully."
    ];
    header("Location: client_documents.php?client_id=$id");
    exit;
}

// Get document list
$docs = $conn->query("SELECT * FROM client_documents WHERE client_id = $id ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>

<head>
    <title>📁 Client Documents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <script>
        const clientId = <?= json_encode($id) ?>;
        function confirmDelete(docId) {
            if (confirm("Are you sure you want to delete this document?")) {
                window.location.href = "?client_id=" + clientId + "&delete=" + docId;
            }
        }
    </script>
</head>

<body class="p-4">
    <div class="container">
        <!-- Bootstrap Alert -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message']['type'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['message']['text']) ?>
                <!-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> -->
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="d-flex align-items-center">
            <?php

            // Store the current URL in the session as the previous URL
            if (!isset($_SESSION['previous_url']) || $_SESSION['previous_url'] !== $_SERVER['REQUEST_URI']) {
                $_SESSION['previous_url'] = $_SERVER['HTTP_REFERER'] ?? 'index.php';
            }

            // Retrieve the previous URL from the session
            $previous = $_SESSION['previous_url'];

            echo '<a href="' . htmlspecialchars($previous) . '" style="text-decoration: none; color: white; background-color: #1E2A38; padding:0; border-radius: 5px; font-family: Arial, sans-serif;margin-right:10px">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="30" height="30" color="#ffffff" fill="none">
    <path d="M15 6C15 6 9.00001 10.4189 9 12C8.99999 13.5812 15 18 15 18" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
</svg></a>';
            ?>
            <h3>📁 Documents for <span class="text-primary"><?= $client_name ?></span></h3>
        </div>

        <form class="row g-3 mt-4" method="POST" enctype="multipart/form-data">
            <div class="col-md-5">
                <input type="text" name="doc_name" class="form-control" placeholder="Document Name" required>
            </div>
            <div class="col-md-5">
                <input type="file" name="doc_file" class="form-control" required>
            </div>
            <div class="col-md-2">
                <button type="submit" name="upload_doc" class="btn btn-success w-100">📎 Upload</button>
            </div>
        </form>

        <table class="table table-bordered table-hover mt-5">
            <thead class="table-light">
                <tr>
                    <th>Document Name</th>
                    <th>File Name</th>
                    <th style="width:100px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($doc = $docs->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($doc['name']) ?></td>
                        <td><a href="uploads/<?= htmlspecialchars($doc['filename']) ?>"
                                target="_blank"><?= htmlspecialchars($doc['filename']) ?></a></td>
                        <td>
                            <button class="btn btn-sm text-danger" onclick="confirmDelete(<?= $doc['id'] ?>)"
                                title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>