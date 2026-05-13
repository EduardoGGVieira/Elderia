<?php
session_start();
header('Content-Type: application/json');
require_once '../../conexao.php'; 

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

$id = $_SESSION['id'];
$tipo = $_SESSION['tipo']; 

// 1. Dados comuns da tabela usuario
$sqlUser = "SELECT nome, email, telefone, cpf FROM usuario WHERE id_usuario = ?";
$stmt = mysqli_prepare($conexao, $sqlUser);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$userData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// 2. Dados específicos detalhados
if ($tipo === 'idoso') {
    $sqlSpec = "SELECT data_nascimento, alergias, informacoes_saude, possui_acessibilidade, necessidades_acessibilidade FROM idoso WHERE id_idoso = ?";
} else {
    $sqlSpec = "SELECT registro_profissional, especialidade, biografia, localizacao, data_emissao FROM profissional WHERE id_profissional = ?";
}

$stmtSpec = mysqli_prepare($conexao, $sqlSpec);
mysqli_stmt_bind_param($stmtSpec, "i", $id);
mysqli_stmt_execute($stmtSpec);
$specData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtSpec));

echo json_encode([
    'success' => true,
    'tipo' => $tipo,
    'dados' => array_merge($userData, $specData)
]);
?>