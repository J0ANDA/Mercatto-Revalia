<?php
// detalle.php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require 'includes/conexion.php';

$producto_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$producto_id]);
$producto = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $producto['nombre']; ?></title>
</head>
<body>
    <h1><?php echo $producto['nombre']; ?></h1>
    <a href="listado.php">Volver al listado</a>
</body>
</html>