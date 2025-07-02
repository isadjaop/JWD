<?php include 'Database.php'; 
include 'header.php'; 
?>

<h2>Contact Us</h2>
<form method="post" action="send_message.php">
  <input name="name" required placeholder="Nama Anda">
  <input name="email" type="email"   required placeholder="Email Anda">
  <textarea name="message" required placeholder="Pesan..."></textarea>
  <button type="submit">Kirim</button>
</form>

<?php include 'footer.php'; ?>