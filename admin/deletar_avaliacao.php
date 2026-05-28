<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../conexao.php';

// SEGURANÇA: Verifica se o usuário está logado E se possui o tipo 'admin'
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Acesso negado. Apenas administradores podem realizar esta ação.']);
    exit;
}

$id_avaliacao = filter_input(INPUT_POST, 'id_avaliacao', FILTER_VALIDATE_INT);

if (!$id_avaliacao) {
    echo json_encode(['success' => false, 'error' => 'ID da avaliação inválido ou não informado.']);
    exit;
}
$sql = "DELETE FROM avaliacao WHERE id_avaliacao = ?";
$stmt = mysqli_prepare($conexao, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $id_avaliacao);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'A avaliação não foi encontrada ou já foi removida.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Falha na execução do banco de dados: ' . mysqli_stmt_error($stmt)]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'error' => 'Falha na preparação da consulta SQL.']);
}

mysqli_close($conexao);
?>