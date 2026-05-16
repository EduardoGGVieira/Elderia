<?php
session_start();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once '../../conexao.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: cadastro.html');
        exit;
    }

    function limpar($valor) {
        return htmlspecialchars(strip_tags(trim($valor)));
    }

    function erro($mensagem) {
        $_SESSION['erro_cadastro'] = $mensagem;
        header('Location: cadastro.html');
        exit;
    }

    $tipo_usuario = limpar($_POST['tipo_usuario'] ?? '');
    $nome         = limpar($_POST['nome'] ?? '');
    $cpf          = limpar($_POST['cpf'] ?? '');
    $email        = limpar($_POST['email'] ?? '');
    $telefone     = limpar($_POST['telefone'] ?? '');
    $senha        = $_POST['senha'] ?? '';
    $confirma     = $_POST['confirme_senha'] ?? '';

    if (empty($nome) || empty($cpf) || empty($email) || empty($senha)) {
        erro('Preencha todos os campos obrigatórios.');
    }

    if ($senha !== $confirma) {
        erro('As senhas não coincidem.');
    }

    $stmt = mysqli_prepare($conexao, "SELECT email FROM usuario WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        erro('Este e-mail já está cadastrado.');
    }

    mysqli_stmt_close($stmt);

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    $sql_user = "INSERT INTO usuario (nome, cpf, email, telefone, senha, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexao, $sql_user);
    mysqli_stmt_bind_param($stmt, 'ssssss', $nome, $cpf, $email, $telefone, $senha_hash, $tipo_usuario);
    mysqli_stmt_execute($stmt);

    $usuario_id = mysqli_insert_id($conexao);
    mysqli_stmt_close($stmt);

    if ($tipo_usuario === 'idoso') {

        $data_nasc    = !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : null;
        $alergias     = limpar($_POST['alergias'] ?? '');
        $info_saude   = limpar($_POST['informacoes_saude'] ?? '');
        $necessidades = limpar($_POST['necessidades_acessibilidade'] ?? '');

        $sql_idoso = "INSERT INTO idoso (id_idoso, data_nascimento, necessidades_acessibilidade, informacoes_saude, alergias) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexao, $sql_idoso);
        mysqli_stmt_bind_param($stmt, 'issss', $usuario_id, $data_nasc, $necessidades, $info_saude, $alergias);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

    } elseif ($tipo_usuario === 'profissional') {

        $reg_prof      = limpar($_POST['registro_profissional'] ?? '');
        $especialidade = limpar($_POST['especialidade'] ?? '');
        $localizacao   = limpar($_POST['localizacao'] ?? '');
        $biografia     = limpar($_POST['biografia'] ?? '');
        $data_emissao  = !empty($_POST['data_emissao']) ? $_POST['data_emissao'] : null;

        $url_doc = null;
        $doc_foto = null;

        if (!empty($_FILES['url_documento']['name']) && $_FILES['url_documento']['error'] === 0) {
            $nome_arq = 'cert_' . $usuario_id . '_' . time() . '.pdf';
            $destino = sys_get_temp_dir() . '/' . $nome_arq;
            if (move_uploaded_file($_FILES['url_documento']['tmp_name'], $destino)) {
                $url_doc = $destino;
            }
        }

        if (!empty($_FILES['documento_foto']['name']) && $_FILES['documento_foto']['error'] === 0) {
            $ext = pathinfo($_FILES['documento_foto']['name'], PATHINFO_EXTENSION);
            $nome_arq = 'doc_' . $usuario_id . '_' . time() . '.' . $ext;
            $destino = sys_get_temp_dir() . '/' . $nome_arq;
            if (move_uploaded_file($_FILES['documento_foto']['tmp_name'], $destino)) {
                $doc_foto = $destino;
            }
        }

        if (empty($reg_prof)) {
            $reg_prof = 'TEMP_' . $usuario_id;
        }

        $sql_prof = "INSERT INTO profissional (id_profissional, registro_profissional, especialidade, localizacao, biografia, url_documento, data_emissao, documento_foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexao, $sql_prof);

        if (!$stmt) {
            die("Erro ao preparar profissional: " . mysqli_error($conexao));
        }

        mysqli_stmt_bind_param($stmt, 'isssssss', $usuario_id, $reg_prof, $especialidade, $localizacao, $biografia, $url_doc, $data_emissao, $doc_foto);

        if (!mysqli_stmt_execute($stmt)) {
            die("Erro ao inserir profissional: " . mysqli_error($conexao));
        }

        mysqli_stmt_close($stmt);
    }

    $_SESSION['id'] = $usuario_id;
    $_SESSION['nome'] = $nome;
    $_SESSION['tipo'] = $tipo_usuario;
    $_SESSION['email'] = $email;
    $_SESSION['sucesso_cadastro'] = 'Cadastro realizado com sucesso!';

    header("Location: ../../perfil/index.html");
    exit;

} catch (Exception $e) {
    die("Erro ao realizar o cadastro: " . $e->getMessage());
}
?>