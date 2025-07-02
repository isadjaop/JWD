<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'Database.php';

// Proteksi hanya admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}
$stm = $conn->prepare("SELECT role FROM users WHERE id=?");
$stm->bind_param('i', $_SESSION['user_id']);
$stm->execute(); $stm->bind_result($role); $stm->fetch(); $stm->close();
if ($role!=='admin') die('Akses ditolak');

include 'header.php';

// Proses Tambah
if (isset($_POST['add_job'])) {
  $sql = "INSERT INTO jobs (company_name,position,location,description,qualifications,expiry_date,contact_info,created_by)
          VALUES (?,?,?,?,?,?,?,?)";
  $q = $conn->prepare($sql);
  $q->bind_param('ssssssis', $_POST['company_name'],$_POST['position'],$_POST['location'],
                 $_POST['description'],$_POST['qualifications'],$_POST['expiry_date'],
                 $_POST['contact_info'], $_SESSION['user_id']);
  $q->execute(); $q->close();
  header('Location: admin.php'); exit;
}

// Proses Edit
if (isset($_POST['edit_job'])) {
  $sql = "UPDATE jobs SET company_name=?,position=?,location=?,description=?,qualifications=?,expiry_date=?,contact_info=? WHERE id=?";
  $q = $conn->prepare($sql);
  $q->bind_param('sssssisi', $_POST['company_name'],$_POST['position'],$_POST['location'],
                 $_POST['description'],$_POST['qualifications'],$_POST['expiry_date'],
                 $_POST['contact_info'],$_POST['job_id']);
  $q->execute(); $q->close();
  header('Location: admin.php'); exit;
}

// Proses Hapus
if (isset($_GET['del_id'])) {
  $q = $conn->prepare("DELETE FROM jobs WHERE id=?");
  $q->bind_param('i', $_GET['del_id']); $q->execute(); $q->close();
  header('Location: admin.php'); exit;
}

// Tampilkan Form & Tabel
?>
<h2>Admin — Manajemen Lowongan</h2>
<form method="post">
  <input name="company_name" placeholder="Perusahaan" required>
  <input name="position" placeholder="Posisi" required>
  <input name="location" placeholder="Lokasi" required>
  <textarea name="description" placeholder="Deskripsi" required></textarea>
  <textarea name="qualifications" placeholder="Kualifikasi" required></textarea>
  <input name="expiry_date" type="date" required>
  <input name="contact_info" placeholder="Kontak" required>
  <button name="add_job">Tambah</button>
</form>
<hr>
<table border="1" cellpadding="5">
  <tr>
    <th>ID</th><th>Perusahaan</th><th>Posisi</th><th>Aksi</th>
  </tr>
  <?php
  $res = $conn->query("SELECT * FROM jobs ORDER BY created_at DESC");
  while ($j = $res->fetch_assoc()): ?>
  <tr>
    <td><?=$j['id']?></td>
    <td><?=$j['company_name']?></td>
    <td><?=$j['position']?></td>
    <td>
      <a href="admin.php?del_id=<?=$j['id']?>" onclick="return confirm('Hapus?')">Hapus</a>
      <button onclick="populateEditForm(<?=htmlspecialchars(json_encode($j))?>)">Edit</button>
    </td>
  </tr>
  <?php endwhile; ?>
</table>

<!-- Modal atau form edit (contoh sederhana) -->
<form method="post" id="editForm" style="display:none;">
  <h3>Edit Lowongan</h3>
  <input type="hidden" name="job_id">
  <!-- input sama dengan form add -->
  <button name="edit_job">Update</button>
</form>

<script>
function populateEditForm(data) {
  const f = document.getElementById('editForm');
  for (let k in data) {
    if (f.elements[k]) f.elements[k].value = data[k];
  }
  f.style.display = 'block';
}
</script>

<?php include 'footer.php'; ?>