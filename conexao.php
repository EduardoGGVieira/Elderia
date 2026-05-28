<?php 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$tempo_limite = 300;
if (isset($_SESSION['id'])) {
    if (isset($_SESSION['ultimo_acesso']) && (time() - $_SESSION['ultimo_acesso'] > $tempo_limite)) {
        
        $_SESSION = array();
        session_destroy();

        
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
        
        $_SESSION['ultimo_acesso'] = time();
    }
}

; 
$servidor = 'localhost:3307';
$usuario = 'root';
$senha = '';
$nome_banco = 'elderia';


$conexao = new mysqli($servidor, $usuario, $senha, $nome_banco);

if ($conexao->connect_error) {
    header('Content-type: application/json; charset=utf-8');
    echo json_encode([


        
        'status' => 'nok',
        'mensagem' => 'Falha na conexão: ' . $conexao->connect_error,
        'data' => []
    ]);
    exit;
}

$conexao->set_charset('utf8mb4');
?>