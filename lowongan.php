<?php
include 'Database.php';
include 'header.php';

$sql = "SELECT * FROM jobs WHERE expiry_date >= CURDATE() ORDER BY expiry_date ASC";
$res = $conn->query($sql);
if (!$res) {
    echo '<p>Error query: ' . $conn->error . '</p>';
} else {
    echo '<h2>Daftar Lowongan</h2>';
    while ($job = $res->fetch_assoc()) {
        ?>
        <div class="job">
          <h3><?=htmlspecialchars($job['position'])?></h3>
          <p><strong>Perusahaan:</strong> <?=htmlspecialchars($job['company_name'])?></p>
          <p><strong>Lokasi:</strong> <?=htmlspecialchars($job['location'])?></p>
          <p><strong>Deskripsi:</strong> <?=nl2br(htmlspecialchars($job['description']))?></p>
          <p><strong>Kualifikasi:</strong> <?=nl2br(htmlspecialchars($job['qualifications']))?></p>
          <p><strong>Expired:</strong> <?=htmlspecialchars($job['expiry_date'])?></p>
          <p><strong>Kontak:</strong> <?=htmlspecialchars($job['contact_info'])?></p>
        </div>
        <hr>
        <?php
    }
}
include 'footer.php';
?>