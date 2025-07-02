<?php
include 'Database.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();

    try {
        $username  = $conn->real_escape_string($_POST['username']);
        $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $email     = $conn->real_escape_string($_POST['email']);
        $role      = 'perusahaan'; 

        $company_name = $conn->real_escape_string($_POST['company_name']);
        $description  = $conn->real_escape_string($_POST['description']);
        $address      = $conn->real_escape_string($_POST['address']);
        $contact      = $conn->real_escape_string($_POST['contact']);

        $sql_user = "INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param('sssss', $username, $password, $company_name, $email, $role);
        $stmt_user->execute();


        $user_id = $conn->insert_id;
        $sql_company = "INSERT INTO perusahaan (user_id, company_name, description, address, contact) VALUES (?, ?, ?, ?, ?)";
        $stmt_company = $conn->prepare($sql_company);
        $stmt_company->bind_param('issss', $user_id, $company_name, $description, $address, $contact);
        $stmt_company->execute();

        $conn->commit();

        header('Location: login.php?registered=1');
        exit;

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $error = "Registrasi Gagal: " . $exception->getMessage();
    }
}
?>
<h2>Registrasi Perusahaan</h2>
    <?php if (!empty($error)): ?><p class="error"><?=$error?></p><?php endif; ?>
        <form method="post">
            <h3>Data Akun</h3>
            <input name="username" required placeholder="Username Akun">
            <input name="email" type="email" required placeholder="Email Perusahaan">
            <input name="password" type="password" required placeholder="Password Akun">
            <hr>
            <h3>Data Profil Perusahaan</h3>
            <input name="company_name" required placeholder="Nama Perusahaan">
            <textarea name="description" required placeholder="Deskripsi Perusahaan"></textarea>
            <textarea name="address" required placeholder="Alamat Perusahaan"></textarea>
            <input name="contact" required placeholder="Kontak (No. Telp/Email HRD)">
            <button type="submit">Register Perusahaan</button>
        </form>
<?php include 'footer.php'; ?>