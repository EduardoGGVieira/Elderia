<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../conexao.php';

if (!$conexao) {
    echo json_encode(["erro" => "Erro ao conectar no banco de dados."]);
    exit;
}

$especialidade_buscada = $_GET['especialidade'] ?? '';
$localizacao = $_GET['localizacao'] ?? '';
$busca_esp = "%" . $especialidade_buscada . "%";
$busca_local = "%" . $localizacao . "%";

$sql = "SELECT u.nome, p.especialidade, p.biografia, p.localizacao, p.id_profissional,
        (SELECT GROUP_CONCAT(DATE_FORMAT(data_hora, '%d/%m/%Y às %H:%i') ORDER BY data_hora ASC SEPARATOR ',') 
         FROM agenda_disponivel 
         WHERE id_profissional = p.id_profissional AND status = 'livre') as agenda
        FROM profissional p
        INNER JOIN usuario u ON p.id_profissional = u.id_usuario
        WHERE p.especialidade LIKE ? AND p.localizacao LIKE ?";

$stmt = mysqli_prepare($conexao, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $busca_esp, $busca_local);
    mysqli_stmt_execute($stmt);
    
    $resultado = mysqli_stmt_get_result($stmt);
    $profissionais = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    
    echo json_encode($profissionais);
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["erro" => "Erro: " . mysqli_error($conexao)]);
}

mysqli_close($conexao);
?>