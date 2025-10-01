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

$mensaje = '';
$error = '';
$producto = null;

// Verificar que se proporcionó un ID de producto
if (!isset($_GET['id'])) {
    header('Location: mis-productos.php');
    exit();
}

$product = new Product();
$id_producto = (int)$_GET['id'];

// Obtener el producto y verificar que pertenece al usuario actual
$producto = $product->getProduct($id_producto);
if (!$producto || $producto['id_vendedor'] != $_SESSION['user_id']) {
    header('Location: mis-productos.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete_photo') {
        $id_foto = (int)($_POST['id_foto'] ?? 0);
        if ($product->deleteProductPhoto($id_foto, $id_producto, $_SESSION['user_id'])) {
            $mensaje = 'Foto eliminada correctamente';
        } else {
            $error = 'Error al eliminar la foto';
        }
    } else {
        $data = [
            'id_producto' => $id_producto,
            'id_vendedor' => $_SESSION['user_id'],
            'nombre' => $_POST['nombre'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'precio' => floatval($_POST['precio'] ?? 0),
            'stock' => intval($_POST['stock'] ?? 0),
            'ciudad' => $_POST['ciudad'] ?? '',
            'provincia' => $_POST['provincia'] ?? '',
            'disponible' => isset($_POST['disponible']) ? 1 : 0
        ];

        // Validaciones básicas
        if (empty($data['nombre']) || empty($data['descripcion']) || 
            $data['precio'] <= 0 || $data['stock'] < 0 || 
            empty($data['ciudad']) || empty($data['provincia'])) {
            $error = 'Todos los campos son obligatorios y los valores deben ser válidos';
        } else {
            if ($product->updateProduct($data)) {
                $_SESSION['mensaje'] = $preferences->translate('msg_product_updated');
                header('Location: mis-productos.php');
                exit();
            } else {
                $error = $preferences->translate('msg_error_product_updated');
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $preferences->getLanguage() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $preferences->translate('edit_product_title') ?> - Mercatto Revalia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
    <style>
    <?= $preferences->getThemeCSS() ?>
    .navbar {
        padding: 0;
        flex-direction: column;
    }
    .navbar-top {
        width: 100%;
        padding: 1rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .navbar-bottom {
        width: 100%;
        padding: 0.8rem 0;
    }
    .navbar-brand {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1.6rem;
        margin-right: 2rem;
        padding: 0;
    }
    .nav-link {
        display: flex !important;
        align-items: center;
        gap: 8px;
        padding: 0.5rem 1rem;
    }
    .material-icons {
        font-size: 24px;
    }
    .btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.5rem 1.2rem;
        font-size: 0.95rem;
    }
    .btn .material-icons {
        font-size: 20px;
    }
    .navbar-nav {
        gap: 0.75rem;
    }
    body {
        padding-top: 120px;
    }
    .main-container {
        padding-top: 2rem;
    }
    @media (max-width: 991.98px) {
        .navbar-collapse {
            padding: 1rem 0;
        }
        .navbar-nav {
            gap: 0.5rem;
        }
        .d-flex {
            gap: 0.5rem;
        }
    }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <!-- Top Row -->
        <div class="navbar-top">
            <div class="container d-flex align-items-center">
                <a class="navbar-brand" href="../index.php">
                    <span class="material-icons">storefront</span>
                    Mercatto Revalia
                </a>
                <div class="d-flex align-items-center gap-3 ms-auto">
                    <!-- Selector de tema -->
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <span class="material-icons"><?= $preferences->getTheme() === 'dark' ? 'dark_mode' : 'light_mode' ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form action="update_preferences.php" method="POST">
                                    <input type="hidden" name="theme" value="light">
                                    <button type="submit" class="dropdown-item">
                                        <span class="material-icons">light_mode</span>
                                        <?= $preferences->translate('theme_light') ?>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="update_preferences.php" method="POST">
                                    <input type="hidden" name="theme" value="dark">
                                    <button type="submit" class="dropdown-item">
                                        <span class="material-icons">dark_mode</span>
                                        <?= $preferences->translate('theme_dark') ?>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Selector de idioma -->
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <span class="material-icons">language</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form action="update_preferences.php" method="POST">
                                    <input type="hidden" name="lang" value="es">
                                    <button type="submit" class="dropdown-item">
                                        <?= $preferences->translate('lang_es') ?>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="update_preferences.php" method="POST">
                                    <input type="hidden" name="lang" value="en">
                                    <button type="submit" class="dropdown-item">
                                        <?= $preferences->translate('lang_en') ?>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Row -->
        <div class="navbar-bottom">
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <div class="d-flex align-items-center gap-3 me-auto">
                        <a class="btn btn-outline-light" href="mis-productos.php">
                            <span class="material-icons">inventory_2</span>
                            <?= $preferences->translate('nav_my_products') ?>
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <a href="crear-producto.php" class="btn btn-success">
                            <span class="material-icons">add_circle</span>
                            <?= $preferences->translate('nav_publish') ?>
                        </a>
                        <a href="favoritos.php" class="btn btn-outline-light">
                            <span class="material-icons">favorite</span>
                            <?= $preferences->translate('nav_favorites') ?>
                        </a>
                        <a href="carrito.php" class="btn btn-outline-light">
                            <span class="material-icons">shopping_cart</span>
                            <?= $preferences->translate('nav_cart') ?>
                        </a>
                        <a href="perfil.php" class="btn btn-outline-light">
                            <span class="material-icons">person</span>
                            <?= $preferences->translate('nav_profile') ?>
                        </a>
                        <a href="logout.php" class="btn btn-outline-light">
                            <span class="material-icons">logout</span>
                            <?= $preferences->translate('nav_logout') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container main-container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4"><?=$preferences->translate('edit_product_title') ?></h2>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="editar-producto.php?id=<?= $id_producto ?>" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="nombre" class="form-label"><?=$preferences->translate('product_name') ?></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required
                                       value="<?= htmlspecialchars($producto['nombre']) ?>">
                                <div class="invalid-feedback">
                                    <?=$preferences->translate('blank_product_name') ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label"><?=$preferences->translate('description') ?></label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required><?= htmlspecialchars($producto['descripcion']) ?></textarea>
                                <div class="invalid-feedback">
                                    <?=$preferences->translate('blank_description') ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="precio" class="form-label"><?=$preferences->translate('product_price') ?> (€)</label>
                                    <input type="number" class="form-control" id="precio" name="precio" 
                                           step="0.01" min="0.01" required
                                           value="<?= htmlspecialchars($producto['precio']) ?>">
                                    <div class="invalid-feedback">
                                        <?=$preferences->translate('blank_price') ?>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="stock" class="form-label"><?=$preferences->translate('product_stock') ?></label>
                                    <input type="number" class="form-control" id="stock" name="stock" 
                                           min="0" required
                                           value="<?= htmlspecialchars($producto['stock']) ?>">
                                    <div class="invalid-feedback">
                                        <?=$preferences->translate('blank_stock') ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ciudad" class="form-label"><?=$preferences->translate('city') ?></label>
                                    <input type="text" class="form-control" id="ciudad" name="ciudad" required
                                           value="<?= htmlspecialchars($producto['ciudad']) ?>">
                                    <div class="invalid-feedback">
                                        <?=$preferences->translate('blank_city') ?>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="provincia" class="form-label"><?=$preferences->translate('province') ?></label>
                                    <input type="text" class="form-control" id="provincia" name="provincia" required
                                           value="<?= htmlspecialchars($producto['provincia']) ?>">
                                    <div class="invalid-feedback">
                                        <?=$preferences->translate('blank_province') ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="disponible" name="disponible"
                                       <?= $producto['disponible'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="disponible"><?=$preferences->translate('product_available_sale') ?></label>
                            </div>

                            <!-- Sección de gestión de fotos -->
                            <div class="mb-4">
                                <h5><?=$preferences->translate('product_photos') ?></h5>
                                <?php if (!empty($producto['fotos'])): ?>
                                    <div class="row g-2">
                                        <?php foreach ($producto['fotos'] as $foto): ?>
                                            <div class="col-md-3 col-sm-4 col-6">
                                                <div class="card position-relative">
                                                    <img src="<?= '../' . htmlspecialchars($foto['url_foto']) ?>" 
                                                         class="card-img-top" 
                                                         style="height: 120px; object-fit: cover;"
                                                         alt="Foto del producto">
                                                    <div class="card-body p-2">
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta foto?')">
                                                            <input type="hidden" name="action" value="delete_photo">
                                                            <input type="hidden" name="id_foto" value="<?= $foto['id_foto'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger w-100">
                                                                <span class="material-icons" style="font-size: 16px;">delete</span>
                                                                Eliminar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <?=$preferences->translate('no_photos') ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"><?=$preferences->translate('save_changes') ?></button>
                                <a href="mis-productos.php" class="btn btn-outline-secondary"><?=$preferences->translate('btn_cancel') ?></a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initFormValidation();
        });
    </script>
</body>
</html> 