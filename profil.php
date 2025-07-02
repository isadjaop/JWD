<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'Database.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?status=Silahkan+Login!');
    exit;
}
$stmt = $conn->prepare("SELECT username, full_name, email, role, created_at FROM users WHERE id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $user = $result->fetch_assoc()) {
    echo '<h2>Profil Saya</h2>';
    echo '<ul>';
    foreach ($user as $key => $val) {
        echo '<li>' . ucfirst(str_replace('_',' ',$key)) . ': ' . htmlspecialchars($val) . '</li>';
    }
    echo '</ul>';
} else {
    header('Location: login.php?status=User tidak ditemukan');
    exit;
}
$stmt->close()
?>
<?php include 'footer.php'; ?>