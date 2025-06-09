<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING));

    if (empty($nome)) {
        echo json_encode(['status' => 'error', 'message' => 'O campo Nome é obrigatório']);
        exit;
    }

    // Diretório de upload (usando letras minúsculas)
    $upload_dir = 'uploads/pessoas/';
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            error_log("Erro ao criar diretório $upload_dir");
            echo json_encode(['status' => 'error', 'message' => 'Erro ao criar diretório de upload']);
            exit;
        }
    }

    // Verificar permissões do diretório
    if (!is_writable($upload_dir)) {
        error_log("Diretório $upload_dir não tem permissões de escrita");
        echo json_encode(['status' => 'error', 'message' => 'Diretório de upload não tem permissões de escrita']);
        exit;
    }

    // Processar rg_frente
    $rg_frente = null;
    if (isset($_FILES['rg_frente']) && $_FILES['rg_frente']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png'];
        if (!in_array($_FILES['rg_frente']['type'], $allowed_types)) {
            echo json_encode(['status' => 'error', 'message' => 'Formato de arquivo inválido para RG Frente (apenas JPG/PNG)']);
            exit;
        }
        $rg_frente_name = uniqid() . '_' . basename($_FILES['rg_frente']['name']);
        $rg_frente = $upload_dir . $rg_frente_name;
        if (file_exists($rg_frente)) {
            echo json_encode(['status' => 'error', 'message' => 'Erro: O arquivo RG Frente já existe.']);
            exit;
        }
        if (!move_uploaded_file($_FILES['rg_frente']['tmp_name'], $rg_frente)) {
            $error = error_get_last();
            error_log("Erro ao mover RG Frente: " . ($error['message'] ?? 'Desconhecido'));
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar RG Frente: ' . ($error['message'] ?? 'Desconhecido')]);
            exit;
        }
    } else {
        $upload_error = $_FILES['rg_frente']['error'] ?? 'Desconhecido';
        error_log("Erro no upload do RG Frente: Código $upload_error");
        echo json_encode(['status' => 'error', 'message' => 'O upload do RG Frente é obrigatório. Código de erro: ' . $upload_error]);
        exit;
    }

    // Processar rg_tras
    $rg_tras = null;
    if (isset($_FILES['rg_tras']) && $_FILES['rg_tras']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png'];
        if (!in_array($_FILES['rg_tras']['type'], $allowed_types)) {
            echo json_encode(['status' => 'error', 'message' => 'Formato de arquivo inválido para RG Trás (apenas JPG/PNG)']);
            exit;
        }
        $rg_tras_name = uniqid() . '_' . basename($_FILES['rg_tras']['name']);
        $rg_tras = $upload_dir . $rg_tras_name;
        if (file_exists($rg_tras)) {
            echo json_encode(['status' => 'error', 'message' => 'Erro: O arquivo RG Trás já existe.']);
            exit;
        }
        if (!move_uploaded_file($_FILES['rg_tras']['tmp_name'], $rg_tras)) {
            $error = error_get_last();
            error_log("Erro ao mover RG Trás: " . ($error['message'] ?? 'Desconhecido'));
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar RG Trás: ' . ($error['message'] ?? 'Desconhecido')]);
            exit;
        }
    } else {
        $upload_error = $_FILES['rg_tras']['error'] ?? 'Desconhecido';
        error_log("Erro no upload do RG Trás: Código $upload_error");
        echo json_encode(['status' => 'error', 'message' => 'O upload do RG Trás é obrigatório. Código de erro: ' . $upload_error]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO pessoas (nome, rg_frente, rg_tras) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nome, $rg_frente, $rg_tras);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Pessoa cadastrada com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao cadastrar pessoa: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
}
?>