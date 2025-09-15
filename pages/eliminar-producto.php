<?php
session_start();
require_once '../includes/Auth.php';
require_once '../includes/Product.php';
require_once '../includes/UserPreferences.php';

$auth = new Auth();
$preferences = UserPreferences::getInstance();

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_producto'])) {
    $product = new Product();
    $id_producto = (int)$_POST['id_producto'];
    
    if ($product->deleteProduct($id_producto, $_SESSION['user_id'])) {
        $_SESSION['mensaje'] = $preferences->translate('msg_deleted_product');
    } else {
        $_SESSION['mensaje'] = $preferences->translate('msg_delete_product_error');
    }
}

header('Location: mis-productos.php');
exit();
?>