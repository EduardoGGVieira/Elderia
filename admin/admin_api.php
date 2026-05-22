<?php
session_start();
header('Content-Type: application/json');

require_once '../conexao.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    echo json_encode(['error' => 'Acesso negado.']);
    exit;
}

$action = $_GET['action'] ?? '';

function json_out($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function apagar_arquivo_se_existir($caminho) {
    if (!empty($caminho) && file_exists($caminho)) {
        unlink($caminho);
    }
}

if ($action === 'list') {
    $sql = "SELECT id_usuario as id, nome, email, tipo_usuario as tipo
            FROM usuario
            WHERE tipo_usuario != 'admin'
            ORDER BY tipo_usuario, nome";

    $result = $conexao->query($sql);
    $usuarios = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
    }

    json_out($usuarios);
}

elseif ($action === 'delete_user') {
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        json_out(['success' => false, 'message' => 'ID inválido.']);
    }

    // Busca o tipo do usuário e caminhos de arquivos antes de deletar
    $stmtBusca = $conexao->prepare("SELECT tipo_usuario FROM usuario WHERE id_usuario = ?");
    $stmtBusca->bind_param("i", $id);
    $stmtBusca->execute();
    $resBusca = $stmtBusca->get_result();
    $user = $resBusca->fetch_assoc();

    if (!$user) {
        json_out(['success' => false, 'message' => 'Usuário não encontrado.']);
    }

    $tipo = $user['tipo_usuario'];

    // Iniciamos uma transação para garantir que ou deleta tudo ou não deleta nada
    $conexao->begin_transaction();

    try {
        if ($tipo === 'profissional') {
            // 1. Remove arquivos de certificados e registros
            $stmtC = $conexao->prepare("SELECT url_documento FROM certificado WHERE id_profissional = ?");
            $stmtC->bind_param("i", $id);
            $stmtC->execute();
            $resC = $stmtC->get_result();
            while ($cert = $resC->fetch_assoc()) {
                apagar_arquivo_se_existir($cert['url_documento']);
            }
            $conexao->query("DELETE FROM certificado WHERE id_profissional = $id");

            // 2. Remove arquivo de documentação profissional
            $stmtP = $conexao->prepare("SELECT documentacao_url FROM profissional WHERE id_profissional = ?");
            $stmtP->bind_param("i", $id);
            $stmtP->execute();
            $prof = $stmtP->get_result()->fetch_assoc();
            if ($prof) {
                apagar_arquivo_se_existir($prof['documentacao_url']);
            }

            // 3. Limpa agenda, consultas e avaliações vinculadas ao profissional
            $conexao->query("DELETE FROM agenda_disponivel WHERE id_profissional = $id");
            $conexao->query("DELETE FROM consulta WHERE id_profissional = $id");
            $conexao->query("DELETE FROM avaliacao WHERE id_profissional = $id");
            $conexao->query("DELETE FROM profissional WHERE id_profissional = $id");

        } elseif ($tipo === 'idoso') {
            // Limpa consultas e registro de idoso
            $conexao->query("DELETE FROM consulta WHERE id_idoso = $id");
            $conexao->query("DELETE FROM idoso WHERE id_idoso = $id");
        }

        // Limpa avaliações que o usuário possa ter escrito (comum a ambos os tipos)
        $conexao->query("DELETE FROM avaliacao WHERE id_usuario = $id");

        // Por fim, deleta o registro principal na tabela usuario
        $stmt = $conexao->prepare("DELETE FROM usuario WHERE id_usuario = ? AND tipo_usuario != 'admin'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
        
        $conexao->commit();
        json_out(['success' => true]);

    } catch (Exception $e) {
        $conexao->rollback();
        json_out(['success' => false, 'message' => 'Erro ao deletar: ' . $e->getMessage()]);
    }
}

elseif ($action === 'get_user') {
    $id = intval($_GET['id'] ?? 0);

    $sql = "SELECT
                u.id_usuario as id,
                u.nome,
                u.email,
                u.telefone,
                u.cpf,
                u.tipo_usuario as tipo,
                p.registro_profissional,
                p.especialidade,
                p.biografia,
                p.localizacao,
                p.documentacao_numero,
                p.documentacao_url,
                p.documentacao_status
            FROM usuario u
            LEFT JOIN profissional p ON u.id_usuario = p.id_profissional
            WHERE u.id_usuario = ?";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    json_out($stmt->get_result()->fetch_assoc());
}

elseif ($action === 'get_documents') {
    $id = intval($_GET['id'] ?? 0);

    $sqlDoc = "SELECT
                    p.id_profissional,
                    p.documentacao_numero,
                    p.documentacao_url,
                    p.documentacao_status
               FROM profissional p
               WHERE p.id_profissional = ?";

    $stmtDoc = $conexao->prepare($sqlDoc);
    $stmtDoc->bind_param("i", $id);
    $stmtDoc->execute();
    $documento = $stmtDoc->get_result()->fetch_assoc();

    $sqlCert = "SELECT
                    id_certificado,
                    titulo,
                    data_emissao,
                    url_documento,
                    status
                FROM certificado
                WHERE id_profissional = ?
                ORDER BY id_certificado DESC";

    $stmtCert = $conexao->prepare($sqlCert);
    $stmtCert->bind_param("i", $id);
    $stmtCert->execute();

    $certificados = [];
    $resCert = $stmtCert->get_result();

    while ($row = $resCert->fetch_assoc()) {
        $certificados[] = $row;
    }

    json_out([
        'success' => true,
        'documento' => $documento,
        'certificados' => $certificados
    ]);
}

elseif ($action === 'validar_certificado' || $action === 'reprovar_certificado') {
    $id = intval($_GET['id'] ?? 0);
    $status = $action === 'validar_certificado' ? 'aprovado' : 'reprovado';

    $stmt = $conexao->prepare("UPDATE certificado SET status = ? WHERE id_certificado = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    json_out(['success' => true]);
}

elseif ($action === 'excluir_certificado') {
    $id = intval($_GET['id'] ?? 0);

    $stmtBusca = $conexao->prepare("SELECT url_documento FROM certificado WHERE id_certificado = ?");
    $stmtBusca->bind_param("i", $id);
    $stmtBusca->execute();
    $cert = $stmtBusca->get_result()->fetch_assoc();

    if ($cert) {
        apagar_arquivo_se_existir($cert['url_documento']);
    }

    $stmt = $conexao->prepare("DELETE FROM certificado WHERE id_certificado = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    json_out(['success' => true]);
}

elseif ($action === 'validar_documento' || $action === 'reprovar_documento') {
    $id = intval($_GET['id'] ?? 0);
    $status = $action === 'validar_documento' ? 'aprovado' : 'reprovado';

    $stmt = $conexao->prepare("UPDATE profissional SET documentacao_status = ? WHERE id_profissional = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    json_out(['success' => true]);
}

elseif ($action === 'excluir_documento') {
    $id = intval($_GET['id'] ?? 0);

    $stmtBusca = $conexao->prepare("SELECT documentacao_url FROM profissional WHERE id_profissional = ?");
    $stmtBusca->bind_param("i", $id);
    $stmtBusca->execute();
    $doc = $stmtBusca->get_result()->fetch_assoc();

    if ($doc) {
        apagar_arquivo_se_existir($doc['documentacao_url']);
    }

    $stmt = $conexao->prepare("
        UPDATE profissional
        SET documentacao_url = NULL,
            documentacao_numero = NULL,
            documentacao_status = 'pendente'
        WHERE id_profissional = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    json_out(['success' => true]);
}

else {
    json_out(['error' => 'Ação inválida.']);
}
?>