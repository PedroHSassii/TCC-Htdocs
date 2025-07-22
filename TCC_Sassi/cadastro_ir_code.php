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
    SELECT a.id, CONCAT(p.nome, ' - Sala ', a.numero_sala) AS ambiente_nome, a.predio_id, a.numero_sala
    FROM ambientes a
    JOIN predios p ON a.predio_id = p.id
");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $ambientes[] = $row;
}

// Lógica para iniciar a leitura do código IR (simulação)
if (isset($_POST['ler'])) {
    // Obter o ID do ambiente selecionado
    $cod_ambiente = $_POST['ambiente'];

    // Buscar o predio_id e numero_sala a partir do cod_ambiente
    $stmt_ambiente_info = $conn->prepare("SELECT predio_id, numero_sala FROM ambientes WHERE id = ?");
    $stmt_ambiente_info->bind_param("i", $cod_ambiente);
    $stmt_ambiente_info->execute();
    $stmt_ambiente_info->bind_result($predio_id, $numero_sala);
    $stmt_ambiente_info->fetch();
    $stmt_ambiente_info->close();

    // Inserir na tabela 'temporaria' para indicar que a leitura foi iniciada
    $leitura = 1; // Setar 'leitura' como 1
    $alteracao = NULL; // Deixar como NULL inicialmente
    $codigo_captado = NULL; // Deixar como NULL inicialmente
    $stmt_insert_temp = $conn->prepare("INSERT INTO temporaria (leitura, alteracao, codigo_captado, predio, sala) VALUES (?, ?, ?, ?, ?)");
    $stmt_insert_temp->bind_param("iiiis", $leitura, $alteracao, $codigo_captado, $predio_id, $numero_sala);
    if ($stmt_insert_temp->execute()) {
        // Não exibir alert aqui, a resposta será via AJAX
        echo "<script>alert('Registro temporário criado com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao criar registro temporário: " . $stmt_insert_temp->error . "');</script>";
    }
    $stmt_insert_temp->close();
} elseif (isset($_POST['salvar'])) {
    // Obter o ID do ambiente selecionado
    $cod_ambiente = $_POST['ambiente'];

    // Buscar o predio_id e numero_sala a partir do cod_ambiente
    $stmt_ambiente_info = $conn->prepare("SELECT predio_id, numero_sala FROM ambientes WHERE id = ?");
    $stmt_ambiente_info->bind_param("i", $cod_ambiente);
    $stmt_ambiente_info->execute();
    $stmt_ambiente_info->bind_result($predio_id, $numero_sala);
    $stmt_ambiente_info->fetch();
    $stmt_ambiente_info->close();

    $codigo_ir = $_POST['codigo_ir'];
    $modo = $_POST['modo'];
    $temperatura = $_POST['temperatura'];
    $velocidade = $_POST['velocidade'];
    $swing = isset($_POST['swing']) ? 1 : 0; // 1 para true, 0 para false
    $timer = isset($_POST['timer']) ? 1 : 0; // 1 para true, 0 para false
    $status = isset($_POST['status']) ? 1 : 0; // 1 para true, 0 para false

    // Salvar no banco de dados
    $stmt = $conn->prepare("INSERT INTO ir_codes (numero_sala, predio_id, codigo_ir, modo, temperatura, velocidade, swing, timer, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisiiiiii", $numero_sala, $predio_id, $codigo_ir, $modo, $temperatura, $velocidade, $swing, $timer, $status);    if ($stmt->execute()) {
        echo "<script>alert('Código IR salvo com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao salvar código IR: " . $stmt->error . "');</script>";
    }
    $stmt->close();
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
        <form id="irCodeForm" method="POST">
            <div class="form-group">
                <label for="ambiente">Ambiente</label>
                <select class="form-control" id="ambiente" name="ambiente" required>
                    <option value="">Selecione um ambiente</option>
                    <?php foreach ($ambientes as $ambiente): ?>
                        <option value="<?php echo $ambiente['id']; ?>"
                                data-predio-id="<?php echo $ambiente['predio_id']; ?>"
                                data-numero-sala="<?php echo $ambiente['numero_sala']; ?>">
                            <?php echo htmlspecialchars($ambiente['ambiente_nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="codigo_ir">Código IR</label>
                <input type="text" class="form-control" id="codigo_ir" name="codigo_ir" value="" readonly>
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
                <label for="status">Status de Energia</label>
                <input type="checkbox" id="status" name="status" value="1" checked>
            </div>

            <div class="text-center" style="margin-bottom: 15px;">
                <button type="button" id="btnLer" class="btn btn-primary">Ler</button>
            </div>

            <div class="text-center">
                <button type="submit" name="salvar" class="btn btn-success">Salvar</button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> <!-- Usar a versão completa do jQuery para AJAX -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            let pollingInterval; // Variável para armazenar o ID do intervalo de polling

            $('#btnLer').on('click', function(e) {
                e.preventDefault(); // Previne o envio padrão do formulário

                const selectedOption = $('#ambiente option:selected');
                const ambienteId = selectedOption.val();
                const predioId = selectedOption.data('predio-id');
                const numeroSala = selectedOption.data('numero-sala');

                if (!ambienteId) {
                    alert('Por favor, selecione um ambiente.');
                    return;
                }

                // Limpa o campo Código IR e desabilita o botão Ler
                $('#codigo_ir').val('');
                $('#btnLer').prop('disabled', true).text('Buscando Código IR...');

                // Envia a requisição inicial para criar o registro temporário
                $.ajax({
                    url: 'cadastro_ir_code.php', // O mesmo arquivo para a lógica de inserção temporária
                    type: 'POST',
                    data: {
                        ler: true, // Indica que é a ação de "ler"
                        ambiente: ambienteId
                    },
                    success: function(response) {
                        // Inicia o polling para buscar o código IR
                        startPolling(predioId, numeroSala);
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao iniciar a leitura: ' + xhr.responseText);
                        $('#btnLer').prop('disabled', false).text('Ler');
                    }
                });
            });

            function startPolling(predioId, numeroSala) {
                // Limpa qualquer intervalo anterior para evitar múltiplos polls
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                }

                pollingInterval = setInterval(function() {
                    $.ajax({
                        url: 'get_ir_code.php', // O novo endpoint para buscar o código
                        type: 'GET',
                        data: {
                            predio_id: predioId,
                            numero_sala: numeroSala
                        },
                        dataType: 'json',
                        success: function(data) {
                            if (data.success && data.codigo_ir) {
                                $('#codigo_ir').val(data.codigo_ir);
                                alert('Código IR captado com sucesso!');
                                clearInterval(pollingInterval); // Para o polling
                                $('#btnLer').prop('disabled', false).text('Ler');
                            } else {
                                // Código ainda não captado, continua o polling
                                console.log(data.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Erro no polling: ' + xhr.responseText);
                            clearInterval(pollingInterval); // Para o polling em caso de erro
                            alert('Erro ao buscar código IR. Tente novamente.');
                            $('#btnLer').prop('disabled', false).text('Ler');
                        }
                    });
                }, 3000); // Polling a cada 3 segundos (ajuste conforme necessário)
            }
        });
    </script>
</body>
</html>
