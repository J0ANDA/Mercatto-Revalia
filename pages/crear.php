<?php
// crear.php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];

    require 'includes/conexion.php';

    $stmt = $pdo->prepare("INSERT INTO productos (nombre) VALUES (?)");
    $stmt->execute([$nombre]);

    header('Location: listado.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Producto</title>
</head>
<body>
    <h1>Crear Producto</h1>
    <form method="POST">
        <label for="nombre">Nombre del Producto:</label>
        <input type="text" id="nombre" name="nombre" required>
        <button type="submit">Crear</button>
    </form>
    <a href="listado.php">Volver al listado</a>
</body>
</html>