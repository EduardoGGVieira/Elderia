<?php
header('Content-Type: application/json');

$host = 'localhost:3307';
$dbname = 'elderia';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    echo json_encode(["erro" => "Erro ao conectar no banco: " . mysqli_connect_error()]);
    exit;
}

$especialidade_buscada = $_GET['especialidade'] ?? '';
$busca_esp = "%" . $especialidade_buscada . "%";

$sql = "SELECT u.id_usuario, u.nome, p.especialidade, p.biografia 
        FROM profissional p
        INNER JOIN usuario u ON p.id_profissional = u.id_usuario
        WHERE p.especialidade LIKE ?";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $busca_esp);
    mysqli_stmt_execute($stmt);
    
    $resultado = mysqli_stmt_get_result($stmt);
    $profissionais = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    
    echo json_encode($profissionais);
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["erro" => "Erro na montagem da busca: " . mysqli_error($conn)]);
}

mysqli_close($conn);
?>