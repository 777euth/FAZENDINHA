<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['categoria']) || !in_array($_SESSION['categoria'], ['Fazendeiro', 'Cadastrar'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado']);
    exit;
}

$logins = $conn->query("SELECT id, nome_site, url, login, senha FROM site_logins")->fetch_all(MYSQLI_ASSOC);

if ($logins === false) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao buscar logins']);
} else {
    echo json_encode([
        'status' => 'success',
        'logins' => $logins
    ]);
}

$conn->close();
?>