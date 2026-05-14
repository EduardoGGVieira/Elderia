<?php

session_start();
include('../../conexao.php');

if (!isset($_SESSION['id'])) {
    die('Usuário não autenticado.');
}

if ($_SESSION['tipo'] !== 'profissional') {
    die('Acesso negado.');
}

$id_profissional = $_SESSION['id'];

$dia_semana = $_POST['dia_semana'];
$horario = $_POST['horario'];

if (empty($dia_semana) || empty($horario)) {
    die('Preencha todos os campos.');
}

$sql = "INSERT INTO agenda_disponivel
(id_profissional, dia_semana, horario)
VALUES (?, ?, ?)";

$stmt = mysqli_prepare($conexao, $sql);

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "iss",
        $id_profissional,
        $dia_semana,
        $horario
    );

    if (mysqli_stmt_execute($stmt)) {

        echo 'Horário disponibilizado com sucesso!';

    } else {

        echo 'Erro ao salvar horário.';
    }

    mysqli_stmt_close($stmt);

} else {

    echo 'Erro na preparação da query.';
}

mysqli_close($conexao);

?>