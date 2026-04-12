<?php
// Arquivo pra salvar o agendamento no banco
// Bem simples pra não complicar a vida

session_start();

// Puxa a conexão
require_once 'conexao.php';

// Verifica se o usuário tá logado
if (!isset($_SESSION['id'])) {
    die("Você precisa logar primeiro, amigão!");
}

// Só quem é IDOSO pode agendar. Profissional não faz sentido agendar com outro profissional aqui.
if ($_SESSION['tipo'] !== 'idoso') {
    die("Apenas idosos podem agendar consultas por aqui, desculpe!");
}

// Pela as infos que vieram do formulário
$id_idoso = $_SESSION['id']; // Quem tá logado é o idoso
$id_prof  = $_POST['id_profissional'] ?? null;
$data_hora = $_POST['data_hora'] ?? null;

// Se faltar informação, mata o script
if (!$id_prof || !$data_hora) {
    die("Faltou preencher alguma coisa no agendamento!");
}

// SQL maroto pra inserir na tabela de consultas
$sql = "INSERT INTO consulta (id_idoso, id_profissional, data_hora, status) VALUES (?, ?, ?, 'agendada')";

$stmt = mysqli_prepare($conexao, $sql);

// to jogando isso aqui só pra ver um problema que deu no banco, pra ajudar nas proximas vezes
if (!$stmt) {
    die("Erro no banco: " . mysqli_error($conexao));
}

mysqli_stmt_bind_param($stmt, "iis", $id_idoso, $id_prof, $data_hora);

if (mysqli_stmt_execute($stmt)) {
    // Se deu certo, mostra uma mensagem de sucesso bem simples
    echo "
    <div style='text-align: center; margin-top: 50px; font-family: sans-serif;'>
        <h2 style='color: green;'>✅ Consulta agendada com sucesso!</h2>
        <p>O profissional já recebeu seu pedido.</p>
        <br>
        <a href='index.html' style='color: #00a6ce; text-decoration: none; font-weight: bold;'>Voltar para o Início</a>
    </div>
    ";
} else {
    // Se deu erro no banco
    echo "Putz, deu erro ao salvar no banco: " . mysqli_error($conexao);
}

// Fecha tudo
mysqli_stmt_close($stmt);
mysqli_close($conexao);
?>
