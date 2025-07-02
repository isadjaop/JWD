<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Portal Lowongan</title>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<nav>
  <ul>
    <li><a href="home.php">Home</a></li>
    <li><a href="contact.php">Contact</a></li>
    <li><a href="lowongan.php">Lowongan</a></li>
    <li><a href="profil.php">Profil</a></li>
    <?php if (!isset($_SESSION['user_id'])): ?>
      <li><a href="login.php">Login</a></li>
      <li><a href="register.php">Register</a></li>
    <?php else: ?>
      <li><a href="logout.php">Logout</a></li>
    <?php endif; ?>
    <li><a href="informasi_umum.php">Informasi Umum</a></li>
  </ul>
</nav>
<hr>