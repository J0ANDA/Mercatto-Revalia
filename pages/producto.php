<?php
// producto.php

session_start();
require_once '../includes/Auth.php';
require_once '../includes/Product.php';
require_once '../includes/Cart.php';
require_once '../includes/UserPreferences.php';

$auth = new Auth();
$product = new Product();
$preferences = UserPreferences::getInstance();

$id_producto = $_GET['id'] ?? 0;
$producto = $product->getProduct($id_producto);

if (!$producto) {
    header('Location: ../index.php');
    exit();
}

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $auth->isLoggedIn()) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'toggle_favorite':
            $user_id = $_SESSION['user_id'];
            if ($product->isFavorito($user_id, $id_producto)) {
                if ($product->removeFavorito($user_id, $id_producto)) {
                    $mensaje = 'Producto eliminado de favoritos';
                } else {
                    $error = 'Error al eliminar de favoritos';
                }
            } else {
                if ($product->addFavorito($user_id, $id_producto)) {
                    $mensaje = 'Producto añadido a favoritos';
                } else {
                    $error = 'Error al añadir a favoritos';
                }
            }
            break;

        case 'add_to_cart':
            $cantidad = (int)($_POST['cantidad'] ?? 1);
            if ($cantidad > 0 && $cantidad <= $producto['stock']) {
                $cart = new Cart($_SESSION['user_id']);
                if ($cart->addItem($id_producto, $cantidad)) {
                    $mensaje = 'Producto agregado al carrito correctamente';
                } else {
                    $error = 'Error al agregar el producto al carrito';
                }
            } else {
                $error = 'Cantidad no válida';
            }
            break;
    }
}

$is_favorito = $auth->isLoggedIn() ? $product->isFavorito($_SESSION['user_id'], $id_producto) : false;
?>

<!DOCTYPE html>
<html lang="<?= $preferences->getLanguage() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($producto['nombre']) ?> - Mercatto Revalia</title>
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
        padding-top: 120px; /* Ajustado para el navbar de dos filas */
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
    /* Estilos para el botón de favoritos */
    .btn-favorite {
        border: none;
        background: none;
        padding: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        margin-left: 10px;
    }
    .btn-favorite:hover {
        transform: scale(1.2);
    }
    .btn-favorite .material-icons {
        font-size: 30px;
        transition: all 0.2s;
    }
    /* Corazón no favorito */
    .btn-favorite:not(.is-favorite) .material-icons {
        color: #6c757d;
    }
    /* Corazón favorito (rojo) */
    .btn-favorite.is-favorite .material-icons {
        color: #dc3545;
    }
    /* Mostrar corazón roto en hover solo si es favorito */
    .btn-favorite.is-favorite:hover .material-icons {
        color: #dc3545;
    }
    .product-image-container {
        position: relative;
    }
    .product-title-container {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }
    .product-title-container h1 {
        margin: 0;
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
                        <?php if ($auth->isLoggedIn()): ?>
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
                        <?php else: ?>
                            <a href="login.php" class="btn btn-outline-light">
                                <span class="material-icons">login</span>
                                <?= $preferences->translate('nav_login') ?>
                            </a>
                            <a href="registro.php" class="btn btn-outline-light">
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

        <div class="row">
            <div class="col-md-6">
                <div class="product-image-container">
                    <div id="productImages" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php if (!empty($producto['fotos'])): ?>
                                <?php foreach ($producto['fotos'] as $index => $foto): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="<?= '../' . htmlspecialchars($foto['url_foto']) ?>" 
                                             class="d-block w-100" 
                                             alt="<?= htmlspecialchars($producto['nombre']) ?> - Imagen <?= $index + 1 ?>">
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="carousel-item active">
                                    <img src="../img/default.jpeg" class="d-block w-100" alt="Sin imagen">
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($producto['fotos']) && count($producto['fotos']) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#productImages" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#productImages" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Siguiente</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="product-title-container">
                    <h1><?= htmlspecialchars($producto['nombre']) ?></h1>
                    <?php if ($auth->isLoggedIn() && $producto['id_vendedor'] != $_SESSION['user_id']): ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="toggle_favorite">
                            <button type="submit" class="btn-favorite <?= $is_favorito ? 'is-favorite' : '' ?>" 
                                    title="<?= $is_favorito ? 'Quitar de favoritos' : 'Añadir a favoritos' ?>">
                                <span class="material-icons favorite-icon">
                                    <?= $is_favorito ? 'favorite' : 'favorite_border' ?>
                                </span>
                                <span class="material-icons broken-icon" style="display: none;">
                                    heart_broken
                                </span>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                <p class="lead">€<?= number_format($producto['precio'], 2) ?></p>
                
                <div class="mb-4">
                    <h5>Descripción:</h5>
                    <p><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
                </div>

                <div class="mb-4">
                    <h5>Detalles:</h5>
                    <ul class="list-unstyled">
                        <li><strong>Ubicación:</strong> <?= htmlspecialchars($producto['ciudad']) ?>, <?= htmlspecialchars($producto['provincia']) ?></li>
                        <li><strong>Stock disponible:</strong> <?= $producto['stock'] ?> unidades</li>
                        <li><strong>Vendedor:</strong> <?= htmlspecialchars($producto['vendedor_nombre']) ?></li>
                    </ul>
                </div>

                <?php if ($auth->isLoggedIn() && $producto['stock'] > 0): ?>
                    <form method="POST" class="mb-4">
                        <input type="hidden" name="action" value="add_to_cart">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <label for="cantidad" class="form-label">Cantidad:</label>
                                <input type="number" class="form-control" id="cantidad" name="cantidad" 
                                       value="1" min="1" max="<?= $producto['stock'] ?>">
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary">Agregar al Carrito</button>
                            </div>
                        </div>
                    </form>
                <?php elseif (!$auth->isLoggedIn()): ?>
                    <div class="alert alert-info">
                        <a href="login.php">Inicia sesión</a> para comprar este producto
                    </div>
                <?php endif; ?>

                <?php if ($producto['stock'] <= 0): ?>
                    <div class="alert alert-warning">
                        Producto agotado
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Efecto hover para mostrar corazón roto
    document.querySelectorAll('.btn-favorite.is-favorite').forEach(button => {
        const favoriteIcon = button.querySelector('.favorite-icon');
        const brokenIcon = button.querySelector('.broken-icon');

        button.addEventListener('mouseenter', () => {
            favoriteIcon.style.display = 'none';
            brokenIcon.style.display = 'block';
        });

        button.addEventListener('mouseleave', () => {
            favoriteIcon.style.display = 'block';
            brokenIcon.style.display = 'none';
        });
    });
    </script>
</body>
</html>