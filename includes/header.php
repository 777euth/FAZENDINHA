<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fazendinha - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="header-top">
            <img src="../assets/img/logo.png" alt="Logo Fazendinha" class="logo">
            <div class="user-info">
                <p><strong>Usuário:</strong> <?php echo htmlspecialchars($_SESSION['nome'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Categoria:</strong> <?php echo htmlspecialchars($_SESSION['categoria'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Hora:</strong> <span id="current-time"></span></p>
                <a href="../logout.php" class="logout-btn">Sair</a>
            </div>
        </div>
        <div id="dashboard-stats">
            <p>Perfis Criados: <span id="perfil-criado">0</span></p>
            <p>Google Aprovado: <span id="google-aprovado">0</span></p>
            <p>Campanhas Ativas: <span id="campanhas">0</span></p>
            <p>Contas Suspensas: <span id="conta-suspensa">0</span></p>
            <p>Empresas Disponíveis: <span id="empresas-disponiveis">0</span></p>
            <p>Pessoas Disponíveis: <span id="pessoas-disponiveis">0</span></p>
            <p>Emails Disponíveis: <span id="emails-disponiveis">0</span></p>
        </div>
    </header>
    <div class="container">