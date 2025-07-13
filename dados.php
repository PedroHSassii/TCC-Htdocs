<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sistema_automacao";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar
if ($conn->connect_error) {
  die("Erro na conexão: " . $conn->connect_error);
}

// Obter parâmetros da URL
$sala = isset($_GET['sala']) ? intval($_GET['sala']) : 0;
$predio = isset($_GET['predio']) ? intval($_GET['predio']) : 0;
$codigo = isset($_GET['codigo']) ? intval($_GET['codigo']) : null; // Novo parâmetro para o código captado

// Executa a consulta
$sql = "SELECT leitura, alteracao FROM temporaria WHERE sala = $sala AND predio = $predio";
$result = $conn->query($sql);

$response = array();

if ($result->num_rows > 0) {
  // Obtém os dados da linha
  $row = $result->fetch_assoc();
  
  // Verifica os valores de leitura e alteracao
  if ($row['leitura'] == 1) {
    $response['acao'] = 'captacao';
  } elseif ($row['alteracao'] == 1) {
    $response['acao'] = 'atualiza';
  } else {
    $response['acao'] = 'nenhuma';
  }
  
  // Se um código foi passado, salva no banco de dados
  if ($codigo !== null) {
    $updateSql = "UPDATE temporaria SET codigo_captado = $codigo WHERE sala = $sala AND predio = $predio";
    if ($conn->query($updateSql) === TRUE) {
      $response['codigo_salvo'] = true;
    } else {
      $response['codigo_salvo'] = false;
      $response['erro'] = $conn->error;
    }
  }
} else {
  $response['acao'] = 'nenhuma';
}

// Retorna JSON
echo json_encode($response);

$conn->close();
?>
