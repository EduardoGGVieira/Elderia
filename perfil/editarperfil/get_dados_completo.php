<?php
session_start();
header('Content-Type: application/json');
require_once '../../conexao.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

$id = $_SESSION['id'];
$tipo = $_SESSION['tipo'];

$sqlUser = "SELECT nome, email, telefone, cpf FROM usuario WHERE id_usuario = ?";
$stmt = mysqli_prepare($conexao, $sqlUser);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$userData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($tipo === 'idoso') {
    $sqlSpec = "SELECT data_nascimento, alergias, informacoes_saude, possui_acessibilidade, necessidades_acessibilidade
                FROM idoso
                WHERE id_idoso = ?";
} else {
    $sqlSpec = "SELECT registro_profissional, especialidade, biografia, localizacao, documentacao_numero, documentacao_url, documentacao_status
                FROM profissional
                WHERE id_profissional = ?";
}

$stmtSpec = mysqli_prepare($conexao, $sqlSpec);
mysqli_stmt_bind_param($stmtSpec, "i", $id);
mysqli_stmt_execute($stmtSpec);
$specData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtSpec));

if (!$specData) {
    $specData = [];
}

if ($tipo === 'profissional') {
    $sqlCerts = "SELECT id_certificado, titulo, data_emissao, url_documento, status
                 FROM certificado
                 WHERE id_profissional = ?
                 ORDER BY id_certificado DESC";

    $stmtC = mysqli_prepare($conexao, $sqlCerts);
    mysqli_stmt_bind_param($stmtC, "i", $id);
    mysqli_stmt_execute($stmtC);
    $resC = mysqli_stmt_get_result($stmtC);

    $certificados = [];

    while ($rowC = mysqli_fetch_assoc($resC)) {
        $rowC['nome_arquivo'] = basename($rowC['url_documento']);
        $certificados[] = $rowC;
    }

    mysqli_stmt_close($stmtC);
    $specData['certificados'] = $certificados;
}

echo json_encode([
    'success' => true,
    'tipo' => $tipo,
    'dados' => array_merge($userData, $specData)
]);
?>