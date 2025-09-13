<?php
require_once '../includes/UserPreferences.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $preferences = UserPreferences::getInstance();
    
    if (isset($_POST['theme'])) {
        $preferences->setTheme($_POST['theme']);
    }
    
    if (isset($_POST['lang'])) {
        $preferences->setLanguage($_POST['lang']);
    }
}

// Redirigir de vuelta a la página anterior
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
exit(); 