<?php
include 'Database.php'; 
include 'header.php'; 
?>

<h2>Contact Us</h2>
<div class="company-info">
  <h3>Tentang PT. Lowongan Kerja</h3>
  <p>
    PT. Contoh Solusi adalah penyedia layanan kerja berbasis IT terkemuka di Indonesia, bla bla bla .<br>
    Alamat: Jl. Merdeka No. 45, Surabaya<br>
    Call Center: <strong>031-1234 567</strong> (Senin–Jumat, 09.00–17.00)
<form method="post" action="send_message.php">
  <input name="name" required placeholder="Nama Anda">
  <input name="email" type="email"   required placeholder="Email Anda">
  <textarea name="message" required placeholder="Pesan..."></textarea>
  <button type="submit">Kirim</button>
</form>

<?php include 'footer.php'; ?>