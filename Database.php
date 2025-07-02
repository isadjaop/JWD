<?php
$server   = "localhost";
$user     = "root";
$password = "";
$db_name  = "db_website";  

$conn = new mysqli($server, $user, $password, $db_name);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
