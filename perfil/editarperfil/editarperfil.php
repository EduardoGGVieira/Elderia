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

mysqli_begin_transaction($conexao);

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
        // BUSCAR DADOS ATUAIS PARA GESTÃO DE ARQUIVO
        $sqlBusca = "SELECT url_documento FROM profissional WHERE id_profissional = ?";
        $stmtB = mysqli_prepare($conexao, $sqlBusca);
        mysqli_stmt_bind_param($stmtB, "i", $id);
        mysqli_stmt_execute($stmtB);
        $resB = mysqli_stmt_get_result($stmtB);
        $profAtual = mysqli_fetch_assoc($resB);
        
        $caminhoFinal = $profAtual['url_documento'];
        $manterCert = $_POST['manter_certificado'] ?? '1';

        // LÓGICA DE REMOÇÃO OU SUBSTITUIÇÃO
        if ($manterCert === '0' || !empty($_FILES['url_documento']['name'])) {
            if (!empty($profAtual['url_documento']) && file_exists($profAtual['url_documento'])) {
                unlink($profAtual['url_documento']); // APAGA DO PC
            }
            $caminhoFinal = null;
        }

        // LÓGICA DE NOVO UPLOAD
        if (!empty($_FILES['url_documento']['name'])) {
            $nome_arq = 'cert_edit_' . $id . '_' . time() . '.pdf';
            $destino = '../../uploads/certificados/' . $nome_arq;
            if (move_uploaded_file($_FILES['url_documento']['tmp_name'], $destino)) {
                $caminhoFinal = $destino;
            }
        }

        // UPDATE INCLUINDO A URL DO DOCUMENTO E RESETANDO VALIDAÇÃO
        $sqlProf = "UPDATE profissional SET registro_profissional=?, especialidade=?, biografia=?, localizacao=?, data_emissao=?, url_documento=?, documento_validado=0 WHERE id_profissional=?";
        $stmtProf = mysqli_prepare($conexao, $sqlProf);
        mysqli_stmt_bind_param($stmtProf, "ssssssi", 
            $_POST['registro_profissional'], 
            $_POST['especialidade'], 
            $_POST['biografia'], 
            $_POST['localizacao'], 
            $_POST['data_emissao'], // Essa é a data do registro profissional geral
            $caminhoFinal,
            $id
        );
        mysqli_stmt_execute($stmtProf);

        // --- LÓGICA DE INSERÇÃO DO NOVO CERTIFICADO ADICIONAL CORRIGIDA ---
        $titulo_cert = $_POST['titulo_certificado'] ?? '';
        $data_emissao_raw = $_POST['data_emissao_cert'] ?? '';
        $data_emissao_cert = !empty($data_emissao_raw) ? date('Y-m-d', strtotime($data_emissao_raw)) : null;

        if (!empty($titulo_cert) && !empty($data_emissao_cert) && !empty($_FILES['url_documento_cert']['name'])) {

            $ext = strtolower(pathinfo($_FILES['url_documento_cert']['name'], PATHINFO_EXTENSION));

            if ($ext !== 'pdf') {
                throw new Exception('O certificado precisa ser um arquivo PDF.');
            }

            $nome_cert = 'certificado_' . $id . '_' . time() . '.pdf';
            $destino_cert = '../../uploads/certificados/' . $nome_cert;

            if (!move_uploaded_file($_FILES['url_documento_cert']['tmp_name'], $destino_cert)) {
                throw new Exception('Erro ao fazer upload do certificado.');
            }

            $sql_cert = "INSERT INTO certificado 
                (id_profissional, titulo, data_emissao, url_documento) 
                VALUES (?, ?, ?, ?)";

            $stmt_cert = mysqli_prepare($conexao, $sql_cert);
            mysqli_stmt_bind_param($stmt_cert, 'isss', $id, $titulo_cert, $data_emissao_cert, $destino_cert);
            mysqli_stmt_execute($stmt_cert);
            mysqli_stmt_close($stmt_cert);
        }
    }

    mysqli_commit($conexao);
    $_SESSION['nome'] = $_POST['nome']; // atualiza o nome na sessao na hora

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    mysqli_rollback($conexao);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>