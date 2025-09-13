<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require 'includes/conexion.php';

// Obtener todos los productos
$stmt = $pdo->query("SELECT * FROM productos");
$productos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Productos</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="../js/main.js"></script>
</head>
<body>
    <h1>Listado de Productos</h1>
    <ul>
        <?php foreach ($productos as $producto): ?>
            <li data-producto-id="<?php echo $producto['id']; ?>">
                <h2><?php echo $producto['nombre']; ?></h2>
                <div id="estrellas-<?php echo $producto['id']; ?>" class="stars"></div>
                <div class="rating-info" id="rating-info-<?php echo $producto['id']; ?>"></div>
                <button onclick="eliminarVoto(<?php echo $producto['id']; ?>)">Eliminar mi voto</button>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
