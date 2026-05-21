<?php
// CODIGO CORRIGIDO PELO PIERRE
// DESENVOLVIMENTO: desative em produção (display_errors = 0)
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

session_start();
header('Content-Type: application/json');

require_once '../../conexao.php';

$identificador = trim($_POST['identificador'] ?? '');
$senha         = $_POST['senha'] ?? '';

if (empty($identificador) || empty($senha)) {
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos.']);
    exit;
}

// Seleciona apenas as colunas necessárias — nunca SELECT *
$stmt = mysqli_prepare(
    $conexao,
    "SELECT id_usuario, nome, tipo_usuario, email, senha
     FROM usuario
     WHERE email = ? OR cpf = ?
     LIMIT 1"
);

// Verifica se o prepare falhou (ex: erro de sintaxe SQL, tabela inexistente)
if (!$stmt) {
    error_log('mysqli_prepare falhou: ' . mysqli_error($conexao));
    echo json_encode(['success' => false, 'message' => 'Erro interno. Tente novamente.']);
    exit;
}

mysqli_stmt_bind_param($stmt, 'ss', $identificador, $identificador);
mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$usuario   = mysqli_fetch_assoc($resultado);

if ($usuario && password_verify($senha, $usuario['senha'])) {

    // Regenera o ID de sessão após login — previne session fixation
    session_regenerate_id(true);

    $_SESSION['id']    = $usuario['id_usuario'];
    $_SESSION['nome']  = $usuario['nome'];
    $_SESSION['tipo']  = $usuario['tipo_usuario'];
    $_SESSION['email'] = $usuario['email'];

    // Caminho absoluto a partir da raiz do site — resolve o loop
    // Ajuste '/Elderia/' para o subdiretório real do seu projeto
    $redirect = '/Elderia/perfil/index.html';

    echo json_encode(['success' => true, 'redirect' => $redirect]);

} else {
    // Mensagem genérica — não revela se o e-mail existe ou não
    echo json_encode(['success' => false, 'message' => 'Credenciais inválidas.']);
}

mysqli_stmt_close($stmt);
exit;
?>