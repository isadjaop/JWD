<?php


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