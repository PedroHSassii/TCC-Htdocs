<?php
session_start();
include 'db.php'; // Conexão com o banco de dados

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Lê o corpo da requisição
$data = json_decode(file_get_contents("php://input"), true);

// Verifica se a ação foi recebida
if (isset($data['acao']) && isset($data['sala_id'])) {
    $sala_id = $data['sala_id'];
    $acao = $data['acao'];

    // Obtém o status atual do ambiente
    $stmt = $conn->prepare("SELECT status FROM ambientes WHERE id = ?");
    $stmt->bind_param("i", $sala_id);
    $stmt->execute();
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("SELECT velocidade FROM ambientes WHERE id = ?");
    $stmt->bind_param("i", $sala_id);
    $stmt->execute();
    $stmt->bind_result($velocidade);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("SELECT temperatura FROM ambientes WHERE id = ?");
    $stmt->bind_param("i", $sala_id);
    $stmt->execute();
    $stmt->bind_result($temperatura);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("SELECT modo FROM ambientes WHERE id = ?");
    $stmt->bind_param("i", $sala_id);
    $stmt->execute();
    $stmt->bind_result($modo);
    $stmt->fetch();
    $stmt->close();

    // Atualiza o status baseado na ação
    switch ($acao) {
        case 'Power':
            // Alterna o status entre 1 (ligado) e 2 (desligado)
            $new_status = ($status === 1) ? 2 : 1;
            // Atualizar o status no banco de dados
            $stmt = $conn->prepare("UPDATE ambientes SET status = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_status, $sala_id);
            $stmt->execute();
            $stmt->close(); // Fechar a declaração após a execução
            break;
        case 'Fan Speed':
            // Alterna entre 1 (Automática), 2 (Baixa), 3 (Média), 4 (Alta)
            $new_velocidade = ($velocidade % 4) + 1; // Isso alterna entre 1 e 4
            // Atualizar a velocidade no banco de dados
            $stmt = $conn->prepare("UPDATE ambientes SET velocidade = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_velocidade, $sala_id);
            $stmt->execute();
            $stmt->close(); // Fechar a declaração após a execução
            break;
        case 'Temp+':
            if ($temperatura < 32) {
            $new_temperatura = $temperatura + 1; // Aumenta a temperatura em 1
        } else {
            $new_temperatura = 32; // Mantém a temperatura em 30 se já estiver nesse valor
        }
            $stmt = $conn->prepare("UPDATE ambientes SET temperatura = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_temperatura, $sala_id);
            $stmt->execute();
            $stmt->close(); // Fechar a declaração após a execução
            break;
        case 'Temp−':
            if ($temperatura > 16) {
            $new_temperatura = $temperatura - 1; // Diminui a temperatura em 1
        } else {
            $new_temperatura = 16; // Mantém a temperatura em 16 se já estiver nesse valor
        }
            $stmt = $conn->prepare("UPDATE ambientes SET temperatura = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_temperatura, $sala_id);
            $stmt->execute();
            $stmt->close(); // Fechar a declaração após a execução
            break;
        case 'Modo':
            // Alterna entre 1 e 5
            $new_modo = ($modo % 5) + 1; // Isso alterna entre 1 e 5
            // Atualizar o modo no banco de dados
            $stmt = $conn->prepare("UPDATE ambientes SET modo = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_modo, $sala_id);
            $stmt->execute();
            $stmt->close(); // Fechar a declaração após a execução
            break;

        case 'Swing':
            // Aqui você pode implementar a lógica para ativar/desativar o swing
            $new_status = $status; // Exemplo: manter o status atual
            break;
        default:
            echo json_encode(["message" => "Ação não reconhecida."]);
            exit();
    }

    
    if ($stmt->execute()) {
        
        // Aqui você pode enviar um sinal para o Arduino
        // Exemplo: enviar um comando IR
        // irsend.sendNEC(codigo_ir, 32); // Substitua 'codigo_ir' pelo código correspondente
    } else {
        echo json_encode(["message" => "Erro ao executar a ação: " . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["message" => "Dados insuficientes."]);
}
?>
