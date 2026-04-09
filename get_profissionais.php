<?php
header('Content-Type: application/json');
$conn = mysqli_connect('localhost:3307', 'root', '', 'elderia'); // Use a sua porta (3306 ou 3307)

if (!$conn) {
    echo json_encode([]);
    exit;
}

// Busca profissionais, trazendo o nome da tabela usuario e especialidade da tabela profissional
$sql = "SELECT u.nome, p.especialidade, p.biografia, p.id_profissional 
        FROM usuario u 
        INNER JOIN profissional p ON u.id_usuario = p.id_profissional 
        WHERE u.tipo_usuario = 'profissional' AND p.visibilidade = 1";

$result = mysqli_query($conn, $sql);
$profissionais = [];

while ($row = mysqli_fetch_assoc($result)) {
    $profissionais[] = $row;
}

echo json_encode($profissionais);
?>