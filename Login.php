<?php
include 'Database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id,password FROM users WHERE username=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s',$username);
    $stmt->execute();
    $stmt->bind_result($id,$hash);
    $result = $stmt->get_result(); 

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($user['role'] === 'perusahaan') {
                $stmt_status = $conn->prepare("SELECT status FROM perusahaan WHERE user_id = ?");
                $stmt_status->bind_param('i', $user['id']);
                $stmt_status->execute();
                $p_res = $stmt_status->get_result()->fetch_assoc();
                if (!$p_res || $p_res['status'] !== 'approved') {
                    $error = 'Akun perusahaan Anda belum disetujui oleh admin.';
                    goto end_login_process;
                }
            }
    
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; 
    
            if ($user['role'] === 'admin') {
                header('Location: admin.php');
            } elseif ($user['role'] === 'perusahaan') {
                header('Location: dashboard_perusahaan.php');
            } else {
                header('Location: profil.php');
            }
            exit;
        }
    }
    
    $error = 'Username atau password salah.';
    end_login_process: 
    $stmt->close();
}
include 'header.php';
?>
<h2>Login</h2>
<?php if (!empty($_GET['registered'])): ?><p>Registrasi sukses. Silakan login.</p><?php endif; ?>
<?php if (!empty($error)): ?><p class="error"><?=$error?></p><?php endif; ?>
<form method="post">
  <input name="username" required placeholder="Username">
  <input name="password" type="password" required placeholder="Password">
  <button type="submit">Login</button>
</form>
<?php include 'footer.php'; ?>