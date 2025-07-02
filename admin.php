<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'Database.php';

// 1. Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php?error=not_logged_in');
  exit; // Wajib ada exit setelah header
}

// 2. Ambil role dari database berdasarkan session ID
$stmt = $conn->prepare("SELECT role FROM users WHERE ID = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// 3. Cek apakah user ada dan rolenya adalah 'admin'
if (!$user || $user['role'] !== 'admin') {
  // Jika tidak, hentikan eksekusi dan tampilkan pesan
  http_response_code(403); // Kode 'Forbidden'
  die('<h1>Akses Ditolak</h1><p>Anda tidak memiliki hak untuk mengakses halaman ini.</p>');
}

// Jika lolos semua pengecekan, sisa halaman admin bisa dimuat di sini...
include 'header.php';
echo "<h2>Selamat Datang, Admin!</h2>";
// ...

if (isset($_POST['add_job'])) {
  $sql = "INSERT INTO jobs (perusahaan, posisi, lokasi, deskripsi, kualifikasi, expiry_date, kontak, dibuat_oleh)
          VALUES (?,?,?,?,?,?,?,?)";
  $q = $conn->prepare($sql);
  
  $q->bind_param('sssssssi', $_POST['perusahaan'], $_POST['posisi'], $_POST['lokasi'],
                 $_POST['deskripsi'], $_POST['kualifikasi'], $_POST['expiry_date'],
                 $_POST['kontak'], $_SESSION['user_id']);
  $q->execute(); $q->close();
  header('Location: admin.php'); exit;
}

if (isset($_POST['edit_job'])) {
  $sql = "UPDATE jobs SET perusahaan=?, posisi=?, lokasi=?, deskripsi=?, kualifikasi=?, expiry_date=?, kontak=? WHERE id=?";
  $q = $conn->prepare($sql);
  $q->bind_param('sssssssi', $_POST['perusahaan'], $_POST['posisi'], $_POST['lokasi'],
                 $_POST['deskripsi'], $_POST['kualifikasi'], $_POST['expiry_date'],
                 $_POST['kontak'], $_POST['job_id']);
  $q->execute(); $q->close();
  header('Location: admin.php'); exit;
}

?>
<h2>Admin — Manajemen Lowongan</h2>
<form method="post">
  <input name="perusahaan" placeholder="Nama Perusahaan" required>
  <input name="posisi" placeholder="Posisi" required>
  <input name="lokasi" placeholder="Lokasi" required>
  <textarea name="deskripsi" placeholder="Deskripsi" required></textarea>
  <textarea name="kualifikasi" placeholder="Kualifikasi" required></textarea>
  <input name="expiry_date" type="date" required>
  <input name="kontak" placeholder="Kontak" required>
  <button name="add_job">Tambah</button>
</form>
<hr>
<table border="1" cellpadding="5">
  <tr>
    <th>ID</th><th>Perusahaan</th><th>Posisi</th><th>Aksi</th>
  </tr>
  <?php
  $res = $conn->query("SELECT * FROM jobs ORDER BY dibuat_tgl DESC");
  while ($j = $res->fetch_assoc()): ?>
  <tr>
    <td><?=$j['id']?></td>
    <td><?=htmlspecialchars($j['perusahaan'])?></td>
    <td><?=htmlspecialchars($j['posisi'])?></td>
    <td>
      <a href="admin.php?del_id=<?=$j['id']?>" onclick="return confirm('Hapus?')">Hapus</a>
      <button onclick="populateEditForm(<?=htmlspecialchars(json_encode($j))?>)">Edit</button>
    </td>
  </tr>
  <?php endwhile; ?>
</table>