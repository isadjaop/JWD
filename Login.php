<?php

if (session_status() === PHP_SESSION_NONE) session_start();
include 'Database.php'; //

$error = '';

if (isset($_POST['username'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $q = "SELECT ID, username, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($q);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Cek jika role adalah 'perusahaan', validasi statusnya dulu
            if ($user['role'] === 'perusahaan') {
                $stmt_status = $conn->prepare("SELECT status FROM perusahaan WHERE user_id = ?");
                $stmt_status->bind_param('i', $user['ID']);
                $stmt_status->execute();
                $p_res = $stmt_status->get_result()->fetch_assoc();
                $stmt_status->close();

                if (!$p_res || $p_res['status'] !== 'approved') {
                    $error = 'Akun perusahaan Anda sedang ditinjau atau belum disetujui oleh Admin.';
                    // Dengan adanya $error, skrip akan lanjut ke bawah dan menampilkan pesan error, tidak melakukan login.
                } else {
                    $_SESSION['user_id'] = $user['ID'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    header('Location: dashboard_perusahaan.php');
                    exit; 
                }
            } else {
                 // Jika bukan perusahaan (admin atau user), langsung set session dan redirect
                $_SESSION['user_id'] = $user['ID'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: admin.php');
                    exit; 
                } else {
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>