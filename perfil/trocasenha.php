<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../conexao.php';


if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Você precisa estar logado para realizar esta ação.']);
    exit;
}

$id_usuario     = $_SESSION['id'];
$senha_antiga   = $_POST['oldPassword'] ?? '';
$senha_nova     = $_POST['newPassword'] ?? '';
$confirmar_nova = $_POST['confirmPassword'] ?? '';

if (empty($senha_antiga) || empty($senha_nova) || empty($confirmar_nova)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, preencha todos os campos do formulário.']);
    exit;
}

if ($senha_nova !== $confirmar_nova) {
    echo json_encode(['success' => false, 'message' => 'A nova senha e a confirmação não coincidem.']);
    exit;
}


$stmt = mysqli_prepare(
    $conexao,
    "SELECT senha FROM usuario WHERE id_usuario = ? LIMIT 1"
);

if (!$stmt) {
    error_log('mysqli_prepare falhou ao buscar senha: ' . mysqli_error($conexao));
    echo json_encode(['success' => false, 'message' => 'Erro interno de servidor.']);
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $id_usuario);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

if (!$usuario) {
    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado.']);
    exit;
}


if (!password_verify($senha_antiga, $usuario['senha'])) {
    echo json_encode(['success' => false, 'message' => 'A senha atual está incorreta.']);
    exit;
}


$nova_senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);

$stmt_update = mysqli_prepare(
    $conexao,
    "UPDATE usuario SET senha = ? WHERE id_usuario = ?"
);

if (!$stmt_update) {
    error_log('mysqli_prepare falhou ao atualizar senha: ' . mysqli_error($conexao));
    echo json_encode(['success' => false, 'message' => 'Erro interno de servidor ao atualizar a senha.']);
    exit;
}

mysqli_stmt_bind_param($stmt_update, 'si', $nova_senha_hash, $id_usuario);
$executou = mysqli_stmt_execute($stmt_update);
mysqli_stmt_close($stmt_update);

if ($executou) {
    echo json_encode(['success' => true, 'message' => 'Sua senha foi atualizada com sucesso!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Não foi possível atualizar a senha no banco de dados.']);
}
exit;
?>
