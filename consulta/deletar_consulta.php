<?php
// deletar_consulta.php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'Usuário não autenticado.']);
    exit;
}

$id_idoso = $_SESSION['id'];
$id_consulta = filter_input(INPUT_POST, 'id_consulta', FILTER_VALIDATE_INT);

if (!$id_consulta) {
    echo json_encode(['success' => false, 'error' => 'ID da consulta inválido.']);
    exit;
}

// 1. Procurar o profissional e o horário da consulta antes de alterar o status
$sql_busca = "SELECT id_profissional, data_hora FROM consulta WHERE id_consulta = ? AND id_idoso = ? LIMIT 1";
$stmt_busca = mysqli_prepare($conexao, $sql_busca);

$id_profissional = null;
$data_hora = null;

if ($stmt_busca) {
    mysqli_stmt_bind_param($stmt_busca, 'ii', $id_consulta, $id_idoso);
    mysqli_stmt_execute($stmt_busca);
    $resultado = mysqli_stmt_get_result($stmt_busca);
    if ($row = mysqli_fetch_assoc($resultado)) {
        $id_profissional = $row['id_profissional'];
        $data_hora = $row['data_hora'];
    }
    mysqli_stmt_close($stmt_busca);
}

if (!$id_profissional || !$data_hora) {
    echo json_encode(['success' => false, 'error' => 'Consulta não encontrada.']);
    exit;
}

// Inicia transação de segurança no banco para rodar os dois UPDATES juntos
mysqli_begin_transaction($conexao);

try {
    
    $sql_update_consulta = "UPDATE consulta SET status = 'cancelada' WHERE id_consulta = ? AND id_idoso = ?";
    $stmt_con = mysqli_prepare($conexao, $sql_update_consulta);
    mysqli_stmt_bind_param($stmt_con, 'ii', $id_consulta, $id_idoso);
    mysqli_stmt_execute($stmt_con);
    mysqli_stmt_close($stmt_con);

 
    $sql_update_agenda = "UPDATE agenda_disponivel SET status = 'livre' WHERE id_profissional = ? AND data_hora = ?";
    $stmt_age = mysqli_prepare($conexao, $sql_update_agenda);
    mysqli_stmt_bind_param($stmt_age, 'is', $id_profissional, $data_hora);
    mysqli_stmt_execute($stmt_age);
    mysqli_stmt_close($stmt_age);

    mysqli_commit($conexao);
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    mysqli_rollback($conexao);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

mysqli_close($conexao);
?>