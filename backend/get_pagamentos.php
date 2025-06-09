<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['categoria']) || $_SESSION['categoria'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

$apenasProximos = isset($_GET['proximos']);
$query = "SELECT * FROM pagamentos";
if ($apenasProximos) {
    $query .= " WHERE data_vencimento >= CURDATE() ORDER BY data_vencimento ASC";
} else {
    $query .= " ORDER BY data_vencimento DESC";
}
$result = $conn->query($query);
$pagamentos = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

echo json_encode(['success' => true, 'pagamentos' => $pagamentos]);
$conn->close();
?>
