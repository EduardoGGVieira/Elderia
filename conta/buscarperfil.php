<?php
session_start();

if (!isset($_SESSION['tipo'])) {
    // Se tentar acessar direto, redireciona para login
    echo json_encode([
        'tipo' => false
    ]);
    exit;
} else {
    echo json_encode([
        'id' => $_SESSION['id'],
        'tipo' => $_SESSION['tipo'],
        'nome' => $_SESSION['nome'],
        'email' => $_SESSION['email']
    ]);
}