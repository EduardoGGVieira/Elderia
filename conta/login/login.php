<?php
session_start();
 
// --- Conexão com o banco de dados ---
$conn = mysqli_connect('localhost', 'root', '', 'elderia');
 
if (!$conn) {
    die("Erro na conexão: " . mysqli_connect_error());
}
 
mysqli_set_charset($conn, 'utf8');
 
// --- Lógica de Login ---
$erro = '';
$sucesso = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
 
    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        // Busca o usuário pelo e-mail na tabela Usuario
        $stmt = mysqli_prepare($conn, "SELECT * FROM Usuario WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $usuario = mysqli_fetch_assoc($resultado);
 
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Login bem-sucedido
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo_usuario'];
 
            // Redireciona conforme o tipo
            if ($usuario['tipo_usuario'] === 'profissional') {
                header('Location: ../dashboard/profissional.php');
            } else {
                header('Location: ../dashboard/idoso.php');
            }
            exit;
        } else {
            $erro = 'E-mail ou senha incorretos. Verifique seus dados e tente novamente.';
        }
    }
}
?>