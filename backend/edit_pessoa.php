<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $nome = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING));

    if (!$id || empty($nome)) {
        echo json_encode(['status' => 'error', 'message' => 'ID ou Nome inválidos']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE pessoas SET nome = ? WHERE id = ?");
    $stmt->bind_param("si", $nome, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Pessoa atualizada com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar pessoa: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
}
?>