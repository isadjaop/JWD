<?php
include 'Database.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = $conn->real_escape_string($_POST['username']);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email     = $conn->real_escape_string($_POST['email']);

    $sql = "INSERT INTO users (username,password,full_name,email) VALUES (?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $username, $password, $full_name, $email);
    if ($stmt->execute()) {
        header('Location: login.php?registered=1');
        exit;
    } else {
        $error = $stmt->error;
    }
    $stmt->close();
}
?>
<h2>Register</h2>
<?php if (!empty($error)): ?><p class="error"><?=$error?></p><?php endif; ?>
<form method="post">
  <input name="full_name" required placeholder="Nama Lengkap">
  <input name="email" type="email" required placeholder="Email">
  <input name="username" required placeholder="Username">
  <input name="password" type="password" required placeholder="Password">
  <button type="submit">Register</button>
</form>
<?php include 'footer.php'; ?>