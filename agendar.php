<?php
// Arquivo para salvar o agendamento no banco

session_start();

// Conexão com banco
require_once 'conexao.php';

// Verifica login
if (!isset($_SESSION['id'])) {
    die("Você precisa estar logado.");
}

// Apenas idosos podem agendar
if ($_SESSION['tipo'] !== 'idoso') {
    die("Apenas idosos podem agendar consultas.");
}

// Dados da sessão e formulário
$id_idoso = $_SESSION['id'];
$id_agenda = $_POST['id_agenda'] ?? null;

// Verifica se veio o ID da agenda
if (!$id_agenda) {
    die("Horário não informado.");
}

// Busca agenda disponível
$sql_agenda = "
    SELECT *
    FROM agenda_disponivel
    WHERE id_agenda = ?
    AND status = 'livre'
";

$stmt_agenda = mysqli_prepare($conexao, $sql_agenda);

if (!$stmt_agenda) {
    die("Erro no banco: " . mysqli_error($conexao));
}

mysqli_stmt_bind_param($stmt_agenda, "i", $id_agenda);

mysqli_stmt_execute($stmt_agenda);

$resultado = mysqli_stmt_get_result($stmt_agenda);

$agenda = mysqli_fetch_assoc($resultado);

// Verifica se o horário existe e está livre
if (!$agenda) {
    die("Horário indisponível.");
}

// Dados da agenda
$id_profissional = $agenda['id_profissional'];
$data_hora = $agenda['data_hora'];

// Insere consulta
$sql_insert = "
    INSERT INTO consulta
    (id_idoso, id_profissional, data_hora, status)
    VALUES (?, ?, ?, 'agendada')
";

$stmt_insert = mysqli_prepare($conexao, $sql_insert);

if (!$stmt_insert) {
    die("Erro ao preparar consulta: " . mysqli_error($conexao));
}

mysqli_stmt_bind_param(
    $stmt_insert,
    "iis",
    $id_idoso,
    $id_profissional,
    $data_hora
);

// Executa insert
if (mysqli_stmt_execute($stmt_insert)) {

    // Atualiza agenda
    $sql_update = "
        UPDATE agenda_disponivel
        SET status = 'agendado'
        WHERE id_agenda = ?
    ";

    $stmt_update = mysqli_prepare($conexao, $sql_update);

    mysqli_stmt_bind_param(
        $stmt_update,
        "i",
        $id_agenda
    );

    mysqli_stmt_execute($stmt_update);

    echo "
    <div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
        <h2 style='color:green;'>✅ Consulta agendada com sucesso!</h2>
        <p>O profissional já recebeu seu pedido.</p>

        <br>

        <a href='index.html'
           style='color:#00a6ce; text-decoration:none; font-weight:bold;'>
           Voltar para o Início
        </a>
    </div>

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

    echo "Erro no banco: " . mysqli_error($conexao);
}

// Fecha conexões
mysqli_stmt_close($stmt_agenda);
mysqli_stmt_close($stmt_insert);

if (isset($stmt_update)) {
    mysqli_stmt_close($stmt_update);
}

mysqli_close($conexao);
?>