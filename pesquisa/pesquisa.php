<?php
/* 
   Arquivo de busca de profissionais
   Corrigido para usar a conexão centralizada e preparado contra SQL Injection
*/

header('Content-Type: application/json');

// 1. Incluindo a conexão centralizada (subindo uma pasta para achar o conexao.php)
require_once '../conexao.php';

// 2. Verifica se a conexão está funcionando (usando a variável do conexao.php)
if (!$conexao) {
    echo json_encode(["erro" => "Erro ao conectar no banco de dados."]);
    exit;
}

// 3. Pega o termo de busca enviado via GET (ex: ?especialidade=fisioterapia)
$especialidade_buscada = $_GET['especialidade'] ?? '';
$busca_esp = "%" . $especialidade_buscada . "%"; // Adiciona os % para o comando LIKE do SQL

// 4. SQL: Busca o nome do usuário (tabela usuario) e a especialidade (tabela profissional)
$sql = "SELECT u.id_usuario, u.nome, p.especialidade, p.biografia 
        FROM profissional p
        INNER JOIN usuario u ON p.id_profissional = u.id_usuario
        WHERE p.especialidade LIKE ?";

// 5. Prepara a consulta para segurança máxima
$stmt = mysqli_prepare($conexao, $sql);

if ($stmt) {
    // 's' significa que o parâmetro é uma String
    mysqli_stmt_bind_param($stmt, "s", $busca_esp);
    mysqli_stmt_execute($stmt);
    
    // Pega o resultado da execução
    $resultado = mysqli_stmt_get_result($stmt);
    
    // Transforma o resultado em um Array associativo
    $profissionais = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    
    // Retorna os dados como JSON (o JavaScript vai ler isso)
    echo json_encode($profissionais);
    
    // Fecha o statement
    mysqli_stmt_close($stmt);
} else {
    // Se der algum erro na consulta
    echo json_encode(["erro" => "Erro na montagem da busca."]);
}

// 6. Fecha a conexão com o banco
mysqli_close($conexao);
?>