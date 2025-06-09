<?php
// Carrega variaveis de ambiente a partir de um arquivo .env se existir
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = array_map('trim', explode('=', $line, 2));
        putenv(sprintf('%s=%s', $key, $value));
        $_ENV[$key] = $value;
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'fazendinha';
$username = getenv('DB_USER') ?: 'usuario';
$password = getenv('DB_PASS') ?: 'senha';

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
