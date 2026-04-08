<?php
session_start();

// --- Conexão com o banco de dados ---
$conn = mysqli_connect('localhost:3307', 'root', '', 'elderia');

if (!$conn) {
    die("Erro na conexão: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8');

// --- Só aceita POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastro.html');
    exit;
}

// --- Funções auxiliares ---
function limpar($valor) {
    return htmlspecialchars(strip_tags(trim($valor)));
}

function erro($mensagem) {
    $_SESSION['erro_cadastro'] = $mensagem;
    header('Location: cadastro.html');
    exit;
}

// --- Dados comuns (tabela Usuario) ---
$tipo_usuario = limpar($_POST['tipo_usuario'] ?? '');
$nome         = limpar($_POST['nome']         ?? '');
$cpf          = limpar($_POST['cpf']          ?? '');
$email        = limpar($_POST['email']        ?? '');
$telefone     = limpar($_POST['telefone']     ?? '');
$senha        = $_POST['senha']               ?? '';
$confirma     = $_POST['confirme_senha']      ?? '';

// --- Validações básicas ---
if (!in_array($tipo_usuario, ['idoso', 'profissional'])) {
    erro('Tipo de usuário inválido.');
}

if (empty($nome) || empty($cpf) || empty($email) || empty($senha)) {
    erro('Preencha todos os campos obrigatórios.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    erro('E-mail inválido.');
}

if ($senha !== $confirma) {
    erro('As senhas não coincidem.');
}

if (strlen($senha) < 6) {
    erro('A senha deve ter no mínimo 6 caracteres.');
}

// --- Verifica e-mail duplicado ---
$stmt = mysqli_prepare($conn, "SELECT id FROM usuario WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    erro('Este e-mail já está cadastrado.');
}
mysqli_stmt_close($stmt);

// --- Verifica CPF duplicado ---
$stmt = mysqli_prepare($conn, "SELECT id FROM usuario WHERE cpf = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $cpf);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    erro('Este CPF já está cadastrado.');
}
mysqli_stmt_close($stmt);

// --- Hash da senha ---
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// --- Insere na tabela Usuario ---
$stmt = mysqli_prepare($conn, "INSERT INTO usuario (nome, cpf, email, telefone, senha, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'sisiss', $nome, $cpf, $email, $telefone, $senha_hash, $tipo_usuario);

if (!mysqli_stmt_execute($stmt)) {
    erro('Erro ao salvar usuário. Tente novamente.');
}

$usuario_id = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

// ================================================================
// FLUXO IDOSO
// ================================================================
if ($tipo_usuario === 'idoso') {

    $data_nascimento          = limpar($_POST['data_nascimento']          ?? '');
    $alergias                 = limpar($_POST['alergias']                 ?? '');
    $informacoes_saude        = limpar($_POST['informacoes_saude']        ?? '');
    $possui_acessibilidade    = isset($_POST['possui_acessibilidade']) ? 1 : 0;
    $necessidades_acessibilidade = limpar($_POST['necessidades_acessibilidade'] ?? '');

    $stmt = mysqli_prepare($conn,
        "INSERT INTO idoso (usuario_id, data_nascimento, alergias, informacoes_saude, possui_acessibilidade, necessidades_acessibilidade)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, 'isssis',
        $usuario_id,
        $data_nascimento,
        $alergias,
        $informacoes_saude,
        $possui_acessibilidade,
        $necessidades_acessibilidade
    );

    if (!mysqli_stmt_execute($stmt)) {
        erro('Erro ao salvar dados do idoso. Tente novamente.');
    }
    mysqli_stmt_close($stmt);
}

// ================================================================
// FLUXO PROFISSIONAL
// ================================================================
if ($tipo_usuario === 'profissional') {

    $registro_profissional = limpar($_POST['registro_profissional'] ?? '');
    $especialidade         = limpar($_POST['especialidade']         ?? '');
    $localizacao           = limpar($_POST['localizacao']           ?? '');
    $biografia             = limpar($_POST['biografia']             ?? '');
    $data_emissao          = limpar($_POST['data_emissao']          ?? '');

    // --- Upload: Certificado PDF ---
    $url_documento = '';
    if (!empty($_FILES['url_documento']['name'])) {
        $ext = strtolower(pathinfo($_FILES['url_documento']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            erro('O certificado deve ser um arquivo PDF.');
        }
        $nome_arquivo = 'cert_' . $usuario_id . '_' . time() . '.pdf';
        $destino = '../../uploads/certificados/' . $nome_arquivo;
        if (!move_uploaded_file($_FILES['url_documento']['tmp_name'], $destino)) {
            erro('Erro ao fazer upload do certificado.');
        }
        $url_documento = $destino;
    }

    // --- Upload: Documento com Foto ---
    $documento_foto = '';
    if (!empty($_FILES['documento_foto']['name'])) {
        $ext = strtolower(pathinfo($_FILES['documento_foto']['name'], PATHINFO_EXTENSION));
        $permitidos = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $permitidos)) {
            erro('O documento com foto deve ser PDF, JPG ou PNG.');
        }
        $nome_arquivo = 'doc_' . $usuario_id . '_' . time() . '.' . $ext;
        $destino = '../../uploads/documentos/' . $nome_arquivo;
        if (!move_uploaded_file($_FILES['documento_foto']['tmp_name'], $destino)) {
            erro('Erro ao fazer upload do documento com foto.');
        }
        $documento_foto = $destino;
    }

    $stmt = mysqli_prepare($conn,
        "INSERT INTO Profissional (usuario_id, registro_profissional, especialidade, localizacao, biografia, url_documento, data_emissao, documento_foto)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, 'isssssss',
        $usuario_id,
        $registro_profissional,
        $especialidade,
        $localizacao,
        $biografia,
        $url_documento,
        $data_emissao,
        $documento_foto
    );

    if (!mysqli_stmt_execute($stmt)) {
        erro('Erro ao salvar dados do profissional. Tente novamente.');
    }
    mysqli_stmt_close($stmt);
}

// --- Cria sessão e redireciona ---
$_SESSION['usuario_id']   = $usuario_id;
$_SESSION['usuario_nome'] = $nome;
$_SESSION['usuario_tipo'] = $tipo_usuario;

$_SESSION['sucesso_cadastro'] = 'Cadastro realizado com sucesso! Bem-vindo(a), ' . $nome . '.';

if ($tipo_usuario === 'profissional') {
    header('Location: ../dashboard/profissional.php');
} else {
    header('Location: ../dashboard/idoso.php');
}
exit;
?>