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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id_vendedor' => $_SESSION['user_id'],
        'nombre' => $_POST['nombre'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? '',
        'precio' => floatval($_POST['precio'] ?? 0),
        'stock' => intval($_POST['stock'] ?? 0),
        'ciudad' => $_POST['ciudad'] ?? '',
        'provincia' => $_POST['provincia'] ?? ''
    ];

    // Validaciones básicas
    if (empty($data['nombre']) || empty($data['descripcion']) || 
        $data['precio'] <= 0 || $data['stock'] <= 0 || 
        empty($data['ciudad']) || empty($data['provincia'])) {
        $error = 'Todos los campos son obligatorios y los valores deben ser válidos';
    } else {
        $product = new Product();
        $id_producto = $product->createProduct($data);
        
        if ($id_producto) {
            // Procesar las imágenes
            if (!empty($_FILES['fotos']['name'][0])) {
                $uploadDir = '../uploads/productos/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fotos = [];
                foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
                    $fileName = $_FILES['fotos']['name'][$key];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $uniqueName = uniqid('prod_' . $id_producto . '_') . '.' . $fileExt;
                    $targetFile = $uploadDir . $uniqueName;
                    // Modificar la ruta que se guarda en la base de datos
                    $dbFilePath = 'uploads/productos/' . $uniqueName; // Eliminar el '../'

                    // Validar tipo de archivo
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                    if (in_array($fileExt, $allowedTypes)) {
                        if (move_uploaded_file($tmp_name, $targetFile)) {
                            $fotos[] = [
                                'id_producto' => $id_producto,
                                'url_foto' => $dbFilePath // Guardar la ruta sin '../'
                            ];
                        }
                    }
                }

                if (!empty($fotos)) {
                    $product->saveProductPhotos($fotos);
                }
            }

            header('Location: mis-productos.php');
            exit();
        } else {
            $error = 'Error al crear el producto';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $preferences->getLanguage() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $preferences->translate('create_product_title') ?> - Mercatto Revalia</title>
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
                        <?php if ($auth->isLoggedIn()): ?>
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
                        <?php endif; ?>
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
                        <h2 class="card-title text-center mb-4">Publicar Nuevo Producto</h2>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="crear-producto.php" class="needs-validation" novalidate enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del Producto</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required
                                       value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>">
                                <div class="invalid-feedback">
                                    Por favor ingresa el nombre del producto
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required><?= isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '' ?></textarea>
                                <div class="invalid-feedback">
                                    Por favor ingresa una descripción
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="precio" class="form-label">Precio (€)</label>
                                    <input type="number" class="form-control" id="precio" name="precio" 
                                           step="0.01" min="0.01" required
                                           value="<?= isset($_POST['precio']) ? htmlspecialchars($_POST['precio']) : '' ?>">
                                    <div class="invalid-feedback">
                                        Por favor ingresa un precio válido
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="stock" class="form-label">Cantidad Disponible</label>
                                    <input type="number" class="form-control" id="stock" name="stock" 
                                           min="1" required
                                           value="<?= isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : '' ?>">
                                    <div class="invalid-feedback">
                                        Por favor ingresa una cantidad válida
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ciudad" class="form-label">Ciudad</label>
                                    <input type="text" class="form-control" id="ciudad" name="ciudad" required
                                           value="<?= isset($_POST['ciudad']) ? htmlspecialchars($_POST['ciudad']) : '' ?>">
                                    <div class="invalid-feedback">
                                        Por favor ingresa la ciudad
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="provincia" class="form-label">Provincia</label>
                                    <input type="text" class="form-control" id="provincia" name="provincia" required
                                           value="<?= isset($_POST['provincia']) ? htmlspecialchars($_POST['provincia']) : '' ?>">
                                    <div class="invalid-feedback">
                                        Por favor ingresa la provincia
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="fotos" class="form-label">Fotos del Producto</label>
                                <input type="file" class="form-control" id="fotos" name="fotos[]" multiple 
                                       accept="image/jpeg,image/png,image/gif">
                                <div class="form-text">
                                    Puedes seleccionar múltiples fotos. Formatos permitidos: JPG, PNG, GIF
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Publicar Producto</button>
                                <a href="../index.php" class="btn btn-outline-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Validación del formulario del lado del cliente
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
    })()
    </script>
</body>
</html>