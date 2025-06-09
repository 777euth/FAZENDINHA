<?php
header('Content-Type: application/json');
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID inválido']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, empresa, cnpj, site_original, telefone, endereco, cidade, estado, cep, pdf_cnpj, pdf_susep FROM empresas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $empresa = $result->fetch_assoc();
    $stmt->close();

    if ($empresa) {
        echo json_encode(['success' => true, 'empresa' => $empresa]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Empresa não encontrada']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Requisição inválida']);
}

$conn->close();
?>