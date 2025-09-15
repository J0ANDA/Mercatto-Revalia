<?php
// index.php

session_start();
require_once 'includes/Auth.php';
require_once 'includes/Product.php';
require_once 'includes/UserPreferences.php';

$auth = new Auth();
$product = new Product();
$preferences = UserPreferences::getInstance();

// Obtener el ID del usuario actual si está logueado
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Modificar la consulta para excluir los productos del usuario actual
$productos = $product->getAvailableProducts();
?>

<!DOCTYPE html>
<html lang="<?= $preferences->getLanguage() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mercatto Revalia - <?= $preferences->translate('nav_home') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
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
    /* Calculate the total height of the navbar (top + bottom) and add some extra space */
    body {
        padding-top: 120px; /* Ajustado para el navbar de dos filas */
    }
    .main-container {
        padding-top: 2rem;
    }
    .navbar-brand {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1.6rem;
        margin-right: 2rem;
        padding: 0;
    }
    .search-form {
        flex: 1;
        max-width: 500px;
    }
    .search-input {
        width: 100%;
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }
    .search-input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }
    .search-input:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.15);
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
    @media (max-width: 991.98px) {
        .navbar-collapse {
            padding: 1rem 0;
        }
        .navbar-nav {
            gap: 0.5rem;
        }
        .search-form {
            max-width: 100%;
            margin: 1rem 0;
        }
    }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        
        <div class="navbar-top">
            <div class="container d-flex align-items-center">
                <a class="navbar-brand" href="index.php">
                    <span class="material-icons">storefront</span>
                    Mercatto Revalia
                </a>
                <form class="search-form" action="search.php" method="GET">
                    <input type="search" class="search-input" placeholder="Buscar productos..." name="q">
                </form>
                <div class="d-flex align-items-center gap-3 ms-auto">
                    <!-- Selector de tema -->
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <span class="material-icons"><?= $preferences->getTheme() === 'dark' ? 'dark_mode' : 'light_mode' ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form action="pages/update_preferences.php" method="POST">
                                    <input type="hidden" name="theme" value="light">
                                    <button type="submit" class="dropdown-item">
                                        <span class="material-icons">light_mode</span>
                                        <?= $preferences->translate('theme_light') ?>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="pages/update_preferences.php" method="POST">
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
                                <form action="pages/update_preferences.php" method="POST">
                                    <input type="hidden" name="lang" value="es">
                                    <button type="submit" class="dropdown-item">
                                        <?= $preferences->translate('lang_es') ?>
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="pages/update_preferences.php" method="POST">
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
                        <a class="btn btn-outline-light" href="pages/mis-productos.php">
                            <span class="material-icons">inventory_2</span>
                            <?= $preferences->translate('nav_my_products') ?>
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <?php if ($auth->isLoggedIn()): ?>
                            <a href="pages/crear-producto.php" class="btn btn-success">
                                <span class="material-icons">add_circle</span>
                                <?= $preferences->translate('nav_publish') ?>
                            </a>
                            <a href="pages/favoritos.php" class="btn btn-outline-light">
                                <span class="material-icons">favorite</span>
                                <?= $preferences->translate('nav_favorites') ?>
                            </a>
                            <a href="pages/carrito.php" class="btn btn-outline-light">
                                <span class="material-icons">shopping_cart</span>
                                <?= $preferences->translate('nav_cart') ?>
                            </a>
                            <a href="pages/perfil.php" class="btn btn-outline-light">
                                <span class="material-icons">person</span>
                                <?= $preferences->translate('nav_profile') ?>
                            </a>
                            <a href="pages/logout.php" class="btn btn-outline-light">
                                <span class="material-icons">logout</span>
                                <?= $preferences->translate('nav_logout') ?>
                            </a>
                        <?php else: ?>
                            <a href="pages/login.php" class="btn btn-outline-light">
                                <span class="material-icons">login</span>
                                <?= $preferences->translate('nav_login') ?>
                            </a>
                            <a href="pages/registro.php" class="btn btn-outline-light">
                                <span class="material-icons">person_add</span>
                                <?= $preferences->translate('nav_register') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container main-container">
        <div class="row">
            <?php 
            // Filtrar productos que no son del usuario actual
            $productos_filtrados = array_filter($productos, function($producto) use ($current_user_id) {
                return $producto['id_vendedor'] != $current_user_id;
            });
            
            if (empty($productos_filtrados)): 
            ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <?= $preferences->translate('msg_empty_products') ?>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($productos_filtrados as $producto): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?= !empty($producto['foto_url']) ? htmlspecialchars($producto['foto_url']) : 'img/default.jpeg' ?>" 
                                 class="card-img-top" alt="<?= htmlspecialchars($producto['nombre']) ?>">
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
                                    <a href="pages/producto.php?id=<?= $producto['id_producto'] ?>" class="btn btn-primary"><?= $preferences->translate('product_details') ?></a>
                                    <small class="text-muted"><?= $preferences->translate('product_stock') ?>: <?= $producto['stock'] ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>