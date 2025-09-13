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

$product = new Product();
$favoritos = $product->getFavoritos($_SESSION['user_id']);
$mensaje = '';
$error = '';

// Procesar acciones de favoritos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $producto_id = (int)$_POST['producto_id'];
    
    if ($_POST['action'] === 'remove') {
        if ($product->removeFavorito($_SESSION['user_id'], $producto_id)) {
            $mensaje = $preferences->translate('msg_favorite_removed');
            // Recargar la lista de favoritos
            $favoritos = $product->getFavoritos($_SESSION['user_id']);
        } else {
            $error = $preferences->translate('msg_error_removing_favorite');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $preferences->getLanguage() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $preferences->translate('nav_favorites') ?> - Mercatto Revalia</title>
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
    .favorite-icon {
        color: #dc3545;
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

    <div class="container mt-4">
        <h1><?= $preferences->translate('nav_favorites') ?></h1>

        <?php if ($mensaje): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($favoritos)): ?>
            <div class="alert alert-info">
                <?= $preferences->translate('msg_no_favorites') ?>
                <a href="../index.php"><?= $preferences->translate('msg_explore_products') ?></a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($favoritos as $producto): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?= !empty($producto['foto_url']) ? '../' . htmlspecialchars($producto['foto_url']) : '../img/default.jpeg' ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($producto['nombre']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($producto['nombre']) ?></h5>
                                <p class="card-text text-truncate-2"><?= htmlspecialchars($producto['descripcion']) ?></p>
                                <p class="card-text">
                                    <strong class="text-primary h5">€<?= number_format($producto['precio'], 2) ?></strong><br>
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($producto['ciudad']) ?>
                                    </small>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="producto.php?id=<?= $producto['id_producto'] ?>" class="btn btn-primary"><?= $preferences->translate('product_details') ?></a>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="producto_id" value="<?= $producto['id_producto'] ?>">
                                        <button type="submit" class="btn btn-outline-danger">
                                            <span class="material-icons">favorite</span>
                                            <?= $preferences->translate('product_remove_favorite') ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>