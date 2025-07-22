<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sistema_automacao";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
  die(json_encode(['error' => 'Erro na conexão com o banco de dados: ' . $conn->connect_error]));
}

// Definir o charset para UTF-8
$conn->set_charset("utf8");

// Obter parâmetros da URL
// Usar filter_input para obter e validar dados de forma mais segura
$sala = filter_input(INPUT_GET, 'sala', FILTER_VALIDATE_INT);
$predio = filter_input(INPUT_GET, 'predio', FILTER_VALIDATE_INT);
$codigo = filter_input(INPUT_GET, 'codigo', FILTER_VALIDATE_INT); // Código IR captado
$temp = filter_input(INPUT_GET, 'temp', FILTER_VALIDATE_FLOAT);   // Temperatura
$hum = filter_input(INPUT_GET, 'hum', FILTER_VALIDATE_FLOAT);     // Umidade

$response = array();

// --- Lógica para verificar ação (captacao/atualiza) ---
if ($sala !== false && $predio !== false) {
    // Usar prepared statement para evitar injeção SQL
    $stmt = $conn->prepare("SELECT leitura, alteracao FROM temporaria WHERE sala = ? AND predio = ?");
    if ($stmt === false) {
        $response['error'] = 'Erro ao preparar a consulta de ação: ' . $conn->error;
    } else {
        $stmt->bind_param("ii", $sala, $predio); // "ii" indica dois inteiros
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            if ($row['leitura'] == 1) {
                $response['acao'] = 'captacao';
            } elseif ($row['alteracao'] == 1) {
                // Lógica para buscar o código IR para atualização
                // Usar prepared statement para evitar injeção SQL
                $sql_ir_code = "SELECT codigo_ir 
                                FROM ir_codes 
                                WHERE numero_sala = ? 
                                AND predio_id = ? 
                                AND modo = (SELECT modo FROM AMBIENTES WHERE numero_sala = ? AND predio_id = ?)
                                AND temperatura = (SELECT TEMPERATURA FROM AMBIENTES WHERE numero_sala = ? AND predio_id = ?)
                                AND velocidade = (SELECT VELOCIDADE FROM AMBIENTES WHERE numero_sala = ? AND predio_id = ?)
                                AND swing = (SELECT SWING FROM AMBIENTES WHERE numero_sala = ? AND predio_id = ?)
                                AND timer = (SELECT TIMER FROM AMBIENTES WHERE numero_sala = ? AND predio_id = ?)
                                AND status = (SELECT STATUS FROM AMBIENTES WHERE numero_sala = ? AND predio_id = ?)";
                
                $stmt_ir = $conn->prepare($sql_ir_code);
                if ($stmt_ir === false) {
                    $response['error'] = 'Erro ao preparar a consulta de código IR: ' . $conn->error;
                } else {
                    // "iiiiiiiiiiii" indica 12 inteiros para os 6 pares de sala/predio
                    $stmt_ir->bind_param("iiiiiiiiiiii", $sala, $predio, $sala, $predio, $sala, $predio, $sala, $predio, $sala, $predio, $sala, $predio);
                    $stmt_ir->execute();
                    $result_ir = $stmt_ir->get_result();
                    
                    if ($result_ir->num_rows > 0) {
                        $codigo_ir = $result_ir->fetch_assoc()['codigo_ir'];
                        $response['acao'] = 'atualiza'; // Indica que é uma ação de atualização
                        $response['codigo_ir'] = $codigo_ir; // Enviar o código IR para o Arduino
                    } else {
                        $response['acao'] = 'nenhuma'; // Nenhuma alteração encontrada ou código IR
                    }
                    $stmt_ir->close();
                }
            } else {
                $response['acao'] = 'nenhuma'; // Nenhuma ação de leitura ou alteração
            }
        } else {
            $response['acao'] = 'nenhuma'; // Nenhuma entrada encontrada para sala/predio na temporaria
        }
        $stmt->close();
    }
} else {
    $response['error'] = 'Parâmetros sala ou predio inválidos.';
    $response['acao'] = 'nenhuma'; // Default para nenhuma ação se os parâmetros base estiverem faltando
}


// --- Lógica para salvar código captado ---
// Este bloco deve ser acessado por salvar_codigo.php
// No entanto, se for para ser no mesmo script, ele precisa ser condicional
// para não interferir com a lógica de "ação".
// Para simplificar, vamos assumir que este script é para "dados.php" e "salvar_codigo.php"
// e "atualizar_dados.php" seriam scripts separados ou teriam um parâmetro 'action'.
// Para o propósito de correção, vou integrar, mas o ideal seria separar.

// Se um código foi passado, salva no banco de dados
if ($codigo !== false && $codigo !== null) {
    // Usar prepared statement para evitar injeção SQL
    $updateSql = "UPDATE temporaria SET codigo_captado = ? WHERE sala = ? AND predio = ?";
    $stmt_save = $conn->prepare($updateSql);
    if ($stmt_save === false) {
        $response['codigo_salvo'] = false;
        $response['erro_salvar'] = 'Erro ao preparar a consulta de salvar código: ' . $conn->error;
    } else {
        $stmt_save->bind_param("iii", $codigo, $sala, $predio); // "iii" indica três inteiros
        if ($stmt_save->execute()) {
            $response['codigo_salvo'] = true;
        } else {
            $response['codigo_salvo'] = false;
            $response['erro_salvar'] = $stmt_save->error;
        }
        $stmt_save->close();
    }
}


if ($temp !== false && $hum !== false && $sala !== false && $predio !== false) {
    $response['temp_hum_atualizado'] = true;
    $response['temperatura'] = $temp;
    $response['umidade'] = $hum;

    
    $updateTempHumSql = "UPDATE AMBIENTES SET temp_atual = ?, hum_atual = ? WHERE numero_sala = ? AND predio_id = ?";
    $stmt_temp_hum = $conn->prepare($updateTempHumSql);
    if ($stmt_temp_hum) {
        $stmt_temp_hum->bind_param("ddii", $temp, $hum, $sala, $predio); // "ddii" para dois doubles e dois inteiros
        if (!$stmt_temp_hum->execute()) {
            $response['temp_hum_atualizado'] = false;
            $response['erro_temp_hum'] = $stmt_temp_hum->error;
        }
        $stmt_temp_hum->close();
    } else {
        $response['temp_hum_atualizado'] = false;
        $response['erro_temp_hum'] = $conn->error;
    }
    
}


// Retorna JSON
header('Content-Type: application/json'); // Define o cabeçalho para JSON
echo json_encode($response);

$conn->close();
?>
