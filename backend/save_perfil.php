<?php
header('Content-Type: application/json');
session_start();
require_once '../config.php';

if (!isset($_SESSION['categoria']) || !in_array($_SESSION['categoria'], ['Admin', 'Cadastrar', 'Fazendeiro'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acesso não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresa_id = filter_input(INPUT_POST, 'empresa_id', FILTER_VALIDATE_INT);
    $pessoa_id = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
    $email_id = filter_input(INPUT_POST, 'email_id', FILTER_VALIDATE_INT);
    $opcao_id = filter_input(INPUT_POST, 'opcao_id', FILTER_VALIDATE_INT);
    $perfil_criado = 1;
    $google_aprovado = 'Pendente';
    $campanhas = 'Pendente';
    $conta_suspensa = 'Não';
    $estado = 'Aguardando';
    $status = 'Ativa';
    $objetivo = 'White';

    $errors = [];
    if (!$empresa_id) $errors[] = 'Empresa é obrigatória';
    if (!$pessoa_id) $errors[] = 'Pessoa é obrigatória';
    if (!$email_id) $errors[] = 'Email é obrigatório';
    if (!$opcao_id) $errors[] = 'Pasta GoLogin é obrigatória';

    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'message' => implode('; ', $errors)]);
        exit;
    }

    // Função para extrair o nome do perfil a partir do site_original
    function extractProfileName($site_original) {
        if (empty($site_original)) {
            return '';
        }
        // Remove o protocolo (https:// ou http://) e o "www."
        $site = preg_replace('#^https?://#', '', $site_original);
        $site = preg_replace('#^www\.#', '', $site);
        // Remove tudo após o primeiro ponto (ex.: .com, .com.br)
        $site = preg_replace('#\..*$#', '', $site);
        return $site;
    }

    $stmt = $conn->prepare("SELECT id, site_original FROM empresas WHERE id = ?");
    $stmt->bind_param("i", $empresa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Empresa não encontrada']);
        $stmt->close();
        exit;
    }
    $empresa = $result->fetch_assoc();
    $nome_perfil = extractProfileName($empresa['site_original']);
    $stmt->close();

    $stmt = $conn->prepare("SELECT id FROM pessoas WHERE id = ?");
    $stmt->bind_param("i", $pessoa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Pessoa não encontrada']);
        $stmt->close();
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT id FROM emails WHERE id = ?");
    $stmt->bind_param("i", $email_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email não encontrado']);
        $stmt->close();
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT id FROM opcoes WHERE id = ?");
    $stmt->bind_param("i", $opcao_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Opção não encontrada']);
        $stmt->close();
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO perfis (empresa_id, pessoa_id, email_id, opcao_id, nome_perfil, perfil_criado, google_aprovado, campanhas, conta_suspensa, estado, status, objetivo, email_profissional, novo_site, numero_google_ads, codigo_ativacao_g2, campo_adicional_1, campo_adicional_2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiissssssssssssss", $empresa_id, $pessoa_id, $email_id, $opcao_id, $nome_perfil, $perfil_criado, $google_aprovado, $campanhas, $conta_suspensa, $estado, $status, $objetivo, $null, $null, $null, $null, $null, $null);

    $null = null;

    if ($stmt->execute()) {
        $perfil_id = $conn->insert_id;
        $stmt_tarefa = $conn->prepare("INSERT INTO tarefas (perfil_id, descricao) VALUES (?, ?)");
        $descricao = "Perfil criado em " . date('Y-m-d H:i:s');
        $stmt_tarefa->bind_param("is", $perfil_id, $descricao);
        $stmt_tarefa->execute();
        $stmt_tarefa->close();
        echo json_encode(['status' => 'success', 'message' => 'Perfil criado com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao criar perfil: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
}
?>