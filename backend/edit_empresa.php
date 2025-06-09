<?php
header('Content-Type: application/json');
session_start();
require_once '../config.php';

if (!isset($_SESSION['categoria']) || !in_array($_SESSION['categoria'], ['Admin', 'Cadastrar'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $empresa = trim(filter_input(INPUT_POST, 'empresa', FILTER_SANITIZE_STRING));
    $cnpj = trim(filter_input(INPUT_POST, 'cnpj', FILTER_SANITIZE_STRING));
    $site_original = filter_input(INPUT_POST, 'site_original', FILTER_VALIDATE_URL);
    $telefone = trim(filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING));
    $endereco = trim(filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_STRING));
    $cidade = trim(filter_input(INPUT_POST, 'cidade', FILTER_SANITIZE_STRING));
    $estado = trim(filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING));
    $cep = trim(filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_STRING));

    $errors = [];
    if (!$id) {
        $errors[] = 'ID é obrigatório';
    }
    if (empty($empresa)) {
        $errors[] = 'Nome da Empresa é obrigatório';
    }
    if (empty($cnpj)) {
        $errors[] = 'CNPJ é obrigatório';
    }

    // Remove pontos, traços e quaisquer caracteres não numéricos do CNPJ
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

    // Validação do CNPJ (apenas verifica se tem 14 dígitos após a limpeza)
    if (strlen($cnpj) !== 14 || !is_numeric($cnpj)) {
        $errors[] = 'CNPJ inválido (deve conter 14 dígitos numéricos)';
    }

    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'message' => implode('; ', $errors)]);
        exit;
    }

    // Diretório de upload (usando letras minúsculas)
    $upload_dir = 'uploads/empresas/';
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

    $stmt = $conn->prepare("SELECT pdf_cnpj, pdf_susep FROM empresas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current = $result->fetch_assoc();
    $stmt->close();

    $pdf_cnpj_path = $current['pdf_cnpj'] ?? '';
    if (isset($_FILES['pdf_cnpj']) && $_FILES['pdf_cnpj']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['pdf_cnpj']['type'] !== 'application/pdf') {
            echo json_encode(['status' => 'error', 'message' => 'Formato de arquivo inválido para PDF CNPJ']);
            exit;
        }
        $pdf_cnpj_name = 'cnpj_' . str_replace(' ', '_', $empresa) . '_' . uniqid() . '.pdf';
        $pdf_cnpj_path = $upload_dir . $pdf_cnpj_name;
        if (file_exists($pdf_cnpj_path)) {
            echo json_encode(['status' => 'error', 'message' => 'Erro: O arquivo PDF CNPJ já existe.']);
            exit;
        }
        if (!move_uploaded_file($_FILES['pdf_cnpj']['tmp_name'], $pdf_cnpj_path)) {
            $error = error_get_last();
            error_log("Erro ao mover PDF CNPJ: " . ($error['message'] ?? 'Desconhecido'));
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar PDF CNPJ: ' . ($error['message'] ?? 'Desconhecido')]);
            exit;
        }
        $pdf_cnpj_path = 'backend/' . $pdf_cnpj_path;
    }

    $pdf_susep_path = $current['pdf_susep'] ?? '';
    if (isset($_FILES['pdf_susep']) && $_FILES['pdf_susep']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['pdf_susep']['type'] !== 'application/pdf') {
            echo json_encode(['status' => 'error', 'message' => 'Formato de arquivo inválido para PDF SUSEP']);
            exit;
        }
        $pdf_susep_name = 'susep_' . str_replace(' ', '_', $empresa) . '_' . uniqid() . '.pdf';
        $pdf_susep_path = $upload_dir . $pdf_susep_name;
        if (file_exists($pdf_susep_path)) {
            echo json_encode(['status' => 'error', 'message' => 'Erro: O arquivo PDF SUSEP já existe.']);
            exit;
        }
        if (!move_uploaded_file($_FILES['pdf_susep']['tmp_name'], $pdf_susep_path)) {
            $error = error_get_last();
            error_log("Erro ao mover PDF SUSEP: " . ($error['message'] ?? 'Desconhecido'));
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar PDF SUSEP: ' . ($error['message'] ?? 'Desconhecido')]);
            exit;
        }
        $pdf_susep_path = 'backend/' . $pdf_susep_path;
    }

    $stmt = $conn->prepare("UPDATE empresas SET empresa = ?, cnpj = ?, site_original = ?, telefone = ?, endereco = ?, cidade = ?, estado = ?, cep = ?, pdf_cnpj = ?, pdf_susep = ? WHERE id = ?");
    $stmt->bind_param("ssssssssssi", $empresa, $cnpj, $site_original, $telefone, $endereco, $cidade, $estado, $cep, $pdf_cnpj_path, $pdf_susep_path, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Empresa atualizada com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar empresa: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
}
?>