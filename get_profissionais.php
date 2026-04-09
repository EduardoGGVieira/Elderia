<?php
// Atualizado por André Felipe
header('Content-Type: application/json');

// Incluindo a conexão centralizada
// Corrigido por André Felipe
require_once 'conexao.php';

// Busca profissionais, trazendo o nome da tabela usuario e especialidade da tabela profissional
// Atualizado por André Felipe: Sincronizado com os nomes de colunas corretos
$sql = "SELECT u.nome, p.especialidade, p.biografia, p.id_profissional 
        FROM usuario u 
        INNER JOIN profissional p ON u.id_usuario = p.id_profissional 
        WHERE u.tipo_usuario = 'profissional' AND p.visibilidade = 1";

$result = mysqli_query($conexao, $sql);
$profissionais = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $profissionais[] = $row;
    }
}

echo json_encode($profissionais);
?>