<?php
session_start(); // Iniciar a sessão

require_once 'conexao.php';
// Definir fuso horário
date_default_timezone_set('America/Sao_Paulo');


if (!isset($_SESSION['id'])) {
    $_SESSION['msg'] = "<p style='color: #f00; font-weight:bold;'>Erro: Você precisa estar logado para avaliar!</p>";
    header("Location: index.html");
    exit;
}


$id_usuario = $_SESSION['id'];


$id_profissional = filter_input(INPUT_POST, 'id_profissional', FILTER_VALIDATE_INT);
$estrela = filter_input(INPUT_POST, 'estrela', FILTER_VALIDATE_INT);
$mensagem = filter_input(INPUT_POST, 'mensagem', FILTER_SANITIZE_SPECIAL_CHARS);


if (!$id_profissional) {
    die("SEM ID");
}


if (!empty($estrela)) {


    $query_avaliacao = "INSERT INTO avaliacao(id_profissional, id_usuario, nota, comentario) VALUES (?, ?, ?, ?)";

    $stmt = mysqli_prepare($conexao, $query_avaliacao);

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "iiis", $id_profissional, $id_usuario, $estrela, $mensagem);


        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['msg'] = "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius:5px; margin-bottom: 10px;'>Avaliação enviada com sucesso! Obrigado.</div>";
        } else {
            $_SESSION['msg'] = "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius:5px; margin-bottom: 10px;'>Erro ao salvar no banco: " . mysqli_error($conexao) . "</div>";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['msg'] = "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius:5px; margin-bottom: 10px;'>Erro de preparação de consulta SQL: " . mysqli_error($conexao) . "</div>";
    }

    header("Location: perfil.php?id=" . $id_profissional);
    exit;

} else {

    $_SESSION['msg'] = "<div style='background: #fff3cd; color: #856404; padding: 10px; border-radius:5px; margin-bottom: 10px;'>Atenção: Selecione pelo menos 1 estrela para avaliar.</div>";


    header("Location: avaliar/avaliar.php?id=" . $id_profissional);
    exit;
}
