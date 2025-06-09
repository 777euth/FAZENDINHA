<?php
$host = 'localhost';
$dbname = 'fazendinha';
$username = 'th2k';
$password = 'Passuordi123';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Erro de conexão: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>