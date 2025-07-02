<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'Database.php';

// --- BLOK PROTEKSI ADMIN YANG KETAT ---
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=not_logged_in');
    exit;
}

$stmt_role = $conn->prepare("SELECT role FROM users WHERE ID = ?");
$stmt_role->bind_param('i', $_SESSION['user_id']);
$stmt_role->execute();
$result_role = $stmt_role->get_result();
$user_session = $result_role->fetch_assoc();
$stmt_role->close();

if (!$user_session || $user_session['role'] !== 'admin') {
    http_response_code(403);
    die('<h1>Akses Ditolak</h1><p>Halaman ini khusus untuk Administrator.</p>');
}
// --- AKHIR BLOK PROTEKSI ---

// --- LOGIKA UNTUK PROSES AKSI (APPROVE/REJECT DLL) ---
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if ($action === 'approve_company' && $id > 0) {
    $stmt_approve = $conn->prepare("UPDATE perusahaan SET status = 'approved' WHERE id = ?");
    $stmt_approve->bind_param('i', $id);
    $stmt_approve->execute();
    $stmt_approve->close();
    header('Location: admin.php?view=perusahaan&status=approved');
    exit;
}
if ($action === 'reject_company' && $id > 0) {
    $stmt_reject = $conn->prepare("UPDATE perusahaan SET status = 'rejected' WHERE id = ?");
    $stmt_reject->bind_param('i', $id);
    $stmt_reject->execute();
    $stmt_reject->close();
    header('Location: admin.php?view=perusahaan&status=rejected');
    exit;
}
// Tambahkan logika lain seperti delete user, dll. di sini

include 'header.php';
$view = $_GET['view'] ?? 'users'; // Halaman default adalah kelola users
?>
<style> /* CSS Sederhana untuk Tab */
    .admin-nav { margin-bottom: 20px; border-bottom: 2px solid #ccc; }
    .admin-nav a { display: inline-block; padding: 10px 15px; text-decoration: none; color: #333; }
    .admin-nav a.active { border-bottom: 2px solid #005A9E; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px;}
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left;}
    th { background-color: #f2f2f2; }
</style>

<h2>Admin Panel</h2>
<nav class="admin-nav">
    <a href="?view=users" class="<?= $view == 'users' ? 'active' : '' ?>">Kelola Pengguna</a>
    <a href="?view=perusahaan" class="<?= $view == 'perusahaan' ? 'active' : '' ?>">Kelola Perusahaan</a>
    <a href="?view=jobs" class="<?= $view == 'jobs' ? 'active' : '' ?>">Kelola Lowongan</a>
    <a href="?view=pesan" class="<?= $view == 'pesan' ? 'active' : '' ?>">Lihat Pesan Kontak</a>
</nav>

<div>
    <?php
    switch ($view) {
        case 'perusahaan':
            echo "<h3>Manajemen Perusahaan</h3>";
            $res = $conn->query("SELECT p.*, u.email FROM perusahaan p JOIN users u ON p.user_id = u.ID ORDER BY p.created_at DESC");
            echo "<table><tr><th>Nama Perusahaan</th><th>Email Akun</th><th>Status</th><th>Aksi</th></tr>";
            while($row = $res->fetch_assoc()) {
                echo "<tr><td>".htmlspecialchars($row['company_name'])."</td>";
                echo "<td>".htmlspecialchars($row['email'])."</td>";
                echo "<td>".htmlspecialchars(ucfirst($row['status']))."</td><td>";
                if ($row['status'] === 'pending') {
                    echo "<a href='?view=perusahaan&action=approve_company&id=".$row['id']."'>Setujui</a> | ";
                    echo "<a href='?view=perusahaan&action=reject_company&id=".$row['id']."'>Tolak</a>";
                } else {
                    echo "Tidak ada aksi";
                }
                echo "</td></tr>";
            }
            echo "</table>";
            break;

        case 'jobs':
            echo "<h3>Manajemen Lowongan Pekerjaan</h3>";
            // Tampilkan semua lowongan dari semua perusahaan
            $res = $conn->query("SELECT * FROM jobs ORDER BY dibuat_tgl DESC");
             echo "<table><tr><th>ID</th><th>Perusahaan</th><th>Posisi</th><th>Aksi</th></tr>";
            while ($j = $res->fetch_assoc()){
                echo "<tr><td>".$j['id']."</td><td>".htmlspecialchars($j['perusahaan'])."</td><td>".htmlspecialchars($j['posisi'])."</td>";
                echo "<td><a href='admin.php?view=jobs&action=delete_job&id=".$j['id']."' onclick='return confirm(\"Hapus?\")'>Hapus</a></td></tr>";
            }
            echo "</table>";
            break;
        
        case 'pesan':
            echo "<h3>Pesan Masuk</h3>";
            // Tampilkan pesan dari tabel_pesan
            $res = $conn->query("SELECT * FROM tabel_pesan ORDER BY id DESC");
            echo "<table><tr><th>Nama</th><th>Email</th><th>Pesan</th></tr>";
            while($row = $res->fetch_assoc()) {
                echo "<tr><td>".htmlspecialchars($row['name'])."</td><td>".htmlspecialchars($row['email'])."</td><td>".nl2br(htmlspecialchars($row['pesan']))."</td></tr>";
            }
            echo "</table>";
            break;
            
        case 'users':
        default:
            echo "<h3>Manajemen Pengguna</h3>";
            $res = $conn->query("SELECT ID, username, full_name, email, role, created_at FROM users ORDER BY created_at DESC");
            echo "<table><tr><th>Username</th><th>Nama Lengkap</th><th>Email</th><th>Role</th><th>Aksi</th></tr>";
            while($row = $res->fetch_assoc()) {
                echo "<tr><td>".htmlspecialchars($row['username'])."</td><td>".htmlspecialchars($row['full_name'])."</td>";
                echo "<td>".htmlspecialchars($row['email'])."</td><td>".htmlspecialchars($row['role'])."</td>";
                echo "<td><a href='admin.php?view=users&action=delete_user&id=".$row['ID']."' onclick='return confirm(\"Hapus?\")'>Hapus</a></td></tr>";
            }
            echo "</table>";
            break;
    }
    ?>
</div>

<?php include 'footer.php'; ?>