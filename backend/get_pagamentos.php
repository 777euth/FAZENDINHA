<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['categoria']) || $_SESSION['categoria'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

$result = $conn->query("SELECT * FROM pagamentos ORDER BY data_vencimento DESC");
$pagamentos = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

echo json_encode(['success' => true, 'pagamentos' => $pagamentos]);
$conn->close();
?>
