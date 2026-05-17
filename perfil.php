<?php

session_start();
require_once 'conexao.php';

// pega o id do profissional da url
$id_prof = $_GET['id'] ?? null;

if (!$id_prof) {
    die("Profissional não encontrado.");
}

// ======================================
// BUSCA DADOS DO PROFISSIONAL
// ======================================

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
mysqli_stmt_bind_param($stmt, "i", $id_prof);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$prof = mysqli_fetch_assoc($resultado);

// Se não achou o profissional, avisa
if (!$prof) {
    die("Profissional não encontrado no banco!");
}

// BUSCA OS CERTIFICADOS DO PROFISSIONAL (ATUALIZADO)
$sql_certs = "SELECT titulo, data_emissao, url_documento FROM certificado WHERE id_profissional = ? ORDER BY data_emissao DESC";
$stmt_certs = mysqli_prepare($conexao, $sql_certs);
mysqli_stmt_bind_param($stmt_certs, "i", $id_prof);
mysqli_stmt_execute($stmt_certs);
$resultado_certs = mysqli_stmt_get_result($stmt_certs);
$certificados = mysqli_fetch_all($resultado_certs, MYSQLI_ASSOC);

// ======================================
// BUSCA HORÁRIOS DISPONÍVEIS
// ======================================

$sql_horarios = "
SELECT *
FROM agenda_disponivel
WHERE id_profissional = ?
AND data_hora > NOW()
AND status = 'livre'
ORDER BY data_hora ASC
";

$stmt_horarios = mysqli_prepare($conexao, $sql_horarios);
mysqli_stmt_bind_param($stmt_horarios, "i", $id_prof);
mysqli_stmt_execute($stmt_horarios);
$resultado_horarios = mysqli_stmt_get_result($stmt_horarios);

// ======================================
// BUSCA AVALIAÇÕES DO PROFISSIONAL
// ======================================
$sql_avaliacoes = "
SELECT a.nota, a.comentario, u.nome 
FROM avaliacao a
INNER JOIN usuario u ON a.id_usuario = u.id_usuario
WHERE a.id_profissional = ? AND a.status_moderacao != 'rejeitada'
ORDER BY a.id_avaliacao DESC
";

$stmt_avaliacoes = mysqli_prepare($conexao, $sql_avaliacoes);
mysqli_stmt_bind_param($stmt_avaliacoes, "i", $id_prof);
mysqli_stmt_execute($stmt_avaliacoes);
$resultado_avaliacoes = mysqli_stmt_get_result($stmt_avaliacoes);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?php echo htmlspecialchars($prof['nome']); ?></title>
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
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
        <a href="index.html" style="text-decoration: none; color: #00a6ce;">
            ← Voltar para a Home
        </a>

        <h1 style="margin-top: 20px;">
            <?php echo htmlspecialchars($prof['nome']); ?>
        </h1>

        <p style="color: #00a6ce; font-weight: bold; font-size: 18px;">
            <?php echo htmlspecialchars($prof['especialidade']); ?>
        </p>

        <div style="margin-top: 20px;">
            <strong>Sobre:</strong>
            <p>
                <?php echo nl2br(htmlspecialchars($prof['biografia'] ?: "Nenhuma biografia cadastrada.")); ?>
            </p>
        </div>

        <div style="margin-top: 10px;">
            <strong>Localização:</strong>
            <p><?php echo htmlspecialchars($prof['localizacao'] ?: "Não informado."); ?></p>
        </div>

        <div style="margin-top: 20px; background: #f9f9f9; padding: 15px; border-radius: 8px;">
            <strong style="color: #333; display: block; margin-bottom: 10px;">Certificados e Especializações:</strong>
            <?php if (count($certificados) > 0): ?>
                <ul style="list-style-type: none; padding: 0; margin: 0;">
                <?php foreach ($certificados as $cert): 
                    // Remove os ../../ do caminho relativo para que o link funcione a partir da raiz do projeto
                    $caminho_correto = str_replace('../../', '', $cert['url_documento']);
                    
                    // Tratamento seguro para formatação de data vinda do MySQL
                    $data_br = "Data não informada";
                    if (!empty($cert['data_emissao']) && $cert['data_emissao'] !== '0000-00-00') {
                        $timestamp = strtotime($cert['data_emissao']);
                        if ($timestamp) {
                            $data_br = date('d/m/Y', $timestamp);
                        }
                    }
                ?>
                    <li style="background: white; padding: 12px; margin-bottom: 8px; border: 1px solid #e2e8f0; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                        <span style="color: #00a6ce; font-weight: bold;">✔ <?php echo htmlspecialchars($cert['titulo']); ?></span><br>
                        <small style="color: #666; display: inline-block; margin: 4px 0;">Emissão: <?php echo $data_br; ?></small><br>
                        <a href="<?php echo htmlspecialchars($caminho_correto); ?>" target="_blank" style="font-size: 0.9em; color: #004085; text-decoration: underline; font-weight: 500;">Visualizar Documento (PDF)</a>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="color: #777; font-style: italic; margin: 0;">Nenhum certificado adicional publicado.</p>
            <?php endif; ?>
        </div>

        <div style="margin-top: 15px;">
            <button style="background: none; border: none; padding: 0;"><a href="avaliar/avaliar.php?id=<?php echo $id_prof; ?>" class="btn-nav" style="display: inline-block; text-decoration: none; background: #004085; color: white; padding: 8px 16px; border-radius: 6px; font-weight: bold;">Avaliar este profissional</a></button>
        </div>

        <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
            <h3>Avaliações</h3>
            <?php if (mysqli_num_rows($resultado_avaliacoes) > 0): ?>
                <?php while ($av = mysqli_fetch_assoc($resultado_avaliacoes)): ?>
                    <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #ddd;">
                        <div style="color: #f39c12; font-size: 18px; margin-bottom: 5px;">
                            <?php 
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $av['nota'] ? '★' : '☆';
                            }
                            ?>
                        </div>
                        <p style="margin: 0 0 10px 0; font-style: italic;">"<?php echo htmlspecialchars($av['comentario']); ?>"</p>
                        <small style="color: #666;">- Avaliado por <strong><?php echo htmlspecialchars($av['nome']); ?></strong></small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #777; font-style: italic;">Este profissional ainda não possui avaliações.</p>
            <?php endif; ?>
        </div>

        <div class="formulario-agendar">
            <h3>Agendar uma Consulta</h3>
            <?php if (isset($_SESSION['id'])): ?>
                <?php if (mysqli_num_rows($resultado_horarios) > 0): ?>
                    <form action="agendar.php" method="POST">
                        <input type="hidden" name="id_profissional" value="<?php echo $id_prof; ?>">
                        <label>Escolha um horário disponível:</label>
                        <select name="id_agenda" class="select-horario" required>
                            <option value="">Selecione um horário</option>
                            <?php while ($horario = mysqli_fetch_assoc($resultado_horarios)): ?>
                                <option value="<?php echo $horario['id_agenda']; ?>">
                                    <?php echo date('d/m/Y H:i', strtotime($horario['data_hora'])); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" class="btn-agendar">Confirmar Agendamento</button>
                        <p style="font-size: 0.8rem; color: #777; margin-top: 10px;">
                            * Seus dados serão compartilhados com o profissional da saúde.
                        </p>
                    </form>
                <?php else: ?>
                    <p class="sem-horarios">Este profissional ainda não disponibilizou horários.</p>
                <?php endif; ?>
            <?php else: ?>
                <p style="color: red;"><strong>Atenção:</strong> Você precisa estar logado para agendar.</p>
                <a href="conta/login/login.html" style="color: #00a6ce;">Clique aqui para fazer login</a>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>