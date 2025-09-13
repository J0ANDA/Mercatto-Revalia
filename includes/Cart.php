<?php
require_once __DIR__ . '/../config/database.php';

class Cart {
    private $db;
    private $user_id;

    public function __construct($user_id) {
        $this->db = Database::getInstance()->getConnection();
        $this->user_id = $user_id;
    }

    private function getOrCreateCart() {
        try {
            $stmt = $this->db->prepare("SELECT id_carrito FROM carritos WHERE id_usuario = ?");
            $stmt->execute([$this->user_id]);
            $cart = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cart) {
                return $cart['id_carrito'];
            }

            $stmt = $this->db->prepare("INSERT INTO carritos (id_usuario) VALUES (?)");
            $stmt->execute([$this->user_id]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function addItem($producto_id, $cantidad = 1) {
        try {
            $cart_id = $this->getOrCreateCart();
            if (!$cart_id) return false;

            // Verificar stock disponible
            $stmt = $this->db->prepare("SELECT stock FROM productos WHERE id_producto = ?");
            $stmt->execute([$producto_id]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$producto || $producto['stock'] < $cantidad) {
                return false;
            }

            // Verificar si el producto ya está en el carrito
            $stmt = $this->db->prepare("
                SELECT cantidad FROM carrito_productos 
                WHERE id_carrito = ? AND id_producto = ?
            ");
            $stmt->execute([$cart_id, $producto_id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item) {
                $nueva_cantidad = $item['cantidad'] + $cantidad;
                if ($nueva_cantidad > $producto['stock']) {
                    return false;
                }

                $stmt = $this->db->prepare("
                    UPDATE carrito_productos 
                    SET cantidad = ? 
                    WHERE id_carrito = ? AND id_producto = ?
                ");
                return $stmt->execute([$nueva_cantidad, $cart_id, $producto_id]);
            }

            $stmt = $this->db->prepare("
                INSERT INTO carrito_productos (id_carrito, id_producto, cantidad) 
                VALUES (?, ?, ?)
            ");
            return $stmt->execute([$cart_id, $producto_id, $cantidad]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function removeItem($producto_id) {
        try {
            $cart_id = $this->getOrCreateCart();
            if (!$cart_id) return false;

            $stmt = $this->db->prepare("
                DELETE FROM carrito_productos 
                WHERE id_carrito = ? AND id_producto = ?
            ");
            return $stmt->execute([$cart_id, $producto_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateQuantity($producto_id, $cantidad) {
        try {
            $cart_id = $this->getOrCreateCart();
            if (!$cart_id) return false;

            // Verificar stock disponible
            $stmt = $this->db->prepare("SELECT stock FROM productos WHERE id_producto = ?");
            $stmt->execute([$producto_id]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$producto || $producto['stock'] < $cantidad) {
                return false;
            }

            $stmt = $this->db->prepare("
                UPDATE carrito_productos 
                SET cantidad = ? 
                WHERE id_carrito = ? AND id_producto = ?
            ");
            return $stmt->execute([$cantidad, $cart_id, $producto_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getItems() {
        try {
            $cart_id = $this->getOrCreateCart();
            if (!$cart_id) return [];

            $stmt = $this->db->prepare("
                SELECT p.*, cp.cantidad 
                FROM carrito_productos cp 
                JOIN productos p ON cp.id_producto = p.id_producto 
                WHERE cp.id_carrito = ?
            ");
            $stmt->execute([$cart_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getTotal() {
        try {
            $cart_id = $this->getOrCreateCart();
            if (!$cart_id) return 0;

            $stmt = $this->db->prepare("
                SELECT SUM(p.precio * cp.cantidad) as total 
                FROM carrito_productos cp 
                JOIN productos p ON cp.id_producto = p.id_producto 
                WHERE cp.id_carrito = ?
            ");
            $stmt->execute([$cart_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function clear() {
        try {
            $cart_id = $this->getOrCreateCart();
            if (!$cart_id) return false;

            $stmt = $this->db->prepare("DELETE FROM carrito_productos WHERE id_carrito = ?");
            return $stmt->execute([$cart_id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?> 