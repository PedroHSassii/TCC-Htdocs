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

// Executa a consulta
$sql = "SELECT * FROM predios";
$result = $conn->query($sql);

// Retorna JSON
$data = array();
while($row = $result->fetch_assoc()) {
  $data[] = $row;
}
echo json_encode($data);
$conn->close();
?>
