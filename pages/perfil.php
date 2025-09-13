<?php
session_start();
require_once '../includes/Auth.php';
require_once '../includes/UserPreferences.php';

$auth = new Auth();
$preferences = UserPreferences::getInstance();

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$mensaje = '';
$error = '';
$usuario = $auth->getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password_actual = $_POST['password_actual'] ?? '';
    $password_nuevo = $_POST['password_nuevo'] ?? '';
    $password_confirmar = $_POST['password_confirmar'] ?? '';

    if (empty($nombre) || empty($email)) {
        $error = $preferences->translate('msg_required_fields');
    } elseif (!empty($password_nuevo) || !empty($password_confirmar)) {
        if (empty($password_actual)) {
            $error = $preferences->translate('msg_current_password_required');
        } elseif ($password_nuevo !== $password_confirmar) {
            $error = $preferences->translate('msg_passwords_not_match');
        } elseif (strlen($password_nuevo) < 6) {
            $error = $preferences->translate('msg_password_length');
        } else {
            if ($auth->updateUserWithPassword($usuario['id_usuario'], $nombre, $email, $password_actual, $password_nuevo)) {
                $mensaje = $preferences->translate('msg_updated');
                $usuario = $auth->getCurrentUser(); // Recargar datos del usuario
            } else {
                $error = $preferences->translate('msg_wrong_password');
            }
        }
    } else {
        if ($auth->updateUser($usuario['id_usuario'], $nombre, $email)) {
            $mensaje = $preferences->translate('msg_updated');
            $usuario = $auth->getCurrentUser(); // Recargar datos del usuario
        } else {
            $error = $preferences->translate('msg_error');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $preferences->getLanguage() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $preferences->translate('profile_title') ?> - Mercatto Revalia</title>
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
                        <h2 class="card-title text-center mb-4"><?= $preferences->translate('profile_title') ?></h2>

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

                        <form method="POST" action="perfil.php" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="nombre" class="form-label"><?= $preferences->translate('profile_name') ?></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required
                                       value="<?= htmlspecialchars($usuario['nombre']) ?>">
                                <div class="invalid-feedback">
                                    <?= $preferences->translate('msg_name_required') ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label"><?= $preferences->translate('profile_email') ?></label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?= htmlspecialchars($usuario['email']) ?>">
                                <div class="invalid-feedback">
                                    <?= $preferences->translate('msg_email_required') ?>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h5><?= $preferences->translate('profile_change_password') ?></h5>
                            <p class="text-muted small"><?= $preferences->translate('profile_password_info') ?></p>

                            <div class="mb-3">
                                <label for="password_actual" class="form-label"><?= $preferences->translate('profile_current_password') ?></label>
                                <input type="password" class="form-control" id="password_actual" name="password_actual">
                            </div>

                            <div class="mb-3">
                                <label for="password_nuevo" class="form-label"><?= $preferences->translate('profile_new_password') ?></label>
                                <input type="password" class="form-control" id="password_nuevo" name="password_nuevo"
                                       minlength="6">
                                <div class="form-text"><?= $preferences->translate('msg_password_length') ?></div>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmar" class="form-label"><?= $preferences->translate('profile_confirm_password') ?></label>
                                <input type="password" class="form-control" id="password_confirmar" name="password_confirmar"
                                       minlength="6">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <span class="material-icons">save</span>
                                    <?= $preferences->translate('profile_save') ?>
                                </button>
                                <a href="mis-productos.php" class="btn btn-outline-secondary">
                                    <span class="material-icons">arrow_back</span>
                                    <?= $preferences->translate('profile_back') ?>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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