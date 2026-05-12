<?php

session_start();
header('Content-Type: application/json');

require_once '../conexao.php';

if (!isset($_SESSION['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuário não autenticado'
    ]);
    exit;
}

$id = $_SESSION['id'];
$tipo = $_SESSION['tipo'];

$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';

mysqli_begin_transaction($conexao);

try {

    // Atualiza usuário
    $sqlUser = "UPDATE usuario 
                SET nome=?, email=?, telefone=? 
                WHERE id_usuario=?";

    $stmtUser = mysqli_prepare($conexao, $sqlUser);

    mysqli_stmt_bind_param(
        $stmtUser,
        "sssi",
        $nome,
        $email,
        $telefone,
        $id
    );

    mysqli_stmt_execute($stmtUser);

    // IDOSO
    if($tipo === 'idoso'){

        $data_nascimento = $_POST['data_nascimento'] ?? null;
        $alergias = $_POST['alergias'] ?? '';
        $informacoes_saude = $_POST['informacoes_saude'] ?? '';
        $possui_acessibilidade = $_POST['possui_acessibilidade'] ?? 0;
        $necessidades_acessibilidade = $_POST['necessidades_acessibilidade'] ?? '';

        $sqlIdoso = "
            UPDATE idoso 
            SET 
                data_nascimento=?,
                alergias=?,
                informacoes_saude=?,
                possui_acessibilidade=?,
                necessidades_acessibilidade=?
            WHERE id_idoso=?
        ";

        $stmtIdoso = mysqli_prepare($conexao, $sqlIdoso);

        mysqli_stmt_bind_param(
            $stmtIdoso,
            "sssisi",
            $data_nascimento,
            $alergias,
            $informacoes_saude,
            $possui_acessibilidade,
            $necessidades_acessibilidade,
            $id
        );

        mysqli_stmt_execute($stmtIdoso);
    }

    // PROFISSIONAL
    else if($tipo === 'profissional'){

        $registro_profissional = $_POST['registro_profissional'] ?? '';
        $especialidade = $_POST['especialidade'] ?? '';
        $biografia = $_POST['biografia'] ?? '';
        $localizacao = $_POST['localizacao'] ?? '';
        $data_emissao = $_POST['data_emissao'] ?? null;

        $sqlProfissional = "
            UPDATE profissional
            SET
                registro_profissional=?,
                especialidade=?,
                biografia=?,
                localizacao=?,
                data_emissao=?
            WHERE id_profissional=?
        ";

        $stmtProf = mysqli_prepare($conexao, $sqlProfissional);

        mysqli_stmt_bind_param(
            $stmtProf,
            "sssssi",
            $registro_profissional,
            $especialidade,
            $biografia,
            $localizacao,
            $data_emissao,
            $id
        );

        mysqli_stmt_execute($stmtProf);
    }

    mysqli_commit($conexao);

    echo json_encode([
        'success' => true
    ]);

} catch (Exception $e) {

    mysqli_rollback($conexao);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>