<?php
ini_set('display_errors', 0);
error_reporting(0);
session_start();
header('Content-Type: application/json');

$conn = mysqli_connect('localhost', 'root', '', 'elderia');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Erro na conexão com o banco.']);
    exit;
}

mysqli_set_charset($conn, 'utf8');

$identificador = trim($_POST['identificador'] ?? '');
$senha = $_POST['senha'] ?? '';

if (empty($identificador) || empty($senha)) {
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos.']);
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM Usuario WHERE email = ? OR cpf = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'ss', $identificador, $identificador);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($resultado);

if ($usuario && password_verify($senha, $usuario['senha'])) {
    $_SESSION['usuario_id']   = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_tipo'] = $usuario['tipo_usuario'];

    $redirect = ($usuario['tipo_usuario'] === 'profissional')
        ? '../dashboard/profissional.php'
        : '../dashboard/idoso.php';

    echo json_encode(['success' => true, 'redirect' => $redirect]);
} else {
    echo json_encode(['success' => false, 'message' => 'E-mail/CPF ou senha incorretos.']);
}
exit;
?>