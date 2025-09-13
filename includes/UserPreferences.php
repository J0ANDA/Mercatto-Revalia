<?php
class UserPreferences {
    private $lang;
    private $theme;
    private static $instance = null;
    private $translations = [];

    private function __construct() {
        $this->initializePreferences();
        $this->loadTranslations();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new UserPreferences();
        }
        return self::$instance;
    }

    private function initializePreferences() {
        // Inicializar idioma
        if (isset($_COOKIE['user_lang'])) {
            $this->lang = $_COOKIE['user_lang'];
        } else {
            $this->lang = 'es'; // Idioma por defecto
            setcookie('user_lang', 'es', time() + (86400 * 30), '/'); // 30 días
        }

        // Inicializar tema
        if (isset($_COOKIE['user_theme'])) {
            $this->theme = $_COOKIE['user_theme'];
        } else {
            $this->theme = 'light'; // Tema por defecto
            setcookie('user_theme', 'light', time() + (86400 * 30), '/');
        }
    }

    private function loadTranslations() {
        $langFile = __DIR__ . "/../lang/{$this->lang}.php";
        if (file_exists($langFile)) {
            $this->translations = require $langFile;
        }
    }

    public function setLanguage($lang) {
        if ($lang === 'es' || $lang === 'en') {
            $this->lang = $lang;
            setcookie('user_lang', $lang, time() + (86400 * 30), '/');
            $this->loadTranslations();
        }
    }

    public function setTheme($theme) {
        if ($theme === 'light' || $theme === 'dark') {
            $this->theme = $theme;
            setcookie('user_theme', $theme, time() + (86400 * 30), '/');
        }
    }

    public function getLanguage() {
        return $this->lang;
    }

    public function getTheme() {
        return $this->theme;
    }

    public function translate($key) {
        return $this->translations[$key] ?? $key;
    }

    public function getThemeCSS() {
        $commonNavStyles = '
            .navbar {
                padding: 0.8rem 0;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1030;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
            }
            .navbar-brand {
                font-size: 1.5rem;
                font-weight: 600;
                padding: 0.5rem 1rem;
                border-radius: 8px;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .navbar .nav-item {
                margin: 0 4px;
            }
            .navbar .btn {
                border-radius: 20px;
                padding: 8px 16px;
                transition: all 0.2s ease;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 0.95rem;
            }
            .navbar .btn:hover {
                transform: translateY(-1px);
            }
            .navbar .btn-success {
                background-color: #28a745;
                border-color: #28a745;
                box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
            }
            .navbar .btn-success:hover {
                background-color: #218838;
                border-color: #1e7e34;
                box-shadow: 0 4px 6px rgba(40, 167, 69, 0.3);
            }
            .navbar .btn-outline-light {
                border-width: 1.5px;
            }
            .navbar .btn-outline-light:hover {
                background-color: rgba(255, 255, 255, 0.1);
            }
            body {
                padding-top: 80px;
            }
            .dropdown-menu {
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                padding: 0.5rem;
                margin-top: 10px;
            }
            .dropdown-item {
                border-radius: 8px;
                padding: 0.5rem 1rem;
                margin: 0.2rem 0;
            }
            .material-icons {
                font-size: 20px;
            }

            /* Theme Switch Styles */
            .theme-switch {
                background-color: #ffffff;
                border-radius: 20px;
                padding: 4px;
                height: 36px;
                min-width: 120px;
                display: flex;
                align-items: center;
                position: relative;
                cursor: pointer;
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
            .theme-switch-track {
                background-color: #e9ecef;
                border-radius: 20px;
                height: 100%;
                width: 100%;
                position: absolute;
                left: 0;
                transition: background-color 0.3s ease;
            }
            .theme-switch-thumb {
                background-color: #ffffff;
                border-radius: 50%;
                width: 28px;
                height: 28px;
                position: absolute;
                left: 4px;
                transition: transform 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            }
            .theme-switch.dark .theme-switch-thumb {
                transform: translateX(84px);
            }
            .theme-switch-icon {
                color: #6c757d;
                font-size: 16px;
            }
            .theme-switch-labels {
                display: flex;
                justify-content: space-between;
                width: 100%;
                padding: 0 12px;
                position: relative;
                z-index: 1;
            }
            .theme-switch-label {
                font-size: 0.85rem;
                font-weight: 500;
                color: #6c757d;
                user-select: none;
            }
            .theme-switch.dark .theme-switch-track {
                background-color: #2d3436;
            }
            .theme-switch.dark .theme-switch-thumb {
                background-color: #2d3436;
            }
            .theme-switch.dark .theme-switch-icon {
                color: #ffffff;
            }
        ';

        if ($this->theme === 'dark') {
            return $commonNavStyles . '
                :root {
                    --bg-color: #121212;
                    --text-color: #ffffff;
                    --card-bg: #1e1e1e;
                    --border-color: #333333;
                    --muted-color: #a0a0a0;
                    --nav-bg: rgba(13, 110, 253, 0.95);
                }
                body {
                    background-color: var(--bg-color);
                    color: var(--text-color);
                }
                .navbar {
                    background-color: var(--nav-bg) !important;
                    backdrop-filter: blur(10px);
                }
                .navbar-top {
                    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
                }
                .search-input {
                    background: rgba(255, 255, 255, 0.15);
                }
                .search-input:focus {
                    background: rgba(255, 255, 255, 0.2);
                }
                .navbar-brand:hover {
                    background-color: rgba(255, 255, 255, 0.1);
                }
                .card {
                    background-color: #242424;
                    border: 1px solid #383838;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                }
                .card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
                    border-color: #4a4a4a;
                }
                .card-body {
                    color: var(--text-color);
                }
                .card-title {
                    color: #ffffff;
                }
                .card-text {
                    color: #e0e0e0;
                }
                .text-muted {
                    color: #909090 !important;
                }
                .btn-primary {
                    background-color: #0d6efd;
                    border-color: #0d6efd;
                }
                .btn-primary:hover {
                    background-color: #0b5ed7;
                    border-color: #0a58ca;
                }
                .table {
                    color: var(--text-color);
                }
                .modal-content {
                    background-color: var(--card-bg);
                    color: var(--text-color);
                }
                .dropdown-menu {
                    background-color: var(--card-bg);
                    border-color: var(--border-color);
                }
                .dropdown-item {
                    color: var(--text-color);
                }
                .dropdown-item:hover {
                    background-color: #2d2d2d;
                    color: var(--text-color);
                }
                .form-control {
                    background-color: #2d2d2d;
                    border-color: var(--border-color);
                    color: var(--text-color);
                }
                .form-control:focus {
                    background-color: #2d2d2d;
                    border-color: #4d4d4d;
                    color: var(--text-color);
                }
                .table-light {
                    background-color: var(--card-bg);
                    color: var(--text-color);
                }
                .alert {
                    background-color: var(--card-bg);
                    border-color: var(--border-color);
                    color: var(--text-color);
                }
                .alert-info {
                    background-color: #1a2935;
                    border-color: #0f5884;
                    color: #9fcdff;
                }
                .alert-success {
                    background-color: #1e4620;
                    border-color: #2f6c2f;
                    color: #8fd19e;
                }
                .alert-danger {
                    background-color: #471925;
                    border-color: #842029;
                    color: #ea868f;
                }
                .form-text {
                    color: var(--muted-color);
                }
                .btn-outline-secondary {
                    color: var(--text-color);
                    border-color: var(--border-color);
                }
                .btn-outline-secondary:hover {
                    background-color: #2d2d2d;
                    border-color: var(--border-color);
                }
            ';
        }
        
        // Tema claro
        return $commonNavStyles . '
            :root {
                --nav-bg: rgba(13, 110, 253, 0.95);
            }
            .navbar {
                background-color: var(--nav-bg) !important;
                backdrop-filter: blur(10px);
            }
            .navbar-brand:hover {
                background-color: rgba(255, 255, 255, 0.1);
            }
        ';
    }
} 