<?php

session_start();
header('Content-Type: application/json');
$conn = mysqli_connect('localhost:3307', 'root', '', 'elderia'); // Ajuste a porta se necessário


if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Usuário não logado']);
    exit;
}

$id_idoso = $_SESSION['id']; 

// SQL que junta dados da consulta com o nome do profissional
$sql = "SELECT c.id_consulta, c.data_hora, c.status, u.nome AS profissional_nome, p.especialidade 
        FROM consulta c
        JOIN profissional p ON c.id_profissional = p.id_profissional
        JOIN usuario u ON p.id_profissional = u.id_usuario
        WHERE c.id_idoso = ? 
        ORDER BY c.data_hora ASC";



$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id_idoso);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$consultas = [];
while ($row = mysqli_fetch_assoc($result)) {
    $consultas[] = $row;
}

echo json_encode($consultas);
?>