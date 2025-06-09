<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estado = trim(filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING));
    $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));
    $objetivo = trim(filter_input(INPUT_POST, 'objetivo', FILTER_SANITIZE_STRING));
    $pasta_gologin = trim(filter_input(INPUT_POST, 'pasta_gologin', FILTER_SANITIZE_STRING));
    $evento = trim(filter_input(INPUT_POST, 'evento', FILTER_SANITIZE_STRING));

    $stmt = $conn->prepare("INSERT INTO opcoes (estado, status, objetivo, pasta_gologin, evento) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $estado, $status, $objetivo, $pasta_gologin, $evento);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Opções cadastradas com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao cadastrar opções: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
}
?>