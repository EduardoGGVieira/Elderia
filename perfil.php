<?php
<<<<<<< HEAD
// Inicia a sessão pra saber quem tá logado
session_start();

// Puxa a conexão que a gente já deixou pronta
require_once 'conexao.php';

// Pega o ID do profissional que veio pela URL (ex: perfil.php?id=5)
=======

// Inicia sessão
session_start();

// Conexão com banco
require_once 'conexao.php';

// Pega o ID do profissional pela URL
>>>>>>> parent of 33f97e8 (Disponibilizar horarios e remarcar consultas / Artur - Eduardo / 13.05)
$id_prof = $_GET['id'] ?? null;

if (!$id_prof) {
    die("FALTA ID");
}

<<<<<<< HEAD
// SQL básico pra pegar os dados do cara no banco
// A gente junta a tabela 'usuario' com a 'profissional' pelo ID
$sql = "SELECT u.nome, u.email, p.especialidade, p.biografia, p.localizacao 
        FROM usuario u 
        INNER JOIN profissional p ON u.id_usuario = p.id_profissional 
        WHERE u.id_usuario = ?";
=======

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
>>>>>>> parent of 33f97e8 (Disponibilizar horarios e remarcar consultas / Artur - Eduardo / 13.05)

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_prof);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$prof = mysqli_fetch_assoc($resultado);

// Se não achou o profissional, avisa
if (!$prof) {
    die("Profissional não encontrado no banco!");
}

<<<<<<< HEAD
// Buscar as avaliações deste profissional
$sql_aval = "SELECT a.nota, a.comentario, u.nome, DATE_FORMAT(a.data_avaliacao, '%d/%m/%Y') as data_f 
             FROM avaliacao a 
             JOIN usuario u ON a.id_usuario = u.id_usuario 
             WHERE a.id_profissional = ? 
             ORDER BY a.data_avaliacao DESC";
$stmt_aval = mysqli_prepare($conexao, $sql_aval);
mysqli_stmt_bind_param($stmt_aval, "i", $id_prof);
mysqli_stmt_execute($stmt_aval);
$res_aval = mysqli_stmt_get_result($stmt_aval);
=======

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

>>>>>>> parent of 33f97e8 (Disponibilizar horarios e remarcar consultas / Artur - Eduardo / 13.05)
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Perfil de <?php echo $prof['nome']; ?></title>
    <link rel="stylesheet" href="index.css">
    <style>
<<<<<<< HEAD
        .perfil-box {
            max-width: 600px;
=======

        body {
            background: #f4f7f6;
        }

        .perfil-box {

            max-width: 600px;

>>>>>>> parent of 33f97e8 (Disponibilizar horarios e remarcar consultas / Artur - Eduardo / 13.05)
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
<<<<<<< HEAD
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
=======

            box-shadow:
                0 5px 15px rgba(0,0,0,0.1);
>>>>>>> parent of 33f97e8 (Disponibilizar horarios e remarcar consultas / Artur - Eduardo / 13.05)
        }

        .btn-agendar {
            background: #00a6ce;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
<<<<<<< HEAD
            margin-top: 15px;
        }

        .btn-avaliar {
            background: #00a6ce;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 15px;
            text-decoration: none;
            display: inline-block;
=======

            margin-top: 20px;
>>>>>>> parent of 33f97e8 (Disponibilizar horarios e remarcar consultas / Artur - Eduardo / 13.05)
        }



        .avaliacoes-secao {
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

<<<<<<< HEAD
        .card-avaliacao {
            background: #f9f9f9;
=======
        .select-horario {

            width: 100%;

            padding: 10px;

            margin-top: 10px;

>>>>>>> parent of 33f97e8 (Disponibilizar horarios e remarcar consultas / Artur - Eduardo / 13.05)
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;

<<<<<<< HEAD
        }

        .estrela-ativa {
            color: #ffc107;
        }

        .estrela-inativa {
            color: #ccc;
        }
=======
            border: 1px solid #ccc;
        }

>>>>>>> parent of 33f97e8 (Disponibilizar horarios e remarcar consultas / Artur - Eduardo / 13.05)
    </style>
</head>

<body style="background: #f4f7f6;">

    <div class="perfil-box">
        <a href="index.html" style="text-decoration: none; color: #00a6ce;">← Voltar para a Home</a>

<<<<<<< HEAD
        <?php
        if (isset($_SESSION['msg'])) {
            echo $_SESSION['msg'];
            unset($_SESSION['msg']);
        }
        ?>
=======
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
>>>>>>> parent of 33f97e8 (Disponibilizar horarios e remarcar consultas / Artur - Eduardo / 13.05)

        <h1 style="margin-top: 20px;"><?php echo htmlspecialchars($prof['nome']); ?></h1>
        <p style="color: #00a6ce; font-weight: bold;"><?php echo $prof['especialidade']; ?></p>

        <div style="margin-top: 20px;">
            <strong>Sobre:</strong>
<<<<<<< HEAD
            <p><?php echo $prof['biografia'] ?: "Nenhuma biografia cadastrada."; ?></p>
        </div>

        <div style="margin-top: 10px;">
            <strong>Localização:</strong>
            <p><?php echo $prof['localizacao'] ?: "Não informado."; ?></p>
        </div>


=======

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
>>>>>>> parent of 33f97e8 (Disponibilizar horarios e remarcar consultas / Artur - Eduardo / 13.05)

        <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
            <a href="avaliacao/avaliar.php?id=<?php echo $id_prof; ?>" class="btn-avaliar"> Avaliar Profissional</a>
        </div>

        <!-- Parte de agendar a consulta -->
        <div class="formulario-agendar">
            <h3>Agendar uma Consulta</h3>

            <?php if (isset($_SESSION['id'])): ?>
                <!-- Se tiver logado, mostra o form de agendamento -->
                <form action="agendar.php" method="POST">
                    <input type="hidden" name="id_profissional" value="<?php echo $id_prof; ?>">

<<<<<<< HEAD
                    <label>Escolha a Data e Hora:</label><br>
                    <input type="datetime-local" name="data_hora" required
                        style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc;">
=======
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
>>>>>>> parent of 33f97e8 (Disponibilizar horarios e remarcar consultas / Artur - Eduardo / 13.05)

                    <button type="submit" class="btn-agendar">Confirmar Agendamento</button>
                    <p style="font-size: 0.8rem; color: #777; margin-top: 10px;">* Seus dados serão compartilhados com o
                        profissional da saúde.</p>
                </form>
            <?php else: ?>
<<<<<<< HEAD
                <!-- Se não tiver logado, manda pro login -->
                <p style="color: red;"><strong>Atenção:</strong> Você precisa estar logado para agendar.</p>
                <a href="conta/login/login.html" style="color: #00a6ce;">Clique aqui para fazer login</a>
=======

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

>>>>>>> parent of 33f97e8 (Disponibilizar horarios e remarcar consultas / Artur - Eduardo / 13.05)
            <?php endif; ?>
        </div>

        <!--mostra as avaliacao -->
        <div class="avaliacoes-secao">
            <h3>Avaliações dos Usuários</h3>
            <?php if (mysqli_num_rows($res_aval) > 0): ?>
                <?php while ($aval = mysqli_fetch_assoc($res_aval)): ?>
                    <div class="card-avaliacao">
                        <div style="display: flex; justify-content: space-between;">
                            <strong><?php echo htmlspecialchars($aval['nome']); ?></strong>
                            <span style="font-size: 0.8rem; color: #777;"><?php echo $aval['data_f']; ?></span>
                        </div>
                        <div style="margin: 5px 0;">
                            <?php
                            $nota = (int) $aval['nota'];
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $nota) {
                                    echo "<span class='estrela-ativa'>★</span>";
                                } else {
                                    echo "<span class='estrela-inativa'>☆</span>";
                                }
                            }
                            ?>
                        </div>
                        <p style="font-style: italic; margin-top: 5px; color: #444;">
                            "<?php echo nl2br(htmlspecialchars($aval['comentario'] ?: 'Sem comentário.')); ?>"
                        </p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #777; font-style: italic;">Este profissional ainda não recebeu avaliações.</p>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>