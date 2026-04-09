<?php
// Atualizado por André Felipe
session_start();

// Habilitar erros para facilitar identificação de problemas
// Corrigido por André Felipe
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Incluindo a conexão centralizada
// Atualizado por André Felipe
require_once '../../conexao.php';

try {
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

    // Validações simples
    if (empty($nome) || empty($cpf) || empty($email) || empty($senha)) {
        erro('Preencha todos os campos obrigatórios.');
    }
    if ($senha !== $confirma) {
        erro('As senhas não coincidem.');
    }

    // 1. Verificar E-mail Duplicado (Tabela usuario em minúsculo)
    // Corrigido por André Felipe
    $stmt = mysqli_prepare($conexao, "SELECT email FROM usuario WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        erro('Este e-mail já está cadastrado.');
    }
    mysqli_stmt_close($stmt);

    // 2. Inserir Usuário Principal (Criptografando a senha)
    // Atualizado por André Felipe
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    $sql_user = "INSERT INTO usuario (nome, cpf, email, telefone, senha, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexao, $sql_user);
    mysqli_stmt_bind_param($stmt, 'ssssss', $nome, $cpf, $email, $telefone, $senha_hash, $tipo_usuario);
    mysqli_stmt_execute($stmt);
    
    $usuario_id = mysqli_insert_id($conexao);
    mysqli_stmt_close($stmt);

    // 3. Fluxos Específicos: Sincronizado com o SQL real
    if ($tipo_usuario === 'idoso') {
        $data_nasc      = !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : null;
        $alergias       = limpar($_POST['alergias'] ?? '');
        $info_saude     = limpar($_POST['informacoes_saude'] ?? '');
        $necessidades   = limpar($_POST['necessidades_acessibilidade'] ?? '');

        // Corrigido por André Felipe: Removida a coluna 'possui_acessibilidade' que não existe no SQL
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

        // Corrigido por André Felipe: Sincronizado com colunas da tabela 'profissional'
        $sql_prof = "INSERT INTO profissional (id_profissional, registro_profissional, especialidade, biografia, localizacao) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexao, $sql_prof);
        mysqli_stmt_bind_param($stmt, 'issss', $usuario_id, $reg_prof, $especialidade, $biografia, $localizacao);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Sucesso no Cadastro
    $_SESSION['usuario_id'] = $usuario_id;
    $_SESSION['usuario_nome'] = $nome;
    $_SESSION['sucesso_cadastro'] = 'Cadastro realizado com sucesso!';
    
    // Redirecionamento corrigido para o perfil
    // Corrigido por André Felipe
    header("Location: ../../perfil/index.html");
    exit;

} catch (Exception $e) {
    // Exibe o erro se algo der errado (útil para o André Felipe debugar)
    die("Erro ao realizar o cadastro: " . $e->getMessage());
}
?>