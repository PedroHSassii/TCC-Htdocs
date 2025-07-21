<?php
include 'db.php'; // Conexão com o banco de dados

header('Content-Type: application/json');

if (isset($_GET['predio_id']) && isset($_GET['numero_sala'])) {
    $predio_id = $_GET['predio_id'];
    $numero_sala = $_GET['numero_sala'];

    // Buscar o código captado na tabela temporaria
    $stmt = $conn->prepare("SELECT codigo_captado FROM temporaria WHERE predio = ? AND sala = ? AND codigo_captado IS NOT NULL ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("is", $predio_id, $numero_sala);
    $stmt->execute();
    $stmt->bind_result($codigo_captado);
    $stmt->fetch();
    $stmt->close();

    if ($codigo_captado) {
        // Se um código for encontrado, retornar sucesso e o código
        echo json_encode(['success' => true, 'codigo_ir' => $codigo_captado]);

        // Opcional: Limpar o registro da tabela temporaria após a leitura
        $stmt_delete = $conn->prepare("DELETE FROM temporaria WHERE predio = ? AND sala = ? AND codigo_captado = ?");
        $stmt_delete->bind_param("iss", $predio_id, $numero_sala, $codigo_captado);
        $stmt_delete->execute();
        $stmt_delete->close();

    } else {
        // Se nenhum código for encontrado, retornar que ainda não está disponível
        echo json_encode(['success' => false, 'message' => 'Código IR ainda não captado.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos.']);
}
?>
