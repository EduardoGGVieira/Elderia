<?php
session_start();

// Reporte de erros para debug técnico
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Tente conectar na porta 3306 (padrão XAMPP). Se for 3307, altere abaixo.
    $conn = mysqli_connect('localhost:3307', 'root', '', 'elderia');
    mysqli_set_charset($conn, 'utf8');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: cadastro.html');
        exit;
    }

    // Funções auxiliares
    function limpar($valor) {
        return htmlspecialchars(strip_tags(trim($valor)));
    }

    function erro($mensagem) {
        $_SESSION['erro_cadastro'] = $mensagem;
        header('Location: cadastro.html');
        exit;
    }

    // Coleta de dados principais
    $tipo_usuario = limpar($_POST['tipo_usuario'] ?? '');
    $nome         = limpar($_POST['nome']         ?? '');
    $cpf          = limpar($_POST['cpf']          ?? '');
    $email        = limpar($_POST['email']        ?? '');
    $telefone     = limpar($_POST['telefone']     ?? '');
    $senha        = $_POST['senha']               ?? '';
    $confirma     = $_POST['confirme_senha']      ?? '';

    // Validações
    if (empty($nome) || empty($cpf) || empty($email) || empty($senha)) {
        erro('Preencha todos os campos obrigatórios.');
    }
    if ($senha !== $confirma) {
        erro('As senhas não coincidem.');
    }

    // 1. Verificar E-mail Duplicado
    $stmt = mysqli_prepare($conn, "SELECT email FROM usuario WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        erro('Este e-mail já está cadastrado.');
    }
    mysqli_stmt_close($stmt);

    // 2. Inserir Usuário Principal (CPF e Telefone como string 's')
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    $sql_user = "INSERT INTO usuario (nome, cpf, email, telefone, senha, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql_user);
    mysqli_stmt_bind_param($stmt, 'ssssss', $nome, $cpf, $email, $telefone, $senha_hash, $tipo_usuario);
    mysqli_stmt_execute($stmt);
    
    $usuario_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // 3. Fluxos Específicos
    if ($tipo_usuario === 'idoso') {
        // Trata data vazia como NULL para o banco
        $data_nasc      = !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : null;
        $alergias       = limpar($_POST['alergias'] ?? '');
        $info_saude     = limpar($_POST['informacoes_saude'] ?? '');
        $possui_acess   = isset($_POST['possui_acessibilidade']) ? 1 : 0;
        $necessidades   = limpar($_POST['necessidades_acessibilidade'] ?? '');

        $sql_idoso = "INSERT INTO idoso (id_idoso, data_nascimento, alergias, informacoes_saude, possui_acessibilidade, necessidades_acessibilidade) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql_idoso);
        mysqli_stmt_bind_param($stmt, 'isssis', $usuario_id, $data_nasc, $alergias, $info_saude, $possui_acess, $necessidades);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

    } elseif ($tipo_usuario === 'profissional') {
        $reg_prof      = limpar($_POST['registro_profissional'] ?? '');
        $especialidade = limpar($_POST['especialidade'] ?? '');
        $localizacao   = limpar($_POST['localizacao'] ?? '');
        $biografia     = limpar($_POST['biografia'] ?? '');
        $data_emissao  = !empty($_POST['data_emissao']) ? $_POST['data_emissao'] : null;

        // Lógica de upload (mantenha as pastas criadas no servidor!)
        $url_doc = "";
        $doc_foto = "";

        // Processamento de Uploads... (Mantendo sua lógica de caminhos)
        if (!empty($_FILES['url_documento']['name'])) {
            $nome_arq = 'cert_' . $usuario_id . '_' . time() . '.pdf';
            $destino = '../../uploads/certificados/' . $nome_arq;
            if (move_uploaded_file($_FILES['url_documento']['tmp_name'], $destino)) $url_doc = $destino;
        }
        if (!empty($_FILES['documento_foto']['name'])) {
            $ext = pathinfo($_FILES['documento_foto']['name'], PATHINFO_EXTENSION);
            $nome_arq = 'doc_' . $usuario_id . '_' . time() . '.' . $ext;
            $destino = '../../uploads/documentos/' . $nome_arq;
            if (move_uploaded_file($_FILES['documento_foto']['tmp_name'], $destino)) $doc_foto = $destino;
        }

        $sql_prof = "INSERT INTO profissional (id_profissional, registro_profissional, especialidade, localizacao, biografia, url_documento, data_emissao, documento_foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql_prof);
        mysqli_stmt_bind_param($stmt, 'isssssss', $usuario_id, $reg_prof, $especialidade, $localizacao, $biografia, $url_doc, $data_emissao, $doc_foto);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Sucesso
    $_SESSION['usuario_id'] = $usuario_id;
    $_SESSION['usuario_nome'] = $nome;
    $_SESSION['sucesso_cadastro'] = 'Cadastro realizado!';
    
    $redirecionar = ($tipo_usuario === 'profissional') ? '../dashboard/profissional.php' : '../dashboard/idoso.php';
    header("Location: $redirecionar");
    exit;

} catch (Exception $e) {
    // Se der erro, ele exibe na tela para você saber o que houve
    die("Erro ao cadastrar: " . $e->getMessage());
}
?>