<?php
header('Content-Type: application/json');
require_once 'conexao.php';

if (!$conexao) {
    echo json_encode([]);
    exit;
}


$sql = "SELECT u.nome, p.especialidade, p.biografia, p.id_profissional 
        FROM usuario u 
        INNER JOIN profissional p ON u.id_usuario = p.id_profissional 

        
        WHERE u.tipo_usuario = 'profissional' AND p.visibilidade in (0,1)";



$result = mysqli_query($conexao, $sql);
$profissionais = [];

while ($row = mysqli_fetch_assoc($result)) {
    $profissionais[] = $row;
}

echo json_encode($profissionais);
?>