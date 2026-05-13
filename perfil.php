<?php

// Inicia sessão
session_start();

// Conexão com banco
require_once 'conexao.php';

// Pega o ID do profissional pela URL
$id_prof = $_GET['id'] ?? null;

if (!$id_prof) {
    die("Profissional não encontrado.");
}


// ===============================
// BUSCA DADOS DO PROFISSIONAL
// ===============================

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


// ===============================
// BUSCA HORÁRIOS DISPONÍVEIS
// ===============================

$sql_horarios = "
SELECT *
FROM agenda_disponivel
WHERE id_profissional = ?
ORDER BY horario ASC
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

            max-width: 600px;

            margin: 50px auto;

            background: white;

            padding: 30px;

            border-radius: 15px;

            box-shadow:
                0 5px 15px rgba(0,0,0,0.1);
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
        }

        .formulario-agendar {

            margin-top: 30px;

            border-top: 1px solid #eee;

            padding-top: 20px;
        }

        .select-horario {

            width: 100%;

            padding: 10px;

            margin-top: 10px;

            border-radius: 8px;

            border: 1px solid #ccc;
        }

    </style>

</head>

<body>

    <div class="perfil-box">

        <a
            href="index.html"

            style="
                text-decoration: none;
                color: #00a6ce;
            "
        >

            ← Voltar para a Home

        </a>


        <h1 style="margin-top: 20px;">

            <?php echo $prof['nome']; ?>

        </h1>


        <p style="
            color: #00a6ce;
            font-weight: bold;
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


        <div style="margin-top: 10px;">

            <strong>Localização:</strong>

            <p>

                <?php
                echo $prof['localizacao']
                ?: "Não informado.";
                ?>

            </p>

        </div>


        <!-- ÁREA DE AGENDAMENTO -->
        <div class="formulario-agendar">

            <h3>Agendar uma Consulta</h3>

            <?php if (isset($_SESSION['id'])): ?>

                <form
                    action="agendar.php"
                    method="POST"
                >

                    <!-- ID DO PROFISSIONAL -->
                    <input
                        type="hidden"
                        name="id_profissional"
                        value="<?php echo $id_prof; ?>"
                    >

                    <label>
                        Escolha um horário disponível:
                    </label>

                    <select
                        name="id_agenda"
                        class="select-horario"
                        required
                    >

                        <option value="">
                            Selecione um horário
                        </option>

                        <?php
                        while($horario =
                            mysqli_fetch_assoc(
                                $resultado_horarios
                            )):
                        ?>

                            <option
                                value="<?php
                                echo $horario['id_agenda'];
                                ?>"
                            >

                                <?php
                                echo $horario['dia_semana'];
                                ?>

                                -

                                <?php
                                echo substr(
                                    $horario['horario'],
                                    0,
                                    5
                                );
                                ?>

                            </option>

                        <?php endwhile; ?>

                    </select>


                    <button
                        type="submit"
                        class="btn-agendar"
                    >

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

                <!-- NÃO LOGADO -->
                <p style="color: red;">

                    <strong>Atenção:</strong>

                    Você precisa estar logado
                    para agendar.

                </p>

                <a
                    href="conta/login/login.html"

                    style="
                        color: #00a6ce;
                    "
                >

                    Clique aqui para fazer login

                </a>

            <?php endif; ?>

        </div>

    </div>

</body>

</html>