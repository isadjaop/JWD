<?php
include 'Database.php';
include 'header.php';

// kegunaan variabel error untuk menghindari notice
$error = '';

$sql = "SELECT 
            j.posisi, j.lokasi, j.deskripsi, j.kualifikasi, j.expiry_date, j.kontak,
            p.company_name, p.description AS company_description, p.address AS company_address
        FROM jobs j
        JOIN users u ON j.dibuat_oleh = u.ID
        JOIN perusahaan p ON u.ID = p.user_id
        WHERE j.expiry_date >= CURDATE() 
        ORDER BY j.expiry_date ASC";

$res = $conn->query($sql);

if (!$res) {
    // Menampilkan error query yang sebenarnya untuk debugging
    echo '<p>Error query: ' . $conn->error . '</p>';
} else {
    echo '<h2>Daftar Lowongan</h2>';
    while ($job = $res->fetch_assoc()) {
        ?>
        <div class="job">
          <h3><?=htmlspecialchars($job['posisi'])?></h3>
          <p><strong>Perusahaan:</strong> <?=htmlspecialchars($job['company_name'])?></p>
          <p><strong>Lokasi:</strong> <?=htmlspecialchars($job['lokasi'])?></p>
          <p><strong>Deskripsi Pekerjaan:</strong> <?=nl2br(htmlspecialchars($job['deskripsi']))?></p>
          <p><strong>Kualifikasi:</strong> <?=nl2br(htmlspecialchars($job['kualifikasi']))?></p>
          <p><strong>Batas Waktu:</strong> <?=htmlspecialchars($job['expiry_date'])?></p>
          <p><strong>Kontak Pendaftaran:</strong> <?=htmlspecialchars($job['kontak'])?></p>
          
          <details>
            <summary><strong>Lihat Profil & Penawaran Perusahaan</strong></summary>
            <div class="company-profile">
                <h4>Tentang <?=htmlspecialchars($job['company_name'])?></h4>
                <p><?=nl2br(htmlspecialchars($job['company_description']))?></p>
                <p><strong>Alamat:</strong> <?=htmlspecialchars($job['company_address'])?></p>
                <p><strong>Penawaran (Contoh):</strong> Gaji kompetitif, Asuransi Kesehatan, Lingkungan kerja fleksibel.</p>
            </div>
          </details>
        </div>
        <hr>
        <?php
    }
}
include 'footer.php';
?>