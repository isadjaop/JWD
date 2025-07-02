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
    <li><a href="lowongan.php">Lowongan</a></li>
    <li><a href="contact.php">Contact</a></li>
    <li><a href="informasi_umum.php">Informasi Umum</a></li>

    <?php if (isset($_SESSION['user_id'])): ?>
      <li><a href="profil.php">Profil Saya</a></li>

      <?php if ($_SESSION['role'] === 'admin'): ?>
        <li><a href="admin.php">Admin Panel</a></li>
      <?php elseif ($_SESSION['role'] === 'perusahaan'): ?>
        <li><a href="dashboard_perusahaan.php">Dashboard Perusahaan</a></li>
      <?php elseif ($_SESSION['role'] === 'user'): ?>
        <li><a href="profil_upload.php">Kelola Dokumen</a></li>
      <?php endif; ?>

      <li><a href="logout.php">Logout</a></li>
      
    <?php else: ?>
      <li><a href="login.php">Login</a></li>
      <li><a href="register.php">Register User</a></li>
      <li><a href="perusahaan_register.php">Register Perusahaan</a></li>
    <?php endif; ?>
  </ul>
</nav>
<hr>