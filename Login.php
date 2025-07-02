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
    if ($stmt->fetch() && password_verify($password,$hash)) {
        $_SESSION['user_id'] = $id;
        header('Location: profil.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
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