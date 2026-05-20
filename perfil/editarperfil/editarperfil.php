<?php
session_start();
header('Content-Type: application/json');
require_once '../../conexao.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado.']);
    exit;
}

$id = $_SESSION['id'];
$tipo = $_SESSION['tipo'];

mysqli_begin_transaction($conexao);

try {
    $stmtUser = mysqli_prepare($conexao, "UPDATE usuario SET nome=?, email=?, telefone=? WHERE id_usuario=?");
    mysqli_stmt_bind_param($stmtUser, "sssi", $_POST['nome'], $_POST['email'], $_POST['telefone'], $id);
    mysqli_stmt_execute($stmtUser);

    if ($tipo === 'idoso') {
        $sqlIdoso = "UPDATE idoso
                     SET data_nascimento=?, alergias=?, informacoes_saude=?, possui_acessibilidade=?, necessidades_acessibilidade=?
                     WHERE id_idoso=?";

        $stmtIdoso = mysqli_prepare($conexao, $sqlIdoso);
        mysqli_stmt_bind_param(
            $stmtIdoso,
            "sssisi",
            $_POST['data_nascimento'],
            $_POST['alergias'],
            $_POST['informacoes_saude'],
            $_POST['possui_acessibilidade'],
            $_POST['necessidades_acessibilidade'],
            $id
        );
        mysqli_stmt_execute($stmtIdoso);
    }

    else if ($tipo === 'profissional') {
        $sqlProf = "UPDATE profissional
                    SET registro_profissional=?, especialidade=?, biografia=?, localizacao=?
                    WHERE id_profissional=?";

        $stmtProf = mysqli_prepare($conexao, $sqlProf);
        mysqli_stmt_bind_param(
            $stmtProf,
            "ssssi",
            $_POST['registro_profissional'],
            $_POST['especialidade'],
            $_POST['biografia'],
            $_POST['localizacao'],
            $id
        );
        mysqli_stmt_execute($stmtProf);

        $titulo_cert = $_POST['titulo_certificado'] ?? '';
        $data_emissao_raw = $_POST['data_emissao_cert'] ?? '';
        $data_emissao_cert = !empty($data_emissao_raw) ? date('Y-m-d', strtotime($data_emissao_raw)) : null;

        if (!empty($titulo_cert) && !empty($data_emissao_cert) && !empty($_FILES['url_documento_cert']['name'])) {
            $ext = strtolower(pathinfo($_FILES['url_documento_cert']['name'], PATHINFO_EXTENSION));

            if ($ext !== 'pdf') {
                throw new Exception('O certificado precisa ser um arquivo PDF.');
            }

            if (!is_dir('../../uploads/certificados')) {
                mkdir('../../uploads/certificados', 0777, true);
            }

            $nome_cert = 'certificado_' . $id . '_' . time() . '.pdf';
            $destino_cert = '../../uploads/certificados/' . $nome_cert;

            if (!move_uploaded_file($_FILES['url_documento_cert']['tmp_name'], $destino_cert)) {
                throw new Exception('Erro ao fazer upload do certificado.');
            }

            $sql_cert = "INSERT INTO certificado
                         (id_profissional, titulo, data_emissao, url_documento, status)
                         VALUES (?, ?, ?, ?, 'pendente')";

            $stmt_cert = mysqli_prepare($conexao, $sql_cert);
            mysqli_stmt_bind_param($stmt_cert, 'isss', $id, $titulo_cert, $data_emissao_cert, $destino_cert);
            mysqli_stmt_execute($stmt_cert);
            mysqli_stmt_close($stmt_cert);
        }
    }

    mysqli_commit($conexao);
    $_SESSION['nome'] = $_POST['nome'];

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    mysqli_rollback($conexao);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>