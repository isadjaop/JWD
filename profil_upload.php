<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'Database.php';

// Proteksi: Hanya untuk role 'user' yang sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$upload_dir = 'uploads/'; // PENTING: Buat folder bernama 'uploads' di direktori yang sama dengan file ini

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_doc'])) {
    $document_type = $_POST['document_type'];
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $original_filename = basename($_FILES['document']['name']);
        $safe_filename = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", $original_filename);
        $target_file = $upload_dir . $safe_filename;

        if (move_uploaded_file($_FILES['document']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO user_documents (user_id, document_type, file_path, original_filename) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('isss', $user_id, $document_type, $target_file, $original_filename);
            $stmt->execute();
            $stmt->close();
            $success_msg = "File ".htmlspecialchars($original_filename)." berhasil di-upload.";
        } else {
            $error_msg = "Terjadi error saat memindahkan file ke direktori.";
        }
    } else {
        $error_msg = "Tidak ada file yang dipilih atau terjadi error upload: " . ($_FILES['document']['error'] ?? 'Unknown Error');
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete_doc' && isset($_GET['id'])) {
    $doc_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    $stmt = $conn->prepare("SELECT file_path FROM user_documents WHERE id=? AND user_id=?");
    $stmt->bind_param('ii', $doc_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($doc = $result->fetch_assoc()) {        if (file_exists($doc['file_path'])) {
            unlink($doc['file_path']);
        }
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

<form method="post" enctype="multipart/form-data" style="margin-bottom: 2rem; padding: 1rem; border: 1px solid #ccc;">
    <label for="document_type"><strong>Jenis Dokumen:</strong></label><br>
    <select name="document_type" id="document_type" required>
        <option value="CV">CV (Curriculum Vitae)</option>
        <option value="Ijazah">Ijazah</option>
        <option value="Sertifikat">Sertifikat</option>
        <option value="Portofolio">Portofolio</option>
        <option value="Lainya">Dokumen Lainnya</option>
    </select>
    <br><br>
    <label for="document"><strong>Pilih File:</strong></label><br>
    <input type="file" name="document" id="document" required>
    <br><br>
    <button type="submit" name="upload_doc">Upload Dokumen</button>
</form>

<hr>
<h3>Dokumen Tersimpan</h3>
<table border="1" cellpadding="5" width="100%">
    <thead>
        <tr><th>Jenis Dokumen</th><th>Nama File</th><th>Tanggal Upload</th><th>Aksi</th></tr>
    </thead>
    <tbody>
        <?php
        $stmt_docs = $conn->prepare("SELECT id, document_type, original_filename, uploaded_at FROM user_documents WHERE user_id = ? ORDER BY uploaded_at DESC");
        $stmt_docs->bind_param('i', $user_id);
        $stmt_docs->execute();
        $result_docs = $stmt_docs->get_result();
        if ($result_docs->num_rows > 0):
            while($doc = $result_docs->fetch_assoc()):
            ?>
            <tr>
                <td><?= htmlspecialchars($doc['document_type']) ?></td>
                <td><a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank"><?= htmlspecialchars($doc['original_filename']) ?></a></td>
                <td><?= htmlspecialchars($doc['uploaded_at']) ?></td>
                <td><a href="?action=delete_doc&id=<?= $doc['id'] ?>" onclick="return confirm('Yakin ingin menghapus file ini?')" style="color:red;">Hapus</a></td>
            </tr>
            <?php endwhile;
        else: ?>
             <tr><td colspan="4" style="text-align:center;">Anda belum meng-upload dokumen apapun.</td></tr>
        <?php endif;
        $stmt_docs->close(); ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>