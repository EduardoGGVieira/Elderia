<?php

session_start();

require_once 'conexao.php';

if (!isset($_SESSION['id'])) {
    die("Você precisa estar logado.");
}

if ($_SESSION['tipo'] !== 'idoso') {
    die("Apenas idosos podem agendar consultas.");
}

$id_idoso = $_SESSION['id'];
$id_agenda = $_POST['id_agenda'] ?? null;

if (empty($id_agenda)) {
    die("Horário não informado.");
}

$sql_agenda = "
SELECT id_profissional, data_hora
FROM agenda_disponivel
WHERE id_agenda = ?
AND status = 'livre'
";

$stmt_agenda = mysqli_prepare($conexao, $sql_agenda);

if (!$stmt_agenda) {
    die("Erro ao preparar busca da agenda.");
}

mysqli_stmt_bind_param($stmt_agenda, "i", $id_agenda);

mysqli_stmt_execute($stmt_agenda);

$resultado = mysqli_stmt_get_result($stmt_agenda);

$agenda = mysqli_fetch_assoc($resultado);

if (!$agenda) {
    die("Horário indisponível.");
}

$id_profissional = $agenda['id_profissional'];
$data_hora = $agenda['data_hora'];

$sql_insert = "
INSERT INTO consulta
(id_idoso, id_profissional, data_hora, status)
VALUES
(?, ?, ?, 'agendada')
";

$stmt_insert = mysqli_prepare($conexao, $sql_insert);

if (!$stmt_insert) {
    die("Erro ao preparar consulta.");
}

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

    $stmt_update = mysqli_prepare($conexao, $sql_update);

    if ($stmt_update) {

        mysqli_stmt_bind_param(
            $stmt_update,
            "i",
            $id_agenda
        );

        mysqli_stmt_execute($stmt_update);

        mysqli_stmt_close($stmt_update);
    }

    echo "
    <script>
        alert('Consulta agendada com sucesso!');
        window.location.href = 'consulta/index.html';
    </script>
    ";

} else {

    echo "
    <script>
        alert('Erro ao agendar consulta.');
        history.back();
    </script>
    ";
}

mysqli_stmt_close($stmt_agenda);
mysqli_stmt_close($stmt_insert);

mysqli_close($conexao);

?>