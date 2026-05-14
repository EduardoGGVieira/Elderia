<?php
// consulta/confirmar.php
// Tela do profissional: ver consultas pendentes e confirmar ou recusar

session_start();
require_once '../conexao.php';

// Pierre > Segurança: só profissionais logados podem acessar
if (!isset($_SESSION['id'])) {
    header('Location: ../conta/login/login.html');
    exit;
}
if ($_SESSION['tipo'] !== 'profissional') {
    header('Location: ../index.html');
    exit;
}

$id_profissional = (int) $_SESSION['id'];
$mensagem        = null;
$mensagem_tipo   = null; // 'sucesso' | 'aviso' | 'erro'

// Pierre > Processa ação POST (confirmar ou recusar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_consulta = filter_input(INPUT_POST, 'id_consulta', FILTER_VALIDATE_INT);
    $acao        = filter_input(INPUT_POST, 'acao',        FILTER_SANITIZE_SPECIAL_CHARS);

    if (!$id_consulta || $id_consulta <= 0 || !in_array($acao, ['confirmar', 'recusar'], true)) {
        $mensagem      = 'Dados inválidos. Tente novamente.';
        $mensagem_tipo = 'erro';

    } else {

        // Pierre >Busca a consulta garantindo que pertence a este profissional e está pendente
        $sql_check = "
            SELECT c.id_consulta, c.data_hora, c.id_idoso
            FROM consulta c
            WHERE c.id_consulta     = ?
              AND c.id_profissional  = ?
              AND c.status           = 'agendada'
            LIMIT 1
        ";
        $stmt_check = mysqli_prepare($conexao, $sql_check);
        mysqli_stmt_bind_param($stmt_check, 'ii', $id_consulta, $id_profissional);
        mysqli_stmt_execute($stmt_check);
        $res_check = mysqli_stmt_get_result($stmt_check);
        $consulta  = mysqli_fetch_assoc($res_check);
        mysqli_stmt_close($stmt_check);

        if (!$consulta) {
            $mensagem      = 'Consulta não encontrada ou já processada.';
            $mensagem_tipo = 'erro';

        } elseif ($acao === 'confirmar') {

            // Pierre >Verifica conflito: já existe outra consulta CONFIRMADA neste mesmo horário?
            $sql_conflito = "
                SELECT id_consulta FROM consulta
                WHERE id_profissional = ?
                  AND data_hora       = ?
                  AND status          = 'confirmada'
                  AND id_consulta    <> ?
                LIMIT 1
            ";
            $stmt_conflito = mysqli_prepare($conexao, $sql_conflito);
            $data_hora     = $consulta['data_hora'];
            mysqli_stmt_bind_param($stmt_conflito, 'isi', $id_profissional, $data_hora, $id_consulta);
            mysqli_stmt_execute($stmt_conflito);
            mysqli_stmt_store_result($stmt_conflito);
            $tem_conflito = mysqli_stmt_num_rows($stmt_conflito) > 0;
            mysqli_stmt_close($stmt_conflito);

            if ($tem_conflito) {
                // Pierre > Horário ocupado → cancela esta consulta automaticamente
                $sql_cancela = "UPDATE consulta SET status = 'cancelada' WHERE id_consulta = ? AND id_profissional = ?";
                $stmt_cancela = mysqli_prepare($conexao, $sql_cancela);
                mysqli_stmt_bind_param($stmt_cancela, 'ii', $id_consulta, $id_profissional);
                mysqli_stmt_execute($stmt_cancela);
                mysqli_stmt_close($stmt_cancela);

                $mensagem      = '⚠️ Este horário já foi confirmado para outro paciente. A consulta foi cancelada automaticamente.';
                $mensagem_tipo = 'aviso';

            } else {
                // Pierre > Sem conflito → confirma + marca slot como agendado (transação)
                mysqli_begin_transaction($conexao);
                try {
                    $sql_confirma = "UPDATE consulta SET status = 'confirmada' WHERE id_consulta = ? AND id_profissional = ?";
                    $stmt_confirma = mysqli_prepare($conexao, $sql_confirma);
                    mysqli_stmt_bind_param($stmt_confirma, 'ii', $id_consulta, $id_profissional);
                    mysqli_stmt_execute($stmt_confirma);
                    mysqli_stmt_close($stmt_confirma);

                    $sql_agenda = "
                        UPDATE agenda_disponivel SET status = 'agendado'
                        WHERE id_profissional = ? AND data_hora = ? AND status = 'livre'
                        LIMIT 1
                    ";
                    $stmt_agenda = mysqli_prepare($conexao, $sql_agenda);
                    mysqli_stmt_bind_param($stmt_agenda, 'is', $id_profissional, $data_hora);
                    mysqli_stmt_execute($stmt_agenda);
                    mysqli_stmt_close($stmt_agenda);

                    mysqli_commit($conexao);
                    $mensagem      = '✅ Consulta confirmada com sucesso!';
                    $mensagem_tipo = 'sucesso';

                } catch (Exception $e) {
                    mysqli_rollback($conexao);
                    $mensagem      = 'Erro ao confirmar. Tente novamente.';
                    $mensagem_tipo = 'erro';
                }
            }

        } else {
            // Pierre > Recusar -> cancela e devolve horárrio para 'livre' (transação)
            $data_hora = $consulta['data_hora'];
            mysqli_begin_transaction($conexao);
            try {
                $sql_recusa = "UPDATE consulta SET status = 'cancelada' WHERE id_consulta = ? AND id_profissional = ?";
                $stmt_recusa = mysqli_prepare($conexao, $sql_recusa);
                mysqli_stmt_bind_param($stmt_recusa, 'ii', $id_consulta, $id_profissional);
                mysqli_stmt_execute($stmt_recusa);
                mysqli_stmt_close($stmt_recusa);

                $sql_libera = "
                    UPDATE agenda_disponivel SET status = 'livre'
                    WHERE id_profissional = ? AND data_hora = ?
                    LIMIT 1
                ";
                $stmt_libera = mysqli_prepare($conexao, $sql_libera);
                mysqli_stmt_bind_param($stmt_libera, 'is', $id_profissional, $data_hora);
                mysqli_stmt_execute($stmt_libera);
                mysqli_stmt_close($stmt_libera);

                mysqli_commit($conexao);
                $mensagem      = 'Consulta recusada. O horário foi liberado na sua agenda.';
                $mensagem_tipo = 'aviso';

            } catch (Exception $e) {
                mysqli_rollback($conexao);
                $mensagem      = 'Erro ao recusar. Tente novamente.';
                $mensagem_tipo = 'erro';
            }
        }
    }
}

// Pierre > Busca consultas pendentes do profissional logado
$consultas = [];

$sql = "
    SELECT
        c.id_consulta,
        c.data_hora,
        u.nome                    AS nome_idoso,
        i.informacoes_saude,
        i.alergias,
        i.possui_acessibilidade

    FROM consulta c
    INNER JOIN usuario u ON c.id_idoso = u.id_usuario
    INNER JOIN idoso   i ON c.id_idoso = i.id_idoso

    WHERE c.id_profissional = ?
      AND c.status          = 'agendada'

    ORDER BY c.data_hora ASC
";

$stmt = mysqli_prepare($conexao, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $id_profissional);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $consultas[] = $row;
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conexao);

// Pierre > Funções auxiliares
function fmt_data(string $dt): string { return (new DateTime($dt))->format('d/m/Y'); }
function fmt_hora(string $dt): string { return (new DateTime($dt))->format('H:i'); }
function esc(string $str): string     { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Elderia — Confirmar Consultas</title>
  <link rel="stylesheet" href="../index.css">
  <style>
    .conteiner-confirmar {
      max-width: 860px;
      margin: 40px auto;
      padding: 0 20px;
    }
    .secao-titulo { text-align: center; margin-bottom: 36px; }
    .secao-titulo h2 { color: var(--cor-primaria); font-size: 1.8rem; margin-bottom: 10px; }
    .linha-decorativa { height: 3px; width: 80px; background: var(--cor-secundaria); margin: 0 auto; border-radius: 2px; }

    .alerta { padding: 14px 20px; border-radius: 8px; font-size: 1rem; font-weight: 600; margin-bottom: 24px; text-align: center; }
    .alerta.sucesso { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alerta.aviso   { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
    .alerta.erro    { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    .card-consulta {
      background: var(--cor-card);
      border: 2px solid var(--cor-primaria);
      border-radius: 12px;
      padding: 24px 28px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .card-topo { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; flex-wrap: wrap; }
    .card-info .nome-idoso { font-size: 1.15rem; font-weight: 700; color: var(--cor-primaria); margin-bottom: 6px; }
    .card-info .detalhe { font-size: 1rem; margin-bottom: 4px; }
    .card-info .detalhe span { font-weight: 600; }
    .badge-status { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; margin-top: 6px; background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
    .badge-acessibilidade { display: inline-block; background: #cce5ff; color: #004085; border: 1px solid #b8daff; padding: 2px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; margin-left: 6px; }

    .card-extras { margin-top: 14px; padding-top: 14px; border-top: 1px solid rgba(0,0,0,0.08); display: flex; gap: 20px; flex-wrap: wrap; font-size: 0.9rem; color: var(--cor-texto, #555); }
    .card-extras .extra-item strong { color: var(--cor-primaria); }

    .card-acoes { display: flex; gap: 12px; flex-shrink: 0; align-items: center; }
    .btn-confirmar, .btn-recusar { padding: 12px 22px; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; border: none; min-height: 48px; min-width: 120px; transition: opacity 0.2s ease; }
    .btn-confirmar { background: var(--cor-primaria); color: #fff; }
    .btn-confirmar:hover { opacity: 0.88; }
    .btn-recusar { background: transparent; color: #c0392b; border: 2px solid #c0392b; }
    .btn-recusar:hover { background: #c0392b; color: #fff; }

    .estado-vazio { text-align: center; padding: 60px 20px; color: var(--cor-texto, #555); }
    .estado-vazio .icone { font-size: 3rem; margin-bottom: 16px; }
    .estado-vazio p { font-size: 1.1rem; }

    @media (max-width: 600px) {
      .card-topo { flex-direction: column; }
      .card-acoes { width: 100%; }
      .btn-confirmar, .btn-recusar { flex: 1; }
    }
  </style>
</head>
<body>

  <header class="cabecalho-principal">
    <div class="logo-container">
      <a href="../index.html"><h1>ELDERIA</h1></a>
    </div>
    <nav class="navegacao-botoes">
      <a href="../perfil/" class="btn-nav">Meu Perfil</a>
      <a href="index.html" class="btn-nav">Consultas</a>
    </nav>
    <div class="usuario-info">
      <a href="../conta/login/login.html" class="btn-sair">Sair</a>
    </div>
  </header>

  <main class="conteiner-confirmar">

    <div class="secao-titulo">
      <h2>Consultas Pendentes</h2>
      <div class="linha-decorativa"></div>
    </div>

    <?php if ($mensagem): ?>
      <div class="alerta <?= esc($mensagem_tipo) ?>">
        <?= esc($mensagem) ?>
      </div>
    <?php endif; ?>

    <?php if (empty($consultas)): ?>
      <div class="estado-vazio">
        <div class="icone">✅</div>
        <p>Nenhuma consulta pendente no momento.</p>
      </div>

    <?php else: ?>
      <?php foreach ($consultas as $c): ?>
        <div class="card-consulta">
          <div class="card-topo">

            <div class="card-info">
              <div class="nome-idoso">
                <?= esc($c['nome_idoso']) ?>
                <?php if ($c['possui_acessibilidade']): ?>
                  <span class="badge-acessibilidade">♿ Acessibilidade</span>
                <?php endif; ?>
              </div>
              <div class="detalhe">Data: <span><?= fmt_data($c['data_hora']) ?></span></div>
              <div class="detalhe">Horário: <span><?= fmt_hora($c['data_hora']) ?></span></div>
              <span class="badge-status">Aguardando confirmação</span>
            </div>

            <div class="card-acoes">
              <form method="POST" action="confirmar.php">
                <input type="hidden" name="id_consulta" value="<?= (int) $c['id_consulta'] ?>">
                <input type="hidden" name="acao" value="confirmar">
                <button type="submit" class="btn-confirmar">Confirmar</button>
              </form>
              <form method="POST" action="confirmar.php">
                <input type="hidden" name="id_consulta" value="<?= (int) $c['id_consulta'] ?>">
                <input type="hidden" name="acao" value="recusar">
                <button type="submit" class="btn-recusar">Recusar</button>
              </form>
            </div>

          </div>

          <?php if ($c['informacoes_saude'] || $c['alergias']): ?>
            <div class="card-extras">
              <?php if ($c['informacoes_saude']): ?>
                <div class="extra-item"><strong>Saúde:</strong> <?= esc($c['informacoes_saude']) ?></div>
              <?php endif; ?>
              <?php if ($c['alergias']): ?>
                <div class="extra-item"><strong>Alergias:</strong> <?= esc($c['alergias']) ?></div>
              <?php endif; ?>
            </div>
          <?php endif; ?>

        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </main>

</body>
</html>