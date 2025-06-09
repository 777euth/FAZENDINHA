<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['categoria']) || $_SESSION['categoria'] !== 'Admin') {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = trim(filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING));
    $valor = filter_input(INPUT_POST, 'valor', FILTER_VALIDATE_FLOAT);
    $data_vencimento = trim(filter_input(INPUT_POST, 'data_vencimento', FILTER_SANITIZE_STRING));
    $data_pagamento = trim(filter_input(INPUT_POST, 'data_pagamento', FILTER_SANITIZE_STRING));
    $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));

    if (empty($descricao) || $valor === false || empty($data_vencimento) || empty($status)) {
        echo json_encode(['status' => 'error', 'message' => 'Campos obrigat\xc3\xb3rios n\xc3\xa3o preenchidos']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO pagamentos (descricao, valor, data_vencimento, data_pagamento, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sdsss", $descricao, $valor, $data_vencimento, $data_pagamento, $status);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Pagamento cadastrado com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao cadastrar pagamento: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'M\xc3\xa9todo n\xc3\xa3o permitido']);
}
?>
