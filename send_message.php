<?php
include 'Database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $conn->real_escape_string($_POST['name']);
    $email   = $conn->real_escape_string($_POST['email']);
    $message = $conn->real_escape_string($_POST['message']);

    $sql = "INSERT INTO Tabel_pesan (name, email, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $name, $email, $message);
    if ($stmt->execute()) {
        echo '<p>Pesan berhasil dikirim.</p>';
    } else {
        echo '<p>Error: ' . $stmt->error . '</p>';
    }
    $stmt->close();
} else {
    header('Location: contact.php');
    exit;
}
?>