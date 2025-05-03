<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "clientzone");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
if ($id === 0) {
    die("Invalid Client ID");
}

// Get client name
$client = $conn->query("SELECT client_name FROM clients WHERE id = $id")->fetch_assoc();
$client_name = $client ? htmlspecialchars($client['client_name']) : 'Unknown Client';

// Handle document upload
if (isset($_POST['upload_doc']) && isset($_FILES['doc_file'])) {
    $docName = $conn->real_escape_string($_POST['doc_name']);
    $fileName = time() . '_' . basename($_FILES['doc_file']['name']);
    $targetDir = __DIR__ . "/uploads/";
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($_FILES['doc_file']['tmp_name'], $targetFile)) {
        $conn->query("INSERT INTO client_documents (client_id, name, filename) VALUES ($id, '$docName', '$fileName')");
    }
}

// Handle document delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $docId = intval($_GET['delete']);
    $file = $conn->query("SELECT filename FROM client_documents WHERE id = $docId AND client_id = $id")->fetch_assoc();
    if ($file) {
        @unlink(__DIR__ . "/uploads/" . $file['filename']);
    }
    $conn->query("DELETE FROM client_documents WHERE id = $docId AND client_id = $id");
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
        function confirmDelete(docId) {
            if (confirm("Are you sure you want to delete this document?")) {
                window.location.href = "?client_id=<?= $id ?>&delete=" + docId;
            }
        }
    </script>
</head>

<body class="p-4">
    <div class="container">
        <h3>📁 Documents for <span class="text-primary"><?= $client_name ?></span></h3>

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
                        <td><a href="uploads/<?= htmlspecialchars($doc['filename']) ?>" target="_blank"><?= htmlspecialchars($doc['filename']) ?></a></td>
                        <td>
                            <button class="btn btn-sm text-danger" onclick="confirmDelete(<?= $doc['id'] ?>)" title="Delete">
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