<?php
include 'Database.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $conn->real_escape_string($_POST['name']);
    $email   = $conn->real_escape_string($_POST['email']);
    $message = $conn->real_escape_string($_POST['message']);

    $sql = "INSERT INTO Tabel_pesan (name, email, pesan) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('sss', $name, $email, $message);
        if ($stmt->execute()) {
            echo '<p>Pesan berhasil dikirim. <a href="contact.php">Kembali</a></p>';
        } else {
            echo '<p>Error saat mengirim: ' . $stmt->error . '</p>';
        }
        $stmt->close();
    } else {
       
        echo '<p>Error preparing statement: ' . $conn->error . '</p>';
    }
} else {
    header('Location: contact.php');
    exit;
}
?>