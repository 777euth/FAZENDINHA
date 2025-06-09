<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['categoria']) || $_SESSION['categoria'] !== 'Admin') {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_site = trim(filter_input(INPUT_POST, 'nome_site', FILTER_SANITIZE_STRING));
    $url = filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL);
    $login = trim(filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING));
    $senha = trim(filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING));

    // Validação básica
    if (empty($nome_site) || !$url || empty($login) || empty($senha)) {
        echo json_encode(['status' => 'error', 'message' => 'Todos os campos são obrigatórios e a URL deve ser válida']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO site_logins (nome_site, url, login, senha) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome_site, $url, $login, $senha);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Login cadastrado com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao cadastrar login: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
}
?>