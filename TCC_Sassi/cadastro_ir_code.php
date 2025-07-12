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
    echo "Acesso negado. Apenas administradores podem cadastrar códigos IR.";
    exit();
}

// Obter ambientes com o nome do prédio e número da sala concatenados
$ambientes = [];
$stmt = $conn->prepare("
    SELECT a.id, CONCAT(p.nome, ' - Sala ', a.numero_sala) AS ambiente_nome 
    FROM ambientes a 
    JOIN predios p ON a.predio_id = p.id
");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $ambientes[] = $row;
}

// Obter funções
$funcoes = [];
$result = $conn->query("SELECT cod_tipofunc, funcao FROM funcoes");
while ($row = $result->fetch_assoc()) {
    $funcoes[] = $row;
}

// Lógica para ler o código IR (simulação)
$codigo_ir = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ler'])) {
    // Endereço MAC do dispositivo (apenas informativo, não é usado para enviar)
    $endereco_mac = 'c8:c9:a3:69:9a:10';

    // Define o endereço IP do dispositivo (substitua pelo IP correto)
    $ip_dispositivo = '192.168.137.25'; // Exemplo de IP, substitua conforme necessário
    $porta = 80 ; // Porta de destino

    // Cria um socket
    $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($socket === false) {
        echo "Erro ao criar socket: " . socket_strerror(socket_last_error());
        exit;
    }

    // Envia um sinal simples (exemplo de mensagem)
    $mensagem = "INICIAR_LEITURA"; // Mensagem que o dispositivo deve reconhecer
    $resultado = socket_sendto($socket, $mensagem, strlen($mensagem), 0, $ip_dispositivo, $porta);

    if ($resultado === false) {
        echo "Erro ao enviar sinal: " . socket_strerror(socket_last_error($socket));
    } else {
        echo "Sinal enviado para iniciar a função com sucesso!";
    }
    // Fecha o socket
    socket_close($socket);
}
 elseif (isset($_POST['salvar'])) {
        $cod_tipofunc = $_POST['funcao'];
        $cod_ambiente = $_POST['ambiente'];
        $codigo_ir = $_POST['codigo_ir'];
        $modo = $_POST['modo'];
        $temperatura = $_POST['temperatura'];
        $velocidade = $_POST['velocidade'];
        $swing = isset($_POST['swing']) ? 1 : 0; // 1 para true, 0 para false
        $timer = isset($_POST['timer']) ? 1 : 0; // 1 para true, 0 para false
        $status = isset($_POST['status']) ? 1 : 0; // 1 para true, 0 para false
        $checksum = $_POST['checksum'];

        // Salvar no banco de dados
        $stmt = $conn->prepare("INSERT INTO ir_codes (cod_tipofunc, cod_ambiente, codigo_ir, modo, temperatura, velocidade, swing, timer, status, checksum) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisiiiiiis", $cod_tipofunc, $cod_ambiente, $codigo_ir, $modo, $temperatura, $velocidade, $swing, $timer, $status, $checksum);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Código IR salvo com sucesso!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <title>Cadastro de Código IR</title>
    <style>
        /* Estilos adicionais para centralizar e aumentar os botões */
        button {
            padding: 15px; /* Aumenta o padding para botões */
            font-size: 16px; /* Aumenta o tamanho da fonte */
            width: 100%; /* Faz com que os botões ocupem toda a largura do contêiner */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Cadastro de Código IR</h1>
        <form method="POST">
            <div class="form-group">
                <label for="ambiente">Ambiente</label>
                <select class="form-control" id="ambiente" name="ambiente" required>
                    <option value="">Selecione um ambiente</option>
                    <?php foreach ($ambientes as $ambiente): ?>
                        <option value="<?php echo $ambiente['id']; ?>"><?php echo htmlspecialchars($ambiente['ambiente_nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="codigo_ir">Código IR</label>
                <input type="text" class="form-control" id="codigo_ir" name="codigo_ir" value="<?php echo htmlspecialchars($codigo_ir); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="modo">Modo de Operação</label>
                <select class="form-control" id="modo" name="modo" required>
                    <option value="1">Frio</option>
                    <option value="2">Quente</option>
                    <option value="3">Automático</option>
                    <option value="4">Ventilação</option>
                    <option value="5">Desumidificação</option>
                </select>
            </div>

            <div class="form-group">
                <label for="temperatura">Temperatura (°C)</label>
                <input type="number" class="form-control" id="temperatura" name="temperatura" min="16" max="30" required>
            </div>

            <div class="form-group">
                <label for="velocidade">Velocidade do Ventilador</label>
                <select class="form-control" id="velocidade" name="velocidade" required>
                    <option value="1">Automática</option>
                    <option value="2">Baixa</option>
                    <option value="3">Média</option>
                    <option value="4">Alta</option>
                </select>
            </div>

            <div class="form-group">
                <label for="swing">Swing</label>
                <input type="checkbox" id="swing" name="swing">
            </div>

            <div class="form-group">
                <label for="timer">Timer</label>
                <input type="checkbox" id="timer" name="timer">
            </div>

            <div class="form-group">
                <form method="POST" action="seu_script.php">
                <label for="status">Status de Energia</label>
                <input type="checkbox" id="status" name="status" value="1" checked>
            </div>

            <div class="text-center" style="margin-bottom: 15px;"> <!-- Centraliza os botões -->
                <button type="submit" name="ler" class="btn btn-primary">Ler</button>
            </div>

            <div class="text-center"> <!-- Centraliza os botões -->                
                <button type="submit" name="salvar" class="btn btn-success">Salvar</button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
