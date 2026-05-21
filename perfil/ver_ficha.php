<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['id']) || !in_array($_SESSION['tipo'], ['profissional', 'admin'])) {
    header('Location: ../index.html');
    exit;
}

$id_idoso = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_idoso) {
    die("ID do idoso inválido ou não informado.");
}

//Busca os dados cruzados de usuário e a tabela específica de idoso
$sql = "
    SELECT 
        u.nome, 
        u.email, 
        u.telefone, 
        i.data_nascimento, 
        i.alergias, 
        i.informacoes_saude, 
        i.possui_acessibilidade, 
        i.necessidades_acessibilidade
    FROM usuario u
    INNER JOIN idoso i ON u.id_usuario = i.id_idoso
    WHERE u.id_usuario = ?
";

$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id_idoso);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$idoso = mysqli_fetch_assoc($resultado);

if (!$idoso) {
    die("Idoso não encontrado no sistema.");
}

function fmt_data($dt) {
    if (!$dt || $dt == '0000-00-00') return "Não informada";
    $d = new DateTime($dt);
    return $d->format('d/m/Y');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elderia - Ficha do Idoso</title>
    <link rel="stylesheet" href="../index.css">
    <style>
        .ficha-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-top: 8px solid var(--cor-secundaria);
        }
        .ficha-header { border-bottom: 2px solid #eee; margin-bottom: 30px; padding-bottom: 10px; }
        .info-secao { margin-bottom: 25px; }
        .info-secao h3 { color: var(--cor-primaria); margin-bottom: 15px; border-left: 4px solid var(--cor-secundaria); padding-left: 10px; }
        .dado-item { margin-bottom: 10px; font-size: 1.1rem; }
        .dado-item strong { color: #333; }
        .caixa-texto { background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #eee; margin-top: 5px; }
        .btn-voltar { display: inline-block; margin-bottom: 20px; text-decoration: none; color: var(--cor-primaria); font-weight: bold; transition: 0.2s; }
        .btn-voltar:hover { color: var(--cor-secundaria); }
    </style>
</head>
<body>
    <header class="cabecalho-principal">
        <div class="logo-container">
            <a href="../index.html"><h1>ELDERIA</h1></a>
        </div>
    </header>

    <main class="ficha-container">
        <a href="../consulta/confirmar.php" class="btn-voltar">← Voltar para Painel de Consultas</a>
        
        <div class="ficha-header">
            <h2>Ficha Médica: <?= htmlspecialchars($idoso['nome']) ?></h2>
        </div>

        <div class="info-secao">
            <h3>Dados de Contato</h3>
            <div class="dado-item"><strong>Telefone:</strong> <?= htmlspecialchars($idoso['telefone'] ?: "Não informado") ?></div>
            <div class="dado-item"><strong>E-mail:</strong> <?= htmlspecialchars($idoso['email']) ?></div>
            <div class="dado-item"><strong>Data de Nascimento:</strong> <?= fmt_data($idoso['data_nascimento']) ?></div>
        </div>

        <div class="info-secao">
            <h3>Saúde e Cuidados</h3>
            <div class="dado-item"><strong>Alergias:</strong><div class="caixa-texto"><?= nl2br(htmlspecialchars($idoso['alergias'] ?: "Nenhuma alergia informada.")) ?></div></div>
            <div class="dado-item"><strong>Condições de Saúde:</strong><div class="caixa-texto"><?= nl2br(htmlspecialchars($idoso['informacoes_saude'] ?: "Nenhuma observação cadastrada.")) ?></div></div>
        </div>

        <div class="info-secao">
            <h3>Acessibilidade</h3>
            <div class="dado-item"><strong>Necessita de Acessibilidade?</strong> <?= $idoso['possui_acessibilidade'] ? '<span style="color: #e67e22; font-weight:bold;">Sim</span>' : 'Não' ?></div>
            <?php if ($idoso['possui_acessibilidade']): ?>
                <div class="dado-item"><strong>Especificações:</strong><div class="caixa-texto"><?= nl2br(htmlspecialchars($idoso['necessidades_acessibilidade'])) ?></div></div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>