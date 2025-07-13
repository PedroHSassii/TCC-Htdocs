<?php
session_start();
include 'db.php'; // Conexão com o banco de dados

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verifica se o usuário é admin
$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT is_admin FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($is_admin);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <title>Menu - Controle de Ar-Condicionado</title>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center">Menu de Controle de Ar-Condicionado</h1>

        <!-- Botões de Ação -->
        <div class="text-center mt-4">
            <a href="controle.php" class="btn btn-primary btn-lg btn-block mb-3">Controlar Ambiente</a>
            <?php if ($is_admin): ?>
                <a href="cadastrar_usuario.php" class="btn btn-secondary btn-lg btn-block mb-3">Cadastrar Usuário</a>
                <a href="cadastrar_ambiente.php" class="btn btn-secondary btn-lg btn-block mb-3">Cadastrar Ambiente</a>
                <a href="cadastrar_predio.php" class="btn btn-secondary btn-lg btn-block mb-3">Cadastrar Predio</a>
                <a href="cadastro_ir_code.php" class="btn btn-secondary btn-lg btn-block mb-3">Mapear Controle Remoto</a>
                <a href="historico.php" class="btn btn-secondary btn-lg btn-block mb-3">Ver Histórico</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-danger btn-lg btn-block">Logoff</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
