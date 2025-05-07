<?php
$db_host = "localhost";
    $db_user = "root";
    $db_pass = "S@utech2024!";
    $db_name = "clientzone";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($_FILES['doc_file']['error'] === 0) {
    $filename = time() . '_' . basename($_FILES['doc_file']['name']);
    move_uploaded_file($_FILES['doc_file']['tmp_name'], "uploads/$filename");

    $stmt = $conn->prepare("INSERT INTO client_documents (client_id, name, filename) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $_POST['client_id'], $_POST['doc_name'], $filename);
    $stmt->execute();
}
header("Location: clientinfo.php?view=" . $_POST['client_id'] . "&tab=docs");
exit;
?>

