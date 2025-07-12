<?php
$servername = "localhost"; // O servidor MySQL
$username = "root"; // Nome de usuário padrão do MySQL no XAMPP
$password = ""; // Senha padrão (deixe vazio)
$dbname = "sistema_automacao"; // Nome do seu banco de dados

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>
