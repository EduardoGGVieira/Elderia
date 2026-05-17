<?php
session_start();
require_once '../../conexao.php';

header('Content-Type: application/json');

// Validação de segurança de sessão
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'profissional') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_profissional = $_SESSION['id'];
    $id_certificado = intval($_POST['id_certificado'] ?? 0);

    if ($id_certificado <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID do certificado inválido.']);
        exit;
    }

    // Busca o arquivo no banco para garantir a propriedade e encontrar o link do arquivo físico
    $sqlBusca = "SELECT url_documento FROM certificado WHERE id_certificado = ? AND id_profissional = ?";
    $stmtB = mysqli_prepare($conexao, $sqlBusca);
    mysqli_stmt_bind_param($stmtB, "ii", $id_certificado, $id_profissional);
    mysqli_stmt_execute($stmtB);
    $resB = mysqli_stmt_get_result($stmtB);
    $cert = mysqli_fetch_assoc($resB);
    mysqli_stmt_close($stmtB);

    if (!$cert) {
        echo json_encode(['success' => false, 'message' => 'Certificado não encontrado ou permissão negada.']);
        exit;
    }

    // Deleta o arquivo PDF físico da pasta uploads
    $caminho_arquivo = $cert['url_documento'];
    if (!empty($caminho_arquivo) && file_exists($caminho_arquivo)) {
        unlink($caminho_arquivo);
    }

    // Remove o registro da tabela certificado
    $sqlDelete = "DELETE FROM certificado WHERE id_certificado = ?";
    $stmtD = mysqli_prepare($conexao, $sqlDelete);
    mysqli_stmt_bind_param($stmtD, "i", $id_certificado);
    
    if (mysqli_stmt_execute($stmtD)) {
        echo json_encode(['success' => true, 'message' => 'Certificado removido com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao remover o registro do banco de dados.']);
    }
    mysqli_stmt_close($stmtD);
    exit;
}
?>