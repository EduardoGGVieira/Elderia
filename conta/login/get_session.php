<?php
session_start();
header('Content-Type: application/json'); 

if (isset($_SESSION['id'])) {
    echo json_encode([
        'logged_in' => true,
        'id' => $_SESSION['id'],
        'nome' => $_SESSION['nome'],
        'tipo' => $_SESSION['tipo'],
        'email' => $_SESSION['email'],

        // ABOBRINHA TESTE DE AUTORIA
        'u' => $_SESSION['u']
    ]);
} else {
    echo json_encode([
        'logged_in' => false
    ]);
}
?>