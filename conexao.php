<?php // php/conexao.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Timeout de 10 segundos para teste
$tempo_limite = 60;
if (isset($_SESSION['id'])) {
    if (isset($_SESSION['ultimo_acesso']) && (time() - $_SESSION['ultimo_acesso'] > $tempo_limite)) {
        // Passou do limite: desloga o usuário e limpa a sessão
        $_SESSION = array();
        session_destroy();

        // Verifica se é uma requisição AJAX/API (fetch ou XMLHttpRequest) para não retornar HTML no lugar de JSON
        $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
            || (strpos($_SERVER['SCRIPT_NAME'], '_api.php') !== false)
            || (strpos($_SERVER['SCRIPT_NAME'], 'get_session.php') !== false)
            || (strpos($_SERVER['SCRIPT_NAME'], 'get_dados_completo.php') !== false);

        if (!$is_ajax) {
            header("Location: /Elderia/index.html");
            exit;
        }
    } else {
        // Atualiza o tempo do último clique do usuário apenas se não expirou
        $_SESSION['ultimo_acesso'] = time();
    }
}

//PUC TEM QUE USAR ESSE
// $servidor = 'localhost:3307'; 
$servidor = 'localhost:3306';
$usuario = 'root';
$senha = '';
$nome_banco = 'elderia';

// Criar a conexão
$conexao = new mysqli($servidor, $usuario, $senha, $nome_banco);
// Verificar se a conexão falhou
if ($conexao->connect_error) {
    header('Content-type: application/json; charset=utf-8');
    echo json_encode([


        // nok = nao ok
        'status' => 'nok',
        'mensagem' => 'Falha na conexão: ' . $conexao->connect_error,
        'data' => []
    ]);
    exit;
}

$conexao->set_charset('utf8mb4');
?>