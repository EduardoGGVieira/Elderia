<?php
header('Content-Type: application/json');
require_once 'conexao.php';

if (!$conexao) {
    echo json_encode([]);
    exit;
}

// Busca profissionais, trazendo o nome da tabela usuario e especialidade da tabela profissional
$sql = "SELECT u.nome, p.especialidade, p.biografia, p.id_profissional 
        FROM usuario u 
        INNER JOIN profissional p ON u.id_usuario = p.id_profissional 
        WHERE u.tipo_usuario = 'profissional' AND p.visibilidade in (0,1)";

// no futuro o profissional pode escolher se quer ou não aparecer na lista, por isso a coluna visibilidade na tabela profissional, para filtrar apenas os que querem aparecer publicamente

$result = mysqli_query($conexao, $sql);
$profissionais = [];

while ($row = mysqli_fetch_assoc($result)) {
    $profissionais[] = $row;
}

echo json_encode($profissionais);
?>