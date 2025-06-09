<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

function sanitizeFileName($string) {
    $string = preg_replace('/[^A-Za-z0-9\-]/', '_', $string);
    return $string;
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

// Diretórios de upload (usando letras minúsculas)
$uploadDirEmpresas = 'uploads/empresas/';
$uploadDirPessoas = 'uploads/pessoas/';
if (!file_exists($uploadDirEmpresas)) {
    mkdir($uploadDirEmpresas, 0777, true);
}
if (!file_exists($uploadDirPessoas)) {
    mkdir($uploadDirPessoas, 0777, true);
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawData = file_get_contents('php://input');
    if (!empty($rawData)) {
        $input = json_decode($rawData, true);
        if (isset($input['action'])) {
            $action = $input['action'];
        }
    }

    if (!$action && isset($_POST['action'])) {
        $action = $_POST['action'];
    }
}

if ($action === 'add_perfil') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['data'])) {
        echo json_encode(['success' => false, 'error' => 'Dados do perfil não fornecidos']);
        exit;
    }

    $data = $input['data'];
    $empresa_id = isset($data['empresa_id']) ? (int)$data['empresa_id'] : null;
    $pessoa_id = isset($data['pessoa_id']) ? (int)$data['pessoa_id'] : null;
    $email_id = isset($data['email_id']) ? (int)$data['email_id'] : null;
    $opcao_id = isset($data['opcao_id']) ? (int)$data['opcao_id'] : null;
    $perfil_criado = isset($data['perfil_criado']) ? (int)$data['perfil_criado'] : 1;
    $google_aprovado = isset($data['google_aprovado']) ? $data['google_aprovado'] : 'Pendente';
    $campanhas = isset($data['campanhas']) ? $data['campanhas'] : 'Pendente';
    $conta_suspensa = isset($data['conta_suspensa']) ? $data['conta_suspensa'] : 'Não';
    $estado = isset($data['estado']) ? $data['estado'] : 'Aguardando';
    $status = isset($data['status']) ? $data['status'] : 'Ativa';
    $objetivo = isset($data['objetivo']) ? $data['objetivo'] : 'White';

    if ($empresa_id) {
        $stmt = $conn->prepare("SELECT id, site_original FROM empresas WHERE id = ?");
        $stmt->bind_param("i", $empresa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'Empresa não encontrada']);
            exit;
        }
        $empresa = $result->fetch_assoc();
        $nome_perfil = extractProfileName($empresa['site_original']);
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'ID da empresa é obrigatório']);
        exit;
    }

    if ($pessoa_id) {
        $stmt = $conn->prepare("SELECT id FROM pessoas WHERE id = ?");
        $stmt->bind_param("i", $pessoa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'Pessoa não encontrada']);
            exit;
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'ID da pessoa é obrigatório']);
        exit;
    }

    if ($email_id) {
        $stmt = $conn->prepare("SELECT id FROM emails WHERE id = ?");
        $stmt->bind_param("i", $email_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'Email não encontrado']);
            exit;
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'ID do email é obrigatório']);
        exit;
    }

    if ($opcao_id) {
        $stmt = $conn->prepare("SELECT id FROM opcoes WHERE id = ?");
        $stmt->bind_param("i", $opcao_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'Opção não encontrada']);
            exit;
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'ID da opção (pasta GoLogin) é obrigatório']);
        exit;
    }

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
        echo json_encode(['success' => true, 'message' => 'Perfil criado com sucesso']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao inserir perfil: ' . $stmt->error]);
    }
    $stmt->close();
} elseif ($action === 'get_all') {
    $empresas = $conn->query("SELECT * FROM empresas")->fetch_all(MYSQLI_ASSOC);
    $pessoas = $conn->query("SELECT * FROM pessoas")->fetch_all(MYSQLI_ASSOC);
    $emails = $conn->query("SELECT * FROM emails")->fetch_all(MYSQLI_ASSOC);
    $opcoes = $conn->query("SELECT * FROM opcoes")->fetch_all(MYSQLI_ASSOC);
    $perfis = $conn->query("SELECT p.*, e.empresa, em.email FROM perfis p LEFT JOIN empresas e ON p.empresa_id = e.id LEFT JOIN emails em ON p.email_id = em.id")->fetch_all(MYSQLI_ASSOC);
    $site_logins = $conn->query("SELECT * FROM site_logins")->fetch_all(MYSQLI_ASSOC);

    foreach ($perfis as &$perfil) {
        $stmt = $conn->prepare("SELECT descricao FROM tarefas WHERE perfil_id = ? ORDER BY data_hora DESC LIMIT 1");
        $stmt->bind_param("i", $perfil['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $ultima_tarefa = $result->fetch_assoc();
        $perfil['ultimo_evento'] = $ultima_tarefa ? $ultima_tarefa['descricao'] : '-';
        $stmt->close();
    }

    $empresas_usadas = array_unique(array_column($perfis, 'empresa_id'));
    $pessoas_usadas = array_unique(array_column($perfis, 'pessoa_id'));
    $emails_usados = array_unique(array_filter(array_column($perfis, 'email_id')));

    $empresas_disponiveis = count(array_filter($empresas, function($empresa) use ($empresas_usadas) {
        return !in_array($empresa['id'], $empresas_usadas);
    }));
    $pessoas_disponiveis = count(array_filter($pessoas, function($pessoa) use ($pessoas_usadas) {
        return !in_array($pessoa['id'], $pessoas_usadas);
    }));
    $emails_disponiveis = count(array_filter($emails, function($email) use ($emails_usados) {
        return !in_array($email['id'], $emails_usados);
    }));

    echo json_encode([
        'empresas' => $empresas,
        'pessoas' => $pessoas,
        'emails' => $emails,
        'opcoes' => $opcoes,
        'perfis' => $perfis,
        'site_logins' => $site_logins,
        'empresas_disponiveis' => $empresas_disponiveis,
        'pessoas_disponiveis' => $pessoas_disponiveis,
        'emails_disponiveis' => $emails_disponiveis
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Ação inválida']);
}

$conn->close();
?>