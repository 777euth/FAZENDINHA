<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $senha = trim(filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING));
    $email_rec = filter_input(INPUT_POST, 'email_rec', FILTER_VALIDATE_EMAIL);

    if (!$id || !$email || empty($senha) || !$email_rec) {
        echo json_encode(['status' => 'error', 'message' => 'Campos inválidos']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE emails SET email = ?, senha = ?, email_rec = ? WHERE id = ?");
    $stmt->bind_param("sssi", $email, $senha, $email_rec, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Email atualizado com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar email: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
}
?>