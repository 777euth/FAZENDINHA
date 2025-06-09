<?php
session_start();
require_once '../config.php';

if ($_SESSION['categoria'] !== 'Fazendeiro') {
    header("Location: ../pages/login.php");
    exit;
}

$eventos = $conn->query("SELECT id, nome, passos, campanhas, conta_suspensa, google_aprovado, estado, status, objetivo, quantidade_dias, campos_exibir, campos_personalizados, campos_perfil_adicionar, exibir_arquivos FROM eventos")->fetch_all(MYSQLI_ASSOC);
$perfis = $conn->query("SELECT p.id, p.nome_perfil, p.empresa_id, p.pessoa_id, p.email_id, p.perfil_criado, p.google_aprovado, p.campanhas, p.conta_suspensa, p.estado, p.status, p.objetivo, p.email_profissional, p.novo_site, p.numero_google_ads, p.codigo_ativacao_g2, p.campo_adicional_1, p.campo_adicional_2, e.empresa AS empresa_nome, e.cnpj, e.site_original, e.telefone, e.endereco, e.cidade, e.estado AS empresa_estado, e.cep, e.pdf_cnpj, e.pdf_susep, pe.nome AS pessoa_nome, pe.rg_frente, pe.rg_tras, em.email AS email_nome, em.senha AS email_senha, em.email_rec AS email_rec FROM perfis p LEFT JOIN empresas e ON p.empresa_id = e.id LEFT JOIN pessoas pe ON p.pessoa_id = pe.id LEFT JOIN emails em ON p.email_id = em.id WHERE p.perfil_criado = 1")->fetch_all(MYSQLI_ASSOC);

if ($eventos === false || $perfis === false) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao buscar dados']);
    $conn->close();
    exit;
}

echo json_encode([
    'status' => 'success',
    'eventos' => $eventos,
    'perfis' => $perfis
]);

$conn->close();
?>