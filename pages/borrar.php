<?php
// borrar.php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require '../includes/conexion.php';

$producto_id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
$stmt->execute([$producto_id]);

header('Location: listado.php');
exit();
?>