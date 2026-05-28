<?php
session_start();

if (!isset($_SESSION['tipo_usuario'])) {
    echo json_encode([
        'tipo_usuario' => false
    ]);
    exit;
} else {
    echo json_encode([
        'id' => $_SESSION['id'],
        'tipo' => $_SESSION['tipo_usuario'],
        'nome' => $_SESSION['nome'],
        'email' => $_SESSION['email']
    ]);
}