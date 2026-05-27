<?php

header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

if (!$conexao) {
    echo json_encode([]);
    exit;
}

// SQL que junta a avaliação com o nome do usuário idoso que a fez
$sql = "SELECT a.nota, a.comentario, u.nome AS nome_idoso 
        FROM avaliacao a
        INNER JOIN usuario u ON a.id_usuario = u.id_usuario
        WHERE a.status_moderacao = 'aprovada' 
        ORDER BY a.id_avaliacao DESC 
        LIMIT 3"; // Limita em até 3 depoimentos para não quebrar o layout

$result = mysqli_query($conexao, $sql);
$depoimentos = [];

while ($row = mysqli_fetch_assoc($result)) {
    $depoimentos[] = $row;
}

echo json_encode($depoimentos);
mysqli_close($conexao);
?>