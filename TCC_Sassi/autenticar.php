<?php
session_start();
include 'db.php'; // Inclui o arquivo de conex�o com o banco de dados

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Prepara a consulta para evitar SQL Injection
    $stmt = $conn->prepare("SELECT id, senha FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Verifica se o usu�rio existe
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        // Verifica a senha
        if (password_verify($senha, $hashed_password)) {
            // Senha correta, inicia a sess�o
            $_SESSION['usuario_id'] = $id;
            header("Location: menu.php"); // Redireciona para a p�gina do Menu
            exit();
        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "Usu�rio n�o encontrado!";
    }
    $stmt->close();
}
?>
