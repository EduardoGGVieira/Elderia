<?php

session_start();

require_once 'conexao.php';

$id_prof = $_GET['id'] ?? null;

if (!$id_prof) {
    die("Profissional não encontrado.");
}

$sql = "
SELECT 
    u.nome,
    u.email,

    p.especialidade,
    p.biografia,
    p.localizacao

FROM usuario u

INNER JOIN profissional p
ON u.id_usuario = p.id_profissional

WHERE u.id_usuario = ?
";

$stmt = mysqli_prepare($conexao, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_prof
);

mysqli_stmt_execute($stmt);

$resultado =
    mysqli_stmt_get_result($stmt);

$prof =
    mysqli_fetch_assoc($resultado);


// Se não encontrar profissional
if (!$prof) {
    die("Profissional não encontrado no banco.");
}


$sql_horarios = "

SELECT *

FROM agenda_disponivel

WHERE id_profissional = ?
AND data_hora > NOW()
AND status = 'livre'

ORDER BY data_hora ASC

";

$stmt_horarios =
    mysqli_prepare($conexao, $sql_horarios);

mysqli_stmt_bind_param(
    $stmt_horarios,
    "i",
    $id_prof
);

mysqli_stmt_execute($stmt_horarios);

$resultado_horarios =
    mysqli_stmt_get_result($stmt_horarios);

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <title>
        Perfil de <?php echo $prof['nome']; ?>
    </title>

    <link rel="stylesheet" href="index.css">

    <style>
        body {
            background: #f4f7f6;
        }

        .perfil-box {

            max-width: 650px;

            margin: 50px auto;

            background: white;

            padding: 30px;

            border-radius: 15px;

            box-shadow:
                0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-agendar {

            background: #00a6ce;

            color: white;

            padding: 12px;

            border: none;

            border-radius: 8px;

            cursor: pointer;

            font-weight: bold;

            width: 100%;

            margin-top: 20px;

            transition: 0.2s;
        }

        .btn-agendar:hover {

            transform: scale(1.01);

            opacity: 0.95;
        }

        .formulario-agendar {

            margin-top: 30px;

            border-top: 1px solid #eee;

            padding-top: 20px;
        }

        .select-horario {

            width: 100%;

            padding: 12px;

            margin-top: 10px;

            border-radius: 8px;

            border: 1px solid #ccc;

            font-size: 15px;
        }

        .sem-horarios {

            margin-top: 15px;

            color: #999;

            font-style: italic;
        }
    </style>

</head>

<body>

    <div class="perfil-box">

        <a href="index.html" style="
                text-decoration: none;
                color: #00a6ce;
            ">

            ← Voltar para a Home

        </a>


        <h1 style="margin-top: 20px;">

            <?php echo $prof['nome']; ?>

        </h1>


        <p style="
            color: #00a6ce;
            font-weight: bold;
            font-size: 18px;
        ">

            <?php echo $prof['especialidade']; ?>

        </p>


        <div style="margin-top: 20px;">

            <strong>Sobre:</strong>

            <p>

                <?php
                echo $prof['biografia']
                    ?: "Nenhuma biografia cadastrada.";
                ?>

            </p>

        </div>


        <div style="margin-top: 15px;">

            <strong>Localização:</strong>

            <p>

                <?php
                echo $prof['localizacao']
                    ?: "Não informado.";
                ?>

            </p>

        </div>


        <div class="formulario-agendar">

            <h3>Agendar uma Consulta</h3>

            <?php if (isset($_SESSION['id'])): ?>

                <?php if (mysqli_num_rows($resultado_horarios) > 0): ?>

                    <form action="agendar.php" method="POST">

                        <input type="hidden" name="id_profissional" value="<?php echo $id_prof; ?>">

                        <label>
                            Escolha um horário disponível:
                        </label>

                        <select name="id_agenda" class="select-horario" required>

                            <option value="">
                                Selecione um horário
                            </option>

                            <?php
                            while (
                                $horario =
                                mysqli_fetch_assoc(
                                    $resultado_horarios
                                )
                            ):
                                ?>

                                <option value="<?php
                                echo $horario['id_agenda'];
                                ?>">

                                    <?php

                                    echo date(

                                        'd/m/Y H:i',

                                        strtotime(
                                            $horario['data_hora']
                                        )
                                    );

                                    ?>

                                </option>

                            <?php endwhile; ?>

                        </select>


                        <button type="submit" class="btn-agendar">

                            Confirmar Agendamento

                        </button>


                        <p style="
                            font-size: 0.8rem;
                            color: #777;
                            margin-top: 10px;
                        ">

                            * Seus dados serão
                            compartilhados com o
                            profissional da saúde.

                        </p>

                    </form>

                <?php else: ?>

                    <p class="sem-horarios">

                        Este profissional ainda não
                        disponibilizou horários.

                    </p>

                <?php endif; ?>

            <?php else: ?>

                <p style="color: red;">

                    <strong>Atenção:</strong>

                    Você precisa estar logado
                    para agendar.

                </p>

                <a href="conta/login/login.html" style="
                        color: #00a6ce;
                    ">

                    Clique aqui para fazer login

                </a>

            <?php endif; ?>

        </div>

    </div>

</body>

</html>