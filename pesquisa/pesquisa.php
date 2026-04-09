<?php
include_once '../conexao.php';;

$especialidade_buscada = $_GET['especialidade'] ?? '';
$localizacao = $_GET['localizacao'] ?? ''; // Nova variável

$busca_esp = "%" . $especialidade_buscada . "%";
$busca_local = "%" . $localizacao . "%";

$sql = "SELECT u.nome, p.especialidade, p.biografia, p.localizacao as cidade, p.id_profissional,
        (SELECT GROUP_CONCAT(CONCAT(dia_semana, ':', horario) ORDER BY dia_semana, horario ASC) 
         FROM agenda_disponivel 
         WHERE id_profissional = p.id_profissional) as agenda
        FROM profissional p
        INNER JOIN usuario u ON p.id_profissional = u.id_usuario
        WHERE p.especialidade LIKE ? AND p.localizacao LIKE ?";

$stmt = mysqli_prepare($conexao, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $busca_esp, $busca_local);
    mysqli_stmt_execute($stmt);
    
    $resultado = mysqli_stmt_get_result($stmt);
    $profissionais = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($profissionais);
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["erro" => "Erro: " . mysqli_error($conexao)]);
}

mysqli_close($conexao);
?>