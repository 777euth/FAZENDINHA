<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['categoria']) || $_SESSION['categoria'] !== 'Fazendeiro') {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $google = trim(filter_input(INPUT_POST, 'google_aprovado', FILTER_SANITIZE_STRING));
    $campanhas = trim(filter_input(INPUT_POST, 'campanhas', FILTER_SANITIZE_STRING));
    $suspensa = trim(filter_input(INPUT_POST, 'conta_suspensa', FILTER_SANITIZE_STRING));
    $estado = trim(filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING));
    $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));
    $objetivo = trim(filter_input(INPUT_POST, 'objetivo', FILTER_SANITIZE_STRING));

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE perfis SET google_aprovado=?, campanhas=?, conta_suspensa=?, estado=?, status=?, objetivo=? WHERE id=?");
    $stmt->bind_param("ssssssi", $google, $campanhas, $suspensa, $estado, $status, $objetivo, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Perfil atualizado com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar perfil: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
}
?>

