<?php // php/conexao.php
// Atualizado por André Felipe

// Configuração da conexão com o banco de dados
// Corrigido por André Felipe: Alterado para porta 3306 (padrão do MariaDB no XAMPP)
$servidor = 'localhost:3306'; 
$usuario = 'root'; 
$senha = ''; 
$nome_banco = 'elderia';

// Criar a conexão
// Corrigido por André Felipe
$conexao = new mysqli($servidor, $usuario, $senha, $nome_banco);

// Verificar se a conexão falhou
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