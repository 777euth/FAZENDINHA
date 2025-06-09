<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['categoria']) || $_SESSION['categoria'] !== 'Admin') {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $nome_site = trim(filter_input(INPUT_POST, 'nome_site', FILTER_SANITIZE_STRING));
    $url = filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL);
    $login = trim(filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING));
    $senha = trim(filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING));

    // Validação
    if (!$id || empty($nome_site) || !$url || empty($login) || empty($senha)) {
        echo json_encode(['status' => 'error', 'message' => 'Todos os campos são obrigatórios e a URL deve ser válida']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE site_logins SET nome_site = ?, url = ?, login = ?, senha = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $nome_site, $url, $login, $senha, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Login atualizado com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar login: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
}
?>