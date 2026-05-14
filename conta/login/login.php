<?php
// Configurações de erro para ajudar no desenvolvimento
// Atualizado por André Felipe
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
session_start();
header('Content-Type: application/json');

// Incluindo a conexão centralizada para evitar repetição de código
// Corrigido por André Felipe
require_once '../../conexao.php';

// O identificador pode ser E-mail ou CPF

// trim para remover espaços extras
$identificador = trim($_POST['identificador'] ?? '');
$senha = $_POST['senha'] ?? '';

if (empty($identificador) || empty($senha)) {
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos.']);
    exit;
}

// Corrigido por André Felipe: Nome da tabela 'usuario' em minúsculo e busca por id_usuario
$stmt = mysqli_prepare($conexao, "SELECT * FROM usuario WHERE email = ? OR cpf = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'ss', $identificador, $identificador);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($resultado);

// Verifica a senha usando hash (segurança)
if ($usuario && password_verify($senha, $usuario['senha'])) {
    // Corrigido para o padrão solicitado (Suspect logic)
    $_SESSION['id']    = $usuario['id_usuario'];
    $_SESSION['nome']  = $usuario['nome'];
    $_SESSION['tipo']  = $usuario['tipo_usuario'];
    $_SESSION['email'] = $usuario['email'];


    // AUTORIA DA ABOBRINHA
    // $_SESSION['u'] = $usuario;

    // Redirecionamento corrigido para a pasta de perfil (já que dashboard não existe)
    // Atualizado por André Felipe
    $redirect = '../../perfil/index.html'; 

    echo json_encode(['success' => true, 'redirect' => $redirect]);
} else {
    echo json_encode(['success' => false, 'message' => 'E-mail/CPF ou senha incorretos.']);
}
exit;
?>
