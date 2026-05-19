<?php
session_start();
header('Content-Type: application/json');

require_once '../conexao.php';

// Verifica se o usuário logado é admin
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    echo json_encode(['error' => 'Acesso negado.']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'list') {

    // Busca todos os usuários menos admin
    $sql = "SELECT id_usuario as id, nome, email, tipo_usuario as tipo 
            FROM usuario 
            WHERE tipo_usuario != 'admin'";

    $result = $conexao->query($sql);

    $usuarios = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
    }

    echo json_encode($usuarios);
    exit;
}

/* NOVA ACTION */
elseif ($action === 'get_user') {

    $id = intval($_GET['id']);

    $sql = "SELECT 
                id_usuario as id,
                nome,
                email,
                tipo_usuario as tipo
            FROM usuario
            WHERE id_usuario = ?";

    $stmt = $conexao->prepare($sql);

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $result = $stmt->get_result();

    $usuario = $result->fetch_assoc();

    echo json_encode($usuario);

    exit;
}

else {
    echo json_encode(['error' => 'Ação inválida.']);
}
?>