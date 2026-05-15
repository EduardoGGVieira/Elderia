<?php
session_start();
header('Content-Type: application/json');
require_once '../../conexao.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'USUÁRIO NAO FOI CADASTRADOOO']);
    exit;
}

$id = $_SESSION['id'];
$tipo = $_SESSION['tipo'];

mysqli_begin_transaction($conexao); //

try {
    // atualizar dados comuns
    $stmtUser = mysqli_prepare($conexao, "UPDATE usuario SET nome=?, email=?, telefone=? WHERE id_usuario=?");
    mysqli_stmt_bind_param($stmtUser, "sssi", $_POST['nome'], $_POST['email'], $_POST['telefone'], $id);
    mysqli_stmt_execute($stmtUser);

    // atualizar dados só de idoso ou de profissional
    if($tipo === 'idoso'){
        $sqlIdoso = "UPDATE idoso SET data_nascimento=?, alergias=?, informacoes_saude=?, possui_acessibilidade=?, necessidades_acessibilidade=? WHERE id_idoso=?";
        $stmtIdoso = mysqli_prepare($conexao, $sqlIdoso);
        mysqli_stmt_bind_param($stmtIdoso, "sssisi", $_POST['data_nascimento'], $_POST['alergias'], $_POST['informacoes_saude'], $_POST['possui_acessibilidade'], $_POST['necessidades_acessibilidade'], $id);
        mysqli_stmt_execute($stmtIdoso);
    } 
    else if($tipo === 'profissional'){
        $sqlProf = "UPDATE profissional SET registro_profissional=?, especialidade=?, biografia=?, localizacao=?, data_emissao=? WHERE id_profissional=?";
        $stmtProf = mysqli_prepare($conexao, $sqlProf);
        mysqli_stmt_bind_param($stmtProf, "sssssi", $_POST['registro_profissional'], $_POST['especialidade'], $_POST['biografia'], $_POST['localizacao'], $_POST['data_emissao'], $id);
        mysqli_stmt_execute($stmtProf);
    }

    mysqli_commit($conexao); //
    $_SESSION['nome'] = $_POST['nome']; // atualiza o nome na sessao na hora

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    mysqli_rollback($conexao); //
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>