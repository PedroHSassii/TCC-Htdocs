<?php
session_start();
include 'db.php'; // Conexão com o banco de dados

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obter as salas disponíveis
$stmt = $conn->prepare("SELECT a.id AS ambiente_id, CONCAT(p.nome, ' - Sala ', a.numero_sala) AS ambiente_nome 
                         FROM ambientes a 
                         JOIN predios p ON a.predio_id = p.id");
$stmt->execute();
$result = $stmt->get_result();
$salas = [];

// Armazena as salas em um array
while ($row = $result->fetch_assoc()) {
    $salas[] = $row;
}

// Inicializa a variável para a sala selecionada
$sala_selecionada = null;
$modo = $temperatura = $velocidade = $timer = $status = null; // Inicializa as variáveis

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sala_id'])) {
    $sala_selecionada = $_POST['sala_id']; // Armazena a sala selecionada

    // Obter dados do ambiente selecionado
    $stmt = $conn->prepare("SELECT modo, temperatura, velocidade, timer, status FROM ambientes WHERE id = ?");
    $stmt->bind_param("i", $sala_selecionada);
    $stmt->execute();
    $stmt->bind_result($modo, $temperatura, $velocidade, $timer, $status);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Controle de Ar-Condicionado</title>
    <style>
        body {
            background-color: #f4f4f4;
        }
        .container {
            margin-top: 50px;
        }
        .monitor {
            background-color: #fff;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .btn-icon {
            width: 100px; /* Largura dos botões */
            height: 80px; /* Altura dos botões */
            font-size: 24px; /* Tamanho da fonte */
            margin: 10px; /* Margem entre os botões */
        }
        .icon-container {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }
       .column {
            display: flex;
            flex-direction: column; /* Coloca os botões em coluna */
            align-items: center; /* Centraliza os botões */
        }
    </style>
</head>
<body>
    <div class="container">
        
        <?php if (!$sala_selecionada): ?>
            <form method="POST" class="mb-4">
                <div class="form-group">
                    <label for="sala_id">Selecione a Sala:</label>
                    <select class="form-control" id="sala_id" name="sala_id" required>
                        <option value="">Selecione uma sala</option>
                        <?php foreach ($salas as $sala): ?>
                            <option value="<?php echo $sala['ambiente_id']; ?>" <?php echo ($sala_selecionada == $sala['ambiente_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sala['ambiente_nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Selecionar</button>
            </form>
   
        <?php else: ?>
            <div class="monitor">
                <h4>Monitor de Controle</h4>
                <p><strong>Modo de Operação:</strong> 
                    <?php 
                    if (isset($modo)) {
                        switch ($modo) {
                            case 1:
                                echo 'Frio';
                                break;
                            case 2:
                                echo 'Quente';
                                break;
                            case 3:
                                echo 'Automático';
                                break;
                            case 4:
                                echo 'Ventilação';
                                break;
                            case 5:
                                echo 'Desumidificação';
                                break;
                            default:
                                echo 'N/A';
                                break;
                        }
                    } else {
                        echo 'N/A';
                    }
                    ?>
                <p><strong>Temperatura:</strong> <?php echo isset($temperatura) ? htmlspecialchars($temperatura) . ' °C' : 'N/A'; ?></p>
                <p><strong>Velocidade do Ventilador:</strong> <?php echo isset($velocidade) ? htmlspecialchars($velocidade) : 'N/A'; ?></p>
                <p><strong>Timer:</strong> <?php echo isset($timer) ? ($timer ? 'Ativado' : 'Desativado') : 'N/A'; ?></p>
                <p><strong>Status:</strong> <?php echo isset($status) ? ($status ? 'Ligado' : 'Desligado') : 'N/A'; ?></p>
            </div>

            <div class="icon-container">
                <div class="column">
                    <button class="btn btn-primary btn-icon" onclick="executarAcao('Power',  <?php echo $sala_selecionada;?>)">Power</button>
                    <button class="btn btn-primary btn-icon" onclick="executarAcao('Fan Speed',  <?php echo $sala_selecionada;?>)">Fan Speed</button>
                </div>

                <div class="column">
                    <button class="btn btn-primary btn-icon" onclick="executarAcao('Temp+',  <?php echo $sala_selecionada;?>)">Temp+</button>
                    <button class="btn btn-primary btn-icon" onclick="executarAcao('Temp−',  <?php echo $sala_selecionada;?>)">Temp−</button>
                </div>

                <div class="column">
                    <button class="btn btn-primary btn-icon" onclick="executarAcao('Modo',  <?php echo $sala_selecionada;?>)">Modo</button>
                    <button class="btn btn-primary btn-icon" onclick="executarAcao('Swing',  <?php echo $sala_selecionada;?>)">Swing</button>
                </div>           
             </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function executarAcao(acao, salaId) {
            fetch('acao.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ acao: acao, sala_id: salaId })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                // Aqui você pode atualizar a interface ou fazer outras ações
            })
            .catch(error => console.error('Erro:', error));
        }
    </script>
</body>
</html>
