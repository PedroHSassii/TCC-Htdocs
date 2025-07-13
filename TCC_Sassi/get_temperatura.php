<?php
session_start();
include 'db.php'; // Conexão com o banco de dados

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT temperatura FROM historico WHERE usuario_id = ? ORDER BY data_hora DESC LIMIT 1");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$stmt->bind_result($temperatura);
$stmt->fetch();
$stmt->close();

echo json_encode(['temperatura' => $temperatura]);
?>
