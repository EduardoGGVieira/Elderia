<?php
session_start();
header('Content-Type: application/json');
require_once '../conexao.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada']);
    exit;
}

$id = $_SESSION['id'];
$tipo = $_SESSION['tipo'];

// 1. Busca dados comuns da tabela usuario
$sql_user = "SELECT nome, email, telefone, cpf FROM usuario WHERE id_usuario = ?";
$stmt = mysqli_prepare($conexao, $sql_user);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// 2. Busca dados específicos conforme o tipo logado
if ($tipo === 'idoso') {
    $sql_spec = "SELECT data_nascimento, alergias, informacoes_saude, necessidades_acessibilidade, possui_acessibilidade FROM idoso WHERE id_idoso = ?";
} else {
    $sql_spec = "SELECT registro_profissional, especialidade, biografia, localizacao, data_emissao FROM profissional WHERE id_profissional = ?";
}

$stmt_spec = mysqli_prepare($conexao, $sql_spec);
mysqli_stmt_bind_param($stmt_spec, "i", $id);
mysqli_stmt_execute($stmt_spec);
$spec_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_spec));

echo json_encode([
    'success' => true,
    'tipo' => $tipo,
    'dados' => array_merge($user_data, $spec_data)
]);
?>