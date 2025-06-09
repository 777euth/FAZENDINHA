<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $senha = trim(filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING));
    $email_rec = filter_input(INPUT_POST, 'email_rec', FILTER_VALIDATE_EMAIL);

    if (!$email || empty($senha) || !$email_rec) {
        echo json_encode(['status' => 'error', 'message' => 'Campos inválidos']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO emails (email, senha, email_rec) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $senha, $email_rec);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Email cadastrado com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao cadastrar email: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
}
?>