<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'Database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?status=login_dulu');
    exit;
}
$stmt_role = $conn->prepare("SELECT role FROM users WHERE ID=?");
$stmt_role->bind_param('i', $_SESSION['user_id']);
$stmt_role->execute();
$result_role = $stmt_role->get_result();
$user = $result_role->fetch_assoc();
$stmt_role->close();

if (!$user || $user['role'] !== 'perusahaan') {
    die('Akses Ditolak. Halaman ini hanya untuk Perusahaan.');
}

$user_id = $_SESSION['user_id'];

// --- PROSES CRUD ---

// Proses Tambah Lowongan
if (isset($_POST['add_job'])) {
    $sql = "INSERT INTO jobs (perusahaan, posisi, lokasi, deskripsi, kualifikasi, expiry_date, kontak, dibuat_oleh) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    // Kolom 'perusahaan' diisi nama perusahaan dari form, 'dibuat_oleh' diisi ID user perusahaan
    $stmt->bind_param('sssssssi', $_POST['perusahaan_nama'], $_POST['posisi'], $_POST['lokasi'], $_POST['deskripsi'], $_POST['kualifikasi'], $_POST['expiry_date'], $_POST['kontak'], $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: dashboard_perusahaan.php?status=tambah_sukses');
    exit;
}

// Proses Hapus Lowongan
if (isset($_GET['del_id'])) {
    $job_id_to_delete = $_GET['del_id'];
    // Validasi tambahan: pastikan lowongan ini milik perusahaan yang login
    $stmt = $conn->prepare("DELETE FROM jobs WHERE id=? AND dibuat_oleh=?");
    $stmt->bind_param('ii', $job_id_to_delete, $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: dashboard_perusahaan.php?status=hapus_sukses');
    exit;
}

// Proses Edit akan lebih kompleks, bisa dibuat di halaman terpisah (misal: edit_lowongan.php?id=xx)
// atau menggunakan modal. Untuk sederhana, kita fokus pada Tambah, Lihat, Hapus dulu.

include 'header.php';
?>

<h2>Dashboard Perusahaan</h2>
<p>Selamat datang! Di sini Anda bisa mengelola semua lowongan pekerjaan dari perusahaan Anda.</p>

<hr>
<h3>Posting Lowongan Baru</h3>
<form method="post">
  <input name="perusahaan_nama" required placeholder="Nama Perusahaan (untuk ditampilkan)">
  <input name="posisi" required placeholder="Posisi yang Dibutuhkan">
  <input name="lokasi" required placeholder="Lokasi Kerja (e.g., Jakarta, Remote)">
  <textarea name="deskripsi" required placeholder="Deskripsi Pekerjaan"></textarea>
  <textarea name="kualifikasi" required placeholder="Kualifikasi yang Dibutuhkan"></textarea>
  <label for="expiry_date">Batas Waktu Lamaran:</label>
  <input name="expiry_date" id="expiry_date" type="date" required>
  <input name="kontak" required placeholder="Informasi Kontak (Email/No. HP HRD)">
  <button type="submit" name="add_job">Publikasikan Lowongan</button>
</form>

<hr>
<h3>Lowongan Anda yang Sedang Aktif</h3>
<table border="1" cellpadding="5" width="100%">
  <thead>
    <tr>
      <th>Posisi</th>
      <th>Lokasi</th>
      <th>Batas Waktu</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Query untuk mengambil hanya lowongan yang dibuat oleh user ini
    $stmt_jobs = $conn->prepare("SELECT id, posisi, lokasi, expiry_date FROM jobs WHERE dibuat_oleh = ? ORDER BY dibuat_tgl DESC");
    $stmt_jobs->bind_param('i', $user_id);
    $stmt_jobs->execute();
    $result_jobs = $stmt_jobs->get_result();

    if ($result_jobs->num_rows > 0):
      while ($job = $result_jobs->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($job['posisi']) ?></td>
        <td><?= htmlspecialchars($job['lokasi']) ?></td>
        <td><?= htmlspecialchars($job['expiry_date']) ?></td>
        <td>
          <a href="detail_lowongan.php?id=<?= $job['id'] ?>">Lihat</a> |
          <a href="edit_lowongan.php?id=<?= $job['id'] ?>">Edit</a> |
          <a href="dashboard_perusahaan.php?del_id=<?= $job['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus lowongan ini?')">Hapus</a>
        </td>
      </tr>
      <?php endwhile;
    else: ?>
      <tr>
        <td colspan="4" style="text-align:center;">Anda belum memposting lowongan apapun.</td>
      </tr>
    <?php endif;
    $stmt_jobs->close();
    ?>
  </tbody>
</table>

<?php include 'footer.php'; ?>