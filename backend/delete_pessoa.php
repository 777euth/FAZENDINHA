<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['categoria']) || !in_array($_SESSION['categoria'], ['Admin', 'Cadastrar'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'ID da pessoa é obrigatório']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id FROM pessoas WHERE id = ? AND id NOT IN (SELECT pessoa_id FROM perfis)");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Pessoa não encontrada ou está associada a um perfil']);
        $stmt->close();
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM pessoas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Pessoa excluída com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir pessoa: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
}
?>