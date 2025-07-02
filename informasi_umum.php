<?php include 'header.php'; 
?>

<h2>Informasi Umum</h2>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'Database.php';

// Proteksi: Hanya untuk role 'user' yang sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
// Cek role untuk memastikan
// (Tambahkan pengecekan role 'user' seperti pada dashboard perusahaan jika perlu)
$user_id = $_SESSION['user_id'];
$upload_dir = 'uploads/'; // Buat folder bernama 'uploads' di root proyek Anda

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Proses Upload
if (isset($_POST['upload_doc'])) {
    $document_type = $_POST['document_type'];
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $original_filename = basename($_FILES['document']['name']);
        $safe_filename = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", $original_filename);
        $target_file = $upload_dir . $safe_filename;

        if (move_uploaded_file($_FILES['document']['tmp_name'], $target_file)) {
            // Simpan path ke database
            $stmt = $conn->prepare("INSERT INTO user_documents (user_id, document_type, file_path, original_filename) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('isss', $user_id, $document_type, $target_file, $original_filename);
            $stmt->execute();
            $stmt->close();
            $success_msg = "File ".htmlspecialchars($original_filename)." berhasil di-upload.";
        } else {
            $error_msg = "Terjadi error saat memindahkan file.";
        }
    } else {
        $error_msg = "Tidak ada file yang dipilih atau terjadi error upload.";
    }
}

// Proses Hapus
if (isset($_GET['delete_doc_id'])) {
    $doc_id = $_GET['delete_doc_id'];
    // Ambil path file untuk dihapus dari server
    $stmt = $conn->prepare("SELECT file_path FROM user_documents WHERE id=? AND user_id=?");
    $stmt->bind_param('ii', $doc_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($doc = $result->fetch_assoc()) {
        if (file_exists($doc['file_path'])) {
            unlink($doc['file_path']); // Hapus file dari server
        }
        // Hapus record dari database
        $stmt_delete = $conn->prepare("DELETE FROM user_documents WHERE id=?");
        $stmt_delete->bind_param('i', $doc_id);
        $stmt_delete->execute();
        $stmt_delete->close();
    }
    $stmt->close();
    header('Location: profil_upload.php?status=delete_success');
    exit;
}

include 'header.php';
?>
<h2>Kelola Dokumen Lamaran</h2>
<p>Upload dokumen-dokumen penting Anda di sini (CV, Ijazah, dll) untuk mempermudah proses lamaran.</p>

<?php if(isset($success_msg)) echo "<p style='color:green;'>$success_msg</p>"; ?>
<?php if(isset($error_msg)) echo "<p style='color:red;'>$error_msg</p>"; ?>

<form method="post" enctype="multipart/form-data">
    <label for="document_type">Jenis Dokumen:</label>
    <select name="document_type" id="document_type" required>
        <option value="CV">CV (Curriculum Vitae)</option>
        <option value="Ijazah">Ijazah</option>
        <option value="Sertifikat">Sertifikat</option>
        <option value="Portofolio">Portofolio</option>
        <option value="Lainnya">Dokumen Lainnya</option>
    </select>
    <br><br>
    <label for="document">Pilih File:</label>
    <input type="file" name="document" id="document" required>
    <br><br>
    <button type="submit" name="upload_doc">Upload Dokumen</button>
</form>

<hr>
<h3>Dokumen Tersimpan</h3>
<table border="1" cellpadding="5" width="100%">
    <thead>
        <tr><th>Jenis</th><th>Nama File</th><th>Tanggal Upload</th><th>Aksi</th></tr>
    </thead>
    <tbody>
        <?php
        $stmt_docs = $conn->prepare("SELECT id, document_type, original_filename, uploaded_at FROM user_documents WHERE user_id = ?");
        $stmt_docs->bind_param('i', $user_id);
        $stmt_docs->execute();
        $result_docs = $stmt_docs->get_result();
        while($doc = $result_docs->fetch_assoc()):
        ?>
        <tr>
            <td><?= htmlspecialchars($doc['document_type']) ?></td>
            <td><?= htmlspecialchars($doc['original_filename']) ?></td>
            <td><?= htmlspecialchars($doc['uploaded_at']) ?></td>
            <td><a href="profil_upload.php?delete_doc_id=<?= $doc['id'] ?>" onclick="return confirm('Yakin hapus file ini?')">Hapus</a></td>
        </tr>
        <?php endwhile; $stmt_docs->close(); ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>

<?php include 'footer.php'; 