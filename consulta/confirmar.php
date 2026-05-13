<?php
// confirmar.php
// Pierre > Tela do profissional para visualizar e confirmar/recusar consultas pendentes

session_start();
require_once '../conexao.php';

// Pierre > Verificação de autenticação 
if (!isset($_SESSION['id'])) {
    header('Location: ../conta/login/login.html');
    exit;
}

if ($_SESSION['tipo'] !== 'profissional') {
    header('Location: ../index.html');
    exit;
}

$id_profissional = $_SESSION['id'];
$mensagem        = null;
$mensagem_tipo   = null; // 'sucesso' | 'erro'

// Pierre > Processa ação POST (confirmar ou recusar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_consulta = filter_input(INPUT_POST, 'id_consulta', FILTER_VALIDATE_INT);
    $novo_status  = filter_input(INPUT_POST, 'novo_status', FILTER_SANITIZE_SPECIAL_CHARS);

    $status_permitidos = ['confirmada', 'recusada'];

    if (!$id_consulta || $id_consulta <= 0 || !in_array($novo_status, $status_permitidos, true)) {
        $mensagem      = 'Dados inválidos. Tente novamente.';
        $mensagem_tipo = 'erro';

    } else {
        // Pierre > Verificação de posse (anti-IDOR): consulta deve pertencer a este profissional e estar pendente
        $sql_check = "
            SELECT id_consulta
            FROM consulta
            WHERE id_consulta    = ?
              AND id_profissional = ?
              AND status         = 'agendada'
            LIMIT 1
        ";
        $stmt_check = mysqli_prepare($conexao, $sql_check);
        mysqli_stmt_bind_param($stmt_check, 'ii', $id_consulta, $id_profissional);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) === 0) {
            $mensagem      = 'Consulta não encontrada ou já processada.';
            $mensagem_tipo = 'erro';
        } else {
            // Pierre > Atualiza o status
            $sql_update = "
                UPDATE consulta
                SET status = ?
                WHERE id_consulta    = ?
                  AND id_profissional = ?
            ";
            $stmt_update = mysqli_prepare($conexao, $sql_update);
            mysqli_stmt_bind_param($stmt_update, 'sii', $novo_status, $id_consulta, $id_profissional);

            if (mysqli_stmt_execute($stmt_update)) {
                $mensagem      = $novo_status === 'confirmada'
                    ? 'Consulta confirmada com sucesso!'
                    : 'Consulta recusada.';
                $mensagem_tipo = $novo_status === 'confirmada' ? 'sucesso' : 'aviso';
            } else {
                $mensagem      = 'Erro ao atualizar no banco. Tente novamente.';
                $mensagem_tipo = 'erro';
            }
            mysqli_stmt_close($stmt_update);
        }
        mysqli_stmt_close($stmt_check);
    }
}

// Pierre > Busca consultas pendentes do profissional logado
$consultas = [];

$sql = "
    SELECT
        c.id_consulta,
        u.nome    AS nome_idoso,
        c.data_hora
    FROM consulta c
    INNER JOIN usuario u ON c.id_idoso = u.id_usuario
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

// Pierre > Helpers
function fmt_data(string $dt): string {
    $d = new DateTime($dt);
    return $d->format('d/m/Y');
}

function fmt_hora(string $dt): string {
    $d = new DateTime($dt);
    return $d->format('H:i');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Elderia - Confirmar Consultas</title>
  <link rel="stylesheet" href="../index.css">
  <style>
    .conteiner-confirmar {
      max-width: 860px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .secao-titulo {
      text-align: center;
      margin-bottom: 36px;
    }

    .secao-titulo h2 {
      color: var(--cor-primaria);
      font-size: 1.8rem;
      margin-bottom: 10px;
    }

    .linha-decorativa {
      height: 3px;
      width: 80px;
      background-color: var(--cor-secundaria);
      margin: 0 auto;
      border-radius: 2px;
    }

    /* Feedback de ação */
    .alerta {
      padding: 14px 20px;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 24px;
      text-align: center;
    }

    .alerta.sucesso { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alerta.aviso   { background-color: #fff3cd; color: #856404; border: 1px solid #ffc107; }
    .alerta.erro    { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    /* Card de consulta */
    .card-consulta {
      background-color: var(--cor-card);
      border: 2px solid var(--cor-primaria);
      border-radius: 12px;
      padding: 24px 28px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      flex-wrap: wrap;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .card-info { flex: 1; min-width: 200px; }

    .card-info .nome-idoso {
      font-size: 1.15rem;
      font-weight: 700;
      color: var(--cor-primaria);
      margin-bottom: 6px;
    }

    .card-info .detalhe {
      font-size: 1rem;
      margin-bottom: 4px;
    }

    .card-info .detalhe span { font-weight: 600; }

    .badge-status {
      display: inline-block;
      padding: 3px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      margin-top: 6px;
      background-color: #fff3cd;
      color: #856404;
      border: 1px solid #ffc107;
    }

    /* Botões de ação */
    .card-acoes { display: flex; gap: 12px; flex-shrink: 0; }

    .btn-confirmar,
    .btn-recusar {
      padding: 12px 22px;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      border: none;
      min-height: 48px;
      min-width: 120px;
      transition: opacity 0.2s ease;
    }

    .btn-confirmar {
      background-color: var(--cor-primaria);
      color: #fff;
    }

    .btn-confirmar:hover { opacity: 0.88; }

    .btn-recusar {
      background-color: transparent;
      color: #c0392b;
      border: 2px solid #c0392b;
    }

    .btn-recusar:hover {
      background-color: #c0392b;
      color: #fff;
    }

    /* Estado vazio */
    .estado-vazio {
      text-align: center;
      padding: 60px 20px;
      color: var(--cor-texto, #555);
    }

    .estado-vazio .icone { font-size: 3rem; margin-bottom: 16px; }
    .estado-vazio p { font-size: 1.1rem; }

    @media (max-width: 600px) {
      .card-consulta { flex-direction: column; align-items: flex-start; }
      .card-acoes { width: 100%; }
      .btn-confirmar, .btn-recusar { flex: 1; }
    }
  </style>
</head>
<body>

  <header class="cabecalho-principal">
    <div class="logo-container">
      <a href="../index.html">
        <h1>ELDERIA</h1>
      </a>
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
      <div class="alerta <?= htmlspecialchars($mensagem_tipo) ?>">
        <?= htmlspecialchars($mensagem) ?>
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
          <div class="card-info">
            <div class="nome-idoso"><?= htmlspecialchars($c['nome_idoso']) ?></div>
            <div class="detalhe">Data: <span><?= fmt_data($c['data_hora']) ?></span></div>
            <div class="detalhe">Horário: <span><?= fmt_hora($c['data_hora']) ?></span></div>
            <span class="badge-status">Aguardando confirmação</span>
          </div>

          <div class="card-acoes">

            <form method="POST" action="confirmar.php">
              <input type="hidden" name="id_consulta" value="<?= (int) $c['id_consulta'] ?>">
              <input type="hidden" name="novo_status"  value="confirmada">
              <button type="submit" class="btn-confirmar">Confirmar</button>
            </form>

            <form method="POST" action="confirmar.php">
              <input type="hidden" name="id_consulta" value="<?= (int) $c['id_consulta'] ?>">
              <input type="hidden" name="novo_status"  value="recusada">
              <button type="submit" class="btn-recusar">Recusar</button>
            </form>

          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </main>

</body>
</html>