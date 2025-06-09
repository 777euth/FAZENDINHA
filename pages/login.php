<?php
session_start();
require_once '../config.php';

// Gera token CSRF se ainda não existir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['categoria'] === 'Admin') {
        header("Location: dashboard_admin.php");
    } elseif ($_SESSION['categoria'] === 'Cadastrar') {
        header("Location: dashboard_cadastrar.php");
    } elseif ($_SESSION['categoria'] === 'Fazendeiro') {
        header("Location: dashboard_fazendeiro.php");
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica token CSRF
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $postedToken)) {
        $erro = 'Token CSRF inválido.';
    } else {
        $usuario = trim(filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING));
        $senha = $_POST['senha'];

        if (empty($usuario) || empty($senha)) {
            $erro = "Usuário e senha são obrigatórios.";
        } else {
            $stmt = $conn->prepare("SELECT id, nome, categoria, senha FROM users WHERE usuario = ?");
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($senha, $user['senha'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nome'] = $user['nome'];
                $_SESSION['categoria'] = $user['categoria'];

                if ($user['categoria'] === 'Admin') {
                    header("Location: dashboard_admin.php");
                } elseif ($user['categoria'] === 'Cadastrar') {
                    header("Location: dashboard_cadastrar.php");
                } elseif ($_SESSION['categoria'] === 'Fazendeiro') {
                    header("Location: dashboard_fazendeiro.php");
                }
                exit;
            } else {
                $erro = "Usuário ou senha incorretos.";
            }

            $stmt->close();
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fazendinha - Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <img src="../assets/img/logo.png" alt="Logo Fazendinha" class="logo">
        <h2>Login</h2>
        <?php if (isset($erro)) echo "<p class='error'>" . htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') . "</p>"; ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="text" name="usuario" placeholder="Usuário" required autocomplete="username">
            <input type="password" name="senha" placeholder="Senha" required autocomplete="current-password">
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>
