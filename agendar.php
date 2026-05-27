<?php

session_start();

require_once 'conexao.php';

if (!isset($_SESSION['id'])) {
    die("Você precisa estar logado.");
}

$id_idoso =
    $_SESSION['id'];

$id_agenda =
    $_POST['id_agenda'];


$sql = "

SELECT *

FROM agenda_disponivel

WHERE id_agenda = ?
AND status = 'livre'

";

$stmt =
    mysqli_prepare($conexao, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_agenda
);

mysqli_stmt_execute($stmt);

$resultado =
    mysqli_stmt_get_result($stmt);

$agenda =
    mysqli_fetch_assoc($resultado);


if (!$agenda) {

    die("Horário indisponível.");
}


$id_profissional =
    $agenda['id_profissional'];

$data_hora =
    $agenda['data_hora'];


$sql_insert = "

INSERT INTO consulta
(id_idoso, id_profissional, data_hora)

VALUES (?, ?, ?)

";

$stmt_insert =
    mysqli_prepare($conexao, $sql_insert);

mysqli_stmt_bind_param(

    $stmt_insert,

    "iis",

    $id_idoso,
    $id_profissional,
    $data_hora
);

if (mysqli_stmt_execute($stmt_insert)) {

    $sql_update = "

    UPDATE agenda_disponivel

    SET status = 'agendado'

    WHERE id_agenda = ?

    ";

    $stmt_update =
        mysqli_prepare(
            $conexao,
            $sql_update
        );

    mysqli_stmt_bind_param(
        $stmt_update,
        "i",
        $id_agenda
    );

    mysqli_stmt_execute($stmt_update);

    echo "

    <script>

        alert(
            'Consulta agendada com sucesso!'
        );

        window.location.href =
            'consulta/index.html';

    </script>

    ";

} else {

    echo "

    <script>

        alert('Erro ao agendar.');

        history.back();

    </script>

    ";
}

?>