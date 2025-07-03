<?php
// isadjaop/jwd/JWD-f3962bf7909aca9720f0c8fc23b93c722f474e43/Login.php - PERBAIKAN TOTAL

if (session_status() === PHP_SESSION_NONE) session_start();
include 'Database.php'; 

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Ambil semua data user yang relevan, bukan hanya id dan password
    $sql = "SELECT ID, username, password, role FROM users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Jika password benar, lanjutkan ke pengecekan role
            
            // Pengecekan khusus untuk role 'perusahaan'
            if ($user['role'] === 'perusahaan') {
                $stmt_status = $conn->prepare("SELECT status FROM perusahaan WHERE user_id = ?");
                $stmt_status->bind_param('i', $user['ID']);
                $stmt_status->execute();
                $p_res = $stmt_status->get_result()->fetch_assoc();
                $stmt_status->close();

                if ($p_res && $p_res['status'] === 'approved') {
                    // Jika disetujui, buat session dan redirect ke dashboard perusahaan
                    $_SESSION['user_id'] = $user['ID'];
                    $_SESSION['role'] = $user['role'];
                    header('Location: dashboard_perusahaan.php');
                    exit;
                } else {
                    // Jika status pending atau rejected
                    $error = 'Akun perusahaan Anda sedang ditinjau atau belum disetujui.';
                }
            } else {
                // Untuk role 'admin' dan 'user'
                $_SESSION['user_id'] = $user['ID'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: admin.php');
                    exit;
                } else { // Asumsi role 'user'
                    header('Location: profil.php');
                    exit;
                }
            }
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Username atau password salah.';
    }
    $stmt->close();
}

include 'header.php';
?>

<h2>Login</h2>
<?php if (!empty($_GET['registered'])): ?><p>Registrasi sukses. Silakan login.</p><?php endif; ?>
<?php if (!empty($error)): ?><p class="error" style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
  <input name="username" required placeholder="Username">
  <input name="password" type="password" required placeholder="Password">
  <button type="submit">Login</button>
</form>

<?php include 'footer.php'; ?>