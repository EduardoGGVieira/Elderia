<?php
include("../../conexao.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Coleta de dados da tabela 'usuario'
    $nome = mysqli_real_escape_string($conn, trim($_POST['nome']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $cpf = mysqli_real_escape_string($conn, trim($_POST['cpf']));
    $telefone = mysqli_real_escape_string($conn, trim($_POST['telefone']));
    $senha = trim($_POST['senha']);
    $tipo_usuario = mysqli_real_escape_string($conn, trim($_POST['tipo_usuario'])); // 'idoso' ou 'profissional'

    // Criptografia da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // 2. Verifica se o e-mail ou CPF já existem (campos UNIQUE no banco)
    $verifica = mysqli_query($conn, "SELECT id_usuario FROM usuario WHERE email = '$email' OR cpf = '$cpf'");

    if (mysqli_num_rows($verifica) > 0) {
        echo "<p style='color:red;'>Erro: E-mail ou CPF já cadastrados!</p>";
        echo "<a href='../login/login.html'>Tente fazer login aqui</a>";
    } else {
        // 3. Inserção na tabela principal: 'usuario'
        $sql_usuario = "INSERT INTO usuario (nome, email, senha, telefone, cpf, tipo_usuario) 
                        VALUES ('$nome', '$email', '$senha_hash', '$telefone', '$cpf', '$tipo_usuario')";

        if (mysqli_query($conn, $sql_usuario)) {
            $id_gerado = mysqli_insert_id($conn); // Pega o ID gerado para usar como FK

            if ($tipo_usuario === 'idoso') {
                // --- FLUXO IDOSO ---
                $data_nascimento = mysqli_real_escape_string($conn, $_POST['data_nascimento']);
                $alergias = mysqli_real_escape_string($conn, $_POST['alergias']);
                $informacoes_saude = mysqli_real_escape_string($conn, $_POST['informacoes_saude']);
                $necessidades = mysqli_real_escape_string($conn, $_POST['necessidades_acessibilidade']);

                $sql_especifico = "INSERT INTO idoso (id_idoso, data_nascimento, necessidades_acessibilidade, informacoes_saude, alergias) 
                                   VALUES ('$id_gerado', '$data_nascimento', '$necessidades', '$informacoes_saude', '$alergias')";

            } else if ($tipo_usuario === 'profissional') {
                // --- FLUXO PROFISSIONAL ---
                $registro = mysqli_real_escape_string($conn, $_POST['registro_profissional']);
                $especialidade = mysqli_real_escape_string($conn, $_POST['especialidade']);
                $localizacao = mysqli_real_escape_string($conn, $_POST['localizacao']);
                $biografia = mysqli_real_escape_string($conn, $_POST['biografia']);

                // Inserção na tabela 'profissional'
                $sql_especifico = "INSERT INTO profissional (id_profissional, registro_profissional, especialidade, biografia, localizacao) 
                                   VALUES ('$id_gerado', '$registro', '$especialidade', '$biografia', '$localizacao')";
                
                // Lógica simples para o upload do Certificado (Salvando apenas o nome do arquivo para exemplo)
                if (isset($_FILES['url_documento']) && $_FILES['url_documento']['error'] === UPLOAD_ERR_OK) {
                    $nome_arquivo = time() . "_" . $_FILES['url_documento']['name'];
                    move_uploaded_file($_FILES['url_documento']['tmp_name'], "../../uploads/" . $nome_arquivo);
                    
                    $data_emissao = mysqli_real_escape_string($conn, $_POST['data_emissao']);
                    // Insere na tabela 'certificado'
                    mysqli_query($conn, "INSERT INTO certificado (id_profissional, url_documento, data_emissao) VALUES ('$id_gerado', '$nome_arquivo', '$data_emissao')");
                }
            }

            // Executa a inserção na tabela específica (idoso ou profissional)
            if (mysqli_query($conn, $sql_especifico)) {
                echo "<p style='color:green;'>Cadastro realizado com sucesso!</p>";
                echo "<a href='../login/login.html'>Clique aqui para logar</a>";
            } else {
                echo "<p style='color:red;'>Erro ao salvar detalhes do perfil: " . mysqli_error($conn) . "</p>";
            }

        } else {
            echo "<p style='color:red;'>Erro ao salvar usuário: " . mysqli_error($conn) . "</p>";
        }
    }
}
?>