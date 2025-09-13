<?php
session_start();
require_once '../includes/Auth.php';
require_once '../includes/Product.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_producto'])) {
    $product = new Product();
    $id_producto = (int)$_POST['id_producto'];
    
    if ($product->deleteProduct($id_producto, $_SESSION['user_id'])) {
        $_SESSION['mensaje'] = 'Producto eliminado correctamente';
    } else {
        $_SESSION['mensaje'] = 'Error al eliminar el producto';
    }
}

header('Location: mis-productos.php');
exit();
?>