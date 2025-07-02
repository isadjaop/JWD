<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'Database.php';

// --- BLOK PROTEKSI ADMIN ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    die('<h1>Akses Ditolak</h1><p>Halaman ini khusus untuk Administrator.</p>');
}
$action = $_GET['action'] ?? '';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$view = $_GET['view'] ?? 'users'; // Default view adalah tabel users

// Aksi untuk Manajemen Perusahaan
if ($view === 'perusahaan' && $id > 0) {
    if ($action === 'approve_company') {
        $stmt = $conn->prepare("UPDATE perusahaan SET status = 'approved' WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        header('Location: admin.php?view=perusahaan&status=approved');
        exit;
    }
    if ($action === 'reject_company') {
        $stmt = $conn->prepare("UPDATE perusahaan SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        header('Location: admin.php?view=perusahaan&status=rejected');
        exit;
    }
}
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $username, $password, $full_name, $email, $role);
    $stmt->execute();
    header('Location: admin.php?view=users&status=added');
    exit;
}
if ($action === 'delete_user' && $id > 0) {
    // Hati-hati: Sebaiknya non-aktifkan user daripada hapus permanen
    $stmt = $conn->prepare("DELETE FROM users WHERE ID = ? AND role != 'admin'"); 
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: admin.php?view=users&status=deleted');
    exit;
}

include 'header.php';
?>
<style> /* CSS Sederhana untuk Tab */
    .admin-nav { margin-bottom: 20px; border-bottom: 2px solid #ccc; }
    .admin-nav a { display: inline-block; padding: 10px 15px; text-decoration: none; color: #333; }
    .admin-nav a.active { border-bottom: 2px solid #005A9E; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px;}
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left;}
    th { background-color: #f2f2f2; }
    .action-link { color: #007bff; text-decoration: none; }
    .action-link:hover { text-decoration: underline; }
    .form-tambah { background-color: #f9f9f9; padding: 15px; border: 1px solid #ddd; margin-bottom: 20px; }
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
            echo "<table><thead><tr><th>Nama Perusahaan</th><th>Email Akun</th><th>Status</th><th>Aksi</th></tr></thead><tbody>";
            while($row = $res->fetch_assoc()) {
                echo "<tr><td>".htmlspecialchars($row['company_name'])."</td>";
                echo "<td>".htmlspecialchars($row['email'])."</td>";
                echo "<td>".htmlspecialchars(ucfirst($row['status']))."</td><td>";
                if ($row['status'] === 'pending') {
                    echo "<a class='action-link' href='?view=perusahaan&action=approve_company&id=".$row['id']."'>Setujui</a> | ";
                    echo "<a class='action-link' href='?view=perusahaan&action=reject_company&id=".$row['id']."'>Tolak</a>";
                } else {
                    echo "Tindakan Selesai";
                }
                echo "</td></tr>";
            }
            echo "</tbody></table>";
            break;

        case 'jobs':
            echo "<h3>Manajemen Lowongan Pekerjaan</h3><p>Fitur tambah dan edit lowongan oleh admin dapat dikembangkan di sini.</p>";
            $res = $conn->query("SELECT * FROM jobs ORDER BY dibuat_tgl DESC");
            echo "<table><thead><tr><th>ID</th><th>Perusahaan</th><th>Posisi</th><th>Aksi</th></tr></thead><tbody>";
            while ($j = $res->fetch_assoc()){
                echo "<tr><td>".$j['id']."</td><td>".htmlspecialchars($j['perusahaan'])."</td><td>".htmlspecialchars($j['posisi'])."</td>";
                echo "<td><a class='action-link' href='admin.php?view=jobs&action=delete_job&id=".$j['id']."' onclick='return confirm(\"Hapus?\")'>Hapus</a></td></tr>";
            }
            echo "</tbody></table>";
            break;
        
        case 'pesan':
            echo "<h3>Pesan Masuk</h3>";
            $res = $conn->query("SELECT * FROM tabel_pesan ORDER BY id DESC");
            echo "<table><thead><tr><th>Nama</th><th>Email</th><th>Pesan</th></tr></thead><tbody>";
            while($row = $res->fetch_assoc()) {
                echo "<tr><td>".htmlspecialchars($row['name'])."</td><td>".htmlspecialchars($row['email'])."</td><td>".nl2br(htmlspecialchars($row['pesan']))."</td></tr>";
            }
            echo "</tbody></table>";
            break;
            
        case 'users':
        default:
            echo "<h3>Manajemen Pengguna</h3>";
            ?>
            <div class="form-tambah">
                <h4>Tambah Pengguna Baru</h4>
                <form method="post" action="admin.php?view=users">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="text" name="full_name" placeholder="Nama Lengkap" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <select name="role" required>
                        <option value="user">User</option>
                        <option value="perusahaan">Perusahaan</option>
                        <option value="admin">Admin</option>
                    </select>
                    <button type="submit" name="add_user">Tambah User</button>
                </form>
            </div>
            <?php
            $res = $conn->query("SELECT ID, username, full_name, email, role, created_at FROM users ORDER BY created_at DESC");
            echo "<table><thead><tr><th>Username</th><th>Nama Lengkap</th><th>Email</th><th>Role</th><th>Aksi</th></tr></thead><tbody>";
            while($row = $res->fetch_assoc()) {
                echo "<tr><td>".htmlspecialchars($row['username'])."</td><td>".htmlspecialchars($row['full_name'])."</td>";
                echo "<td>".htmlspecialchars($row['email'])."</td><td>".htmlspecialchars($row['role'])."</td>";
                echo "<td>";
                // Admin tidak bisa menghapus dirinya sendiri atau admin lain untuk keamanan
                if ($row['role'] !== 'admin') {
                    echo "<a class='action-link' href='admin.php?view=users&action=delete_user&id=".$row['ID']."' onclick='return confirm(\"Yakin hapus pengguna ini?\")'>Hapus</a>";
                } else {
                    echo "Tidak ada aksi";
                }
                echo "</td></tr>";
            }
            echo "</tbody></table>";
            break;
    }
    ?>
</div>

<?php include 'footer.php'; ?>