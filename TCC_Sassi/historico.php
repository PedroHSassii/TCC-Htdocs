<?php
session_start();
include 'db.php'; // Conexão com o banco de dados

// Verifica se o usuário está logado
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
    echo "Acesso negado. Apenas administradores podem acessar esta página.";
    exit();
}

// Filtros
$usuario = isset($_POST['usuario']) ? $_POST['usuario'] : '';
$data_inicio = isset($_POST['data_inicio']) ? $_POST['data_inicio'] : '';
$data_fim = isset($_POST['data_fim']) ? $_POST['data_fim'] : '';

// Consulta para obter o histórico com filtros
$sql = "SELECT * FROM historico WHERE 1=1";

if ($usuario) {
    $sql .= " AND usuario = '" . $conn->real_escape_string($usuario) . "'";
}

if ($data_inicio) {
    $sql .= " AND data_hora >= '" . $conn->real_escape_string($data_inicio) . " 00:00:00'";
}

if ($data_fim) {
    $sql .= " AND data_hora <= '" . $conn->real_escape_string($data_fim) . " 23:59:59'";
}

$sql .= " ORDER BY data_hora DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Histórico de Uso</title>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Histórico de Uso do Ar-Condicionado</h1>

        <form method="POST" class="form-inline mb-4">
            <div class="form-group mx-sm-3 mb-2">
                <label for="usuario" class="sr-only">Usuário</label>
                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuário" value="<?php echo htmlspecialchars($usuario); ?>">
            </div>
            <div class="form-group mx-sm-3 mb-2">
                <label for="data_inicio" class="sr-only">Data Início</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo htmlspecialchars($data_inicio); ?>">
            </div>
            <div class="form-group mx-sm-3 mb-2">
                <label for="data_fim" class="sr-only">Data Fim</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo htmlspecialchars($data_fim); ?>">
            </div>
            <button type="submit" class="btn btn-primary mb-2">Filtrar</button>
        </form>

        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Data e Hora</th>
                    <th>Usuário</th>
                    <th>Temperatura (°C)</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row["data_hora"] . "</td>
                                <td>" . $row["usuario"] . "</td>
                                <td>" . $row["temperatura"] . "</td>
                                <td>" . $row["acao"] . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>Nenhum registro encontrado.</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
