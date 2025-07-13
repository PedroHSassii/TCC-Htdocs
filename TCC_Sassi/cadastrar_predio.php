<?php
session_start();
include 'db.php'; // Conexão com o banco de dados

// Verifica se o usuário está logado e é admin
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

if (!$is_admin) {
    echo "Acesso negado. Apenas administradores podem cadastrar prédios.";
    exit();
}

// Obter a lista de usuários administradores
$stmt = $conn->prepare("SELECT id, nome FROM usuarios WHERE is_admin = 1");
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_predio = $_POST['nome_predio'];
    $responsavel_id = $_POST['responsavel_id']; // ID do responsável

    // Prepara a consulta para inserir o novo prédio
    $stmt = $conn->prepare("INSERT INTO predios (nome, responsavel_id) VALUES (?, ?)");
    $stmt->bind_param("si", $nome_predio, $responsavel_id);
    
    if ($stmt->execute()) {
        echo "Prédio cadastrado com sucesso!";
    } else {
        echo "Erro ao cadastrar prédio: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Prédio</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center">Cadastrar Novo Prédio</h1>
        <form action="" method="POST" class="mt-4">
            <div class="form-group">
                <label for="nome_predio">Nome do Prédio:</label>
                <input type="text" name="nome_predio" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="responsavel_id">Responsável:</label>
                <select name="responsavel_id" class="form-control" required>
                    <option value="">Selecione um responsável</option>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['nome']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Cadastrar</button>
        </form>
        <div class="text-center mt-3">
            <a href="menu.php">Voltar para o menu</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
