<?php
// deletar_avaliacao.php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../conexao.php'; 

// Segurança: Verifica se o usuário está logado e se ele é um ADMIN
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Acesso negado. Apenas administradores podem apagar avaliações.']);
    exit;
}

$id_avaliacao = filter_input(INPUT_POST, 'id_avaliacao', FILTER_VALIDATE_INT);

if (!$id_avaliacao) {
    echo json_encode(['success' => false, 'error' => 'ID da avaliação inválido.']);
    exit;
}

// Executa a exclusão no banco de dados
$sql = "DELETE FROM avaliacao WHERE id_avaliacao = ?";
$stmt = mysqli_prepare($conexao, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $id_avaliacao);
    
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Avaliação não encontrada ou já foi removida.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Falha ao executar a exclusão no banco de dados.']);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'error' => 'Erro na preparação do comando SQL.']);
}

mysqli_close($conexao);
?>