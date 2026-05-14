<?php

session_start();

include('../../conexao.php');


if (!isset($_SESSION['id'])) {
    die('Usuário não autenticado.');
}


if ($_SESSION['tipo'] !== 'profissional') {
    die('Acesso negado.');
}


$id_profissional =
    $_SESSION['id'];

$data_hora =
    $_POST['data_hora'];


if (empty($data_hora)) {
    die('Preencha todos os campos.');
}


$sql = "

INSERT INTO agenda_disponivel
(id_profissional, data_hora)

VALUES (?, ?)

";


$stmt =
    mysqli_prepare($conexao, $sql);


if ($stmt) {

    mysqli_stmt_bind_param(

        $stmt,

        "is",

        $id_profissional,
        $data_hora
    );

    if (mysqli_stmt_execute($stmt)) {

        echo
        'Horário disponibilizado com sucesso!';

    } else {

        echo
        'Erro ao salvar horário.';
    }

    mysqli_stmt_close($stmt);

} else {

    echo
    'Erro na preparação da query.';
}

mysqli_close($conexao);

?>