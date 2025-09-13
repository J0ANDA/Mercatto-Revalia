<?php
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register($nombre, $email, $password) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
            return $stmt->execute([$nombre, $email, $hashedPassword]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id_usuario'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                return true;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        session_destroy();
        return true;
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateUser($id_usuario, $nombre, $email) {
        try {
            $stmt = $this->db->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id_usuario = ?");
            return $stmt->execute([$nombre, $email, $id_usuario]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateUserWithPassword($id_usuario, $nombre, $email, $password_actual, $password_nuevo) {
        try {
            // Verificar la contraseña actual
            $stmt = $this->db->prepare("SELECT password FROM usuarios WHERE id_usuario = ?");
            $stmt->execute([$id_usuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password_actual, $user['password'])) {
                $hashedPassword = password_hash($password_nuevo, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("UPDATE usuarios SET nombre = ?, email = ?, password = ? WHERE id_usuario = ?");
                return $stmt->execute([$nombre, $email, $hashedPassword, $id_usuario]);
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?> 