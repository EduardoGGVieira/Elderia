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

    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Consulta Agendada - Elderia</title>

        <link rel="stylesheet" href="index.css">

        <style>
            body {
                background: #f4f7f6;
            }

            .conteiner-sucesso {
                max-width: 550px;
                margin: 80px auto;
                padding: 20px;
            }

            .card-sucesso {
                background: white;
                border-radius: 12px;
                padding: 35px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
                border: 2px solid #CCCCCC;
                text-align: center;
            }

            .message-box {
                width: 100%;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 8px;
                font-size: 16px;
                animation: aparecer 0.3s ease;
                box-sizing: border-box;
            }

            .message-box.success {
                background: #d4edda;
                color: #155724;
                border: 1px solid #28a745;
            }

            .btn-ok {
                width: 100%;
                padding: 15px;
                font-size: 1.05rem;
                font-weight: bold;
                background-color: #E36414;
                color: white;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                transition: 0.3s;
                text-decoration: none;
                display: inline-block;
                margin-top: 10px;
                box-sizing: border-box;
            }

            .btn-ok:hover {
                opacity: 0.95;
                transform: scale(1.01);
            }

            @keyframes aparecer {

                from {
                    opacity: 0;
                    transform: translateY(-5px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }

            }
        </style>
    </head>

    <body>

        <main class="conteiner-sucesso">

            <div class="card-sucesso">

                <h2>Consulta Confirmada</h2>

                <div class="message-box success">
                    Sua consulta foi agendada com sucesso!
                </div>

                <p style="color: #555; margin-bottom: 25px;">
                    O profissional já pode visualizar o seu agendamento.
                </p>

                <a href="index.html" class="btn-ok">
                    OK
                </a>

            </div>

        </main>

    </body>

    </html>

    <?php

} else {
    ?>

    <!DOCTYPE html>
    <html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erro no Agendamento - Elderia</title>

        <link rel="stylesheet" href="index.css">

        <style>
            body {
                background: #f4f7f6;
            }

            .conteiner-erro {
                max-width: 550px;
                margin: 80px auto;
                padding: 20px;
            }

            .card-erro {
                background: white;
                border-radius: 12px;
                padding: 35px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
                border: 2px solid #CCCCCC;
                text-align: center;
            }

            .message-box {
                width: 100%;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 8px;
                font-size: 16px;
                animation: aparecer 0.3s ease;
                box-sizing: border-box;
            }

            .message-box.error {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #dc3545;
            }

            .btn-ok {
                width: 100%;
                padding: 15px;
                font-size: 1.05rem;
                font-weight: bold;
                background-color: #E36414;
                color: white;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                transition: 0.3s;
                text-decoration: none;
                display: inline-block;
                margin-top: 10px;
                box-sizing: border-box;
            }

            .btn-ok:hover {
                opacity: 0.95;
                transform: scale(1.01);
            }

            @keyframes aparecer {

                from {
                    opacity: 0;
                    transform: translateY(-5px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }

            }
        </style>
    </head>

    <body>

        <main class="conteiner-erro">

            <div class="card-erro">

                <h2>Erro ao Agendar</h2>

                <div class="message-box error">
                    Não foi possível concluir o agendamento.
                </div>

                <p style="color: #555; margin-bottom: 25px;">
                    Tente novamente em alguns instantes.
                </p>

                <a href="javascript:history.back()" class="btn-ok">
                    Voltar
                </a>

            </div>

        </main>

    </body>

    </html>

    <?php
}
?>

?>