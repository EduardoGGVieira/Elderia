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
    // Corrigido por André Felipe: Usando 'id_usuario' conforme o banco
    $_SESSION['usuario_id']   = $usuario['id_usuario'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_tipo'] = $usuario['tipo_usuario'];

    // Redirecionamento corrigido para a pasta de perfil (já que dashboard não existe)
    // Atualizado por André Felipe
    $redirect = '../../perfil/index.html'; 

    echo json_encode(['success' => true, 'redirect' => $redirect]);
} else {
    echo json_encode(['success' => false, 'message' => 'E-mail/CPF ou senha incorretos.']);
}
exit;
?>








/* essa é a verificção de session do Suspect, PRECISA SER FEITO ASSIM.
<?php
session_start();
include("../../conexao.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    // Busca o usuário no banco
    $sql = "SELECT id, email, senha, nome, tipo FROM usuario WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 0) {
        echo "<p style='color:red;'>Usuário não cadastrado.</p>";
        echo "<a href='../cadastro'>Cadastre-se aqui</a>";
    } else {
        $user = mysqli_fetch_assoc($result);

        // Verifica a senha
        if (password_verify($senha, $user['senha'])) {
            $_SESSION['id'] = $user['id']; // Salva a caceta do ID
            $_SESSION['tipo'] = $user['tipo']; // Salva tipo
            $_SESSION['nome'] = $user['nome']; // Salva nome
            $_SESSION['email'] = $user['email']; // Salva email
            echo "correto";
            header("Location: ../../");
            exit;
        } else {
            echo "<p style='color:red;'>Senha incorreta.</p>";
        }
    }
}
*/