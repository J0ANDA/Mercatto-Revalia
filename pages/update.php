<?php
// update.php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require '../includes/conexion.php';

$producto_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];

    $stmt = $pdo->prepare("UPDATE productos SET nombre = ? WHERE id = ?");
    $stmt->execute([$nombre, $producto_id]);

    header('Location: listado.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$producto_id]);
$producto = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar Producto</title>
</head>
<body>
    <h1>Actualizar Producto</h1>
    <form method="POST">
        <label for="nombre">Nombre del Producto:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo $producto['nombre']; ?>" required>
        <button type="submit">Actualizar</button>
    </form>
    <a href="listado.php">Volver al listado</a>
</body>
</html>