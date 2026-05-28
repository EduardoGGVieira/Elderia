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

    function validarExtensao($nomeArquivo, $extensoesPermitidas) {
        $ext = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
        return in_array($ext, $extensoesPermitidas);
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

    mysqli_begin_transaction($conexao);

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
        $possui_acessibilidade = isset($_POST['possui_acessibilidade']) ? 1 : 0;

        $sql_idoso = "INSERT INTO idoso
            (id_idoso, data_nascimento, necessidades_acessibilidade, informacoes_saude, alergias, possui_acessibilidade)
            VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conexao, $sql_idoso);
        mysqli_stmt_bind_param($stmt, 'issssi', $usuario_id, $data_nasc, $necessidades, $info_saude, $alergias, $possui_acessibilidade);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    elseif ($tipo_usuario === 'profissional') {
        $reg_prof      = limpar($_POST['registro_profissional'] ?? '');
        $especialidade = limpar($_POST['especialidade'] ?? '');
        $localizacao   = limpar($_POST['localizacao'] ?? '');
        $biografia     = limpar($_POST['biografia'] ?? '');
        $data_emissao  = !empty($_POST['data_emissao']) ? $_POST['data_emissao'] : null;
        $titulo_cert   = limpar($_POST['titulo_certificado'] ?? 'Certificado de Formação');

        $url_certificado = null;
        $url_documentacao = null;

        if (!empty($_FILES['url_documento']['name'])) {
            if (!validarExtensao($_FILES['url_documento']['name'], ['pdf'])) {
                throw new Exception('O certificado precisa ser PDF.');
            }

            if (!is_dir('../../uploads/certificados')) {
                mkdir('../../uploads/certificados', 0777, true);
            }

            $nome_arq = 'cert_' . $usuario_id . '_' . time() . '.pdf';
            $destino = '../../uploads/certificados/' . $nome_arq;

            if (!move_uploaded_file($_FILES['url_documento']['tmp_name'], $destino)) {
                throw new Exception('Erro ao enviar certificado.');
            }

            $url_certificado = $destino;
        }

        if (!empty($_FILES['documento_foto']['name'])) {
            if (!validarExtensao($_FILES['documento_foto']['name'], ['pdf', 'jpg', 'jpeg', 'png', 'webp'])) {
                throw new Exception('A documentação profissional precisa ser PDF ou imagem.');
            }

            if (!is_dir('../../uploads/documentos')) {
                mkdir('../../uploads/documentos', 0777, true);
            }

            $ext = strtolower(pathinfo($_FILES['documento_foto']['name'], PATHINFO_EXTENSION));
            $nome_arq = 'doc_' . $usuario_id . '_' . time() . '.' . $ext;
            $destino = '../../uploads/documentos/' . $nome_arq;

            if (!move_uploaded_file($_FILES['documento_foto']['tmp_name'], $destino)) {
                throw new Exception('Erro ao enviar documentação profissional.');
            }

            $url_documentacao = $destino;
        }

        $sql_prof = "INSERT INTO profissional
            (id_profissional, registro_profissional, especialidade, localizacao, biografia, visibilidade, documentacao_numero, documentacao_url, documentacao_status)
            VALUES (?, ?, ?, ?, ?, 0, ?, ?, 'pendente')";

        $stmt = mysqli_prepare($conexao, $sql_prof);
        mysqli_stmt_bind_param(
            $stmt,
            'issssss',
            $usuario_id,
            $reg_prof,
            $especialidade,
            $localizacao,
            $biografia,
            $reg_prof,
            $url_documentacao
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!empty($url_certificado)) {
            $sql_cert = "INSERT INTO certificado
                (id_profissional, titulo, data_emissao, url_documento, status)
                VALUES (?, ?, ?, ?, 'pendente')";

            $stmt_cert = mysqli_prepare($conexao, $sql_cert);
            mysqli_stmt_bind_param($stmt_cert, 'isss', $usuario_id, $titulo_cert, $data_emissao, $url_certificado);
            mysqli_stmt_execute($stmt_cert);
            mysqli_stmt_close($stmt_cert);
        }
    }

    mysqli_commit($conexao);

    $_SESSION['id'] = $usuario_id;
    $_SESSION['nome'] = $nome;
    $_SESSION['tipo'] = $tipo_usuario;
    $_SESSION['email'] = $email;
    $_SESSION['sucesso_cadastro'] = 'Cadastro realizado com sucesso! Seus documentos ficarão pendentes até análise do administrador.';

    header("Location: ../../perfil/index.html");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conexao);
    die("Erro ao realizar o cadastro: " . $e->getMessage());
}
?>