<?php // php/conexao.php

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