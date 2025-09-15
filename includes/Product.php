<?php
require_once __DIR__ . '/../config/database.php';

class Product {
    private $db;
    private $pdo;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }

    public function createProduct($data) {
        $sql = "INSERT INTO productos (id_vendedor, nombre, descripcion, precio, stock, ciudad, provincia) 
                VALUES (:id_vendedor, :nombre, :descripcion, :precio, :stock, :ciudad, :provincia)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_vendedor' => $data['id_vendedor'],
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'],
                ':precio' => $data['precio'],
                ':stock' => $data['stock'],
                ':ciudad' => $data['ciudad'],
                ':provincia' => $data['provincia']
            ]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function updateProduct($data) {
        $sql = "UPDATE productos 
                SET nombre = :nombre, 
                    descripcion = :descripcion, 
                    precio = :precio, 
                    stock = :stock, 
                    ciudad = :ciudad, 
                    provincia = :provincia,
                    disponible = :disponible
                WHERE id_producto = :id_producto 
                AND id_vendedor = :id_vendedor";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id_producto' => $data['id_producto'],
                ':id_vendedor' => $data['id_vendedor'],
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'],
                ':precio' => $data['precio'],
                ':stock' => $data['stock'],
                ':ciudad' => $data['ciudad'],
                ':provincia' => $data['provincia'],
                ':disponible' => $data['disponible']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteProduct($id_producto, $id_vendedor) {
        $sql = "DELETE FROM productos WHERE id_producto = :id_producto AND id_vendedor = :id_vendedor";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id_producto' => $id_producto,
                ':id_vendedor' => $id_vendedor
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getProduct($id_producto) {
        $sql = "SELECT p.*, u.nombre as vendedor_nombre, u.id_usuario as id_vendedor,
                       (SELECT fp.url_foto 
                        FROM fotos_producto fp 
                        WHERE fp.id_producto = p.id_producto 
                        ORDER BY fp.id_foto ASC 
                        LIMIT 1) as foto_principal
                FROM productos p 
                JOIN usuarios u ON p.id_vendedor = u.id_usuario 
                WHERE p.id_producto = :id_producto";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_producto' => $id_producto]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($producto) {
                // Obtener todas las fotos del producto
                $producto['fotos'] = $this->getProductPhotos($id_producto);
            }
            
            return $producto;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getSellerProducts($id_vendedor) {
        $sql = "SELECT p.*, 
                       (SELECT fp.url_foto 
                        FROM fotos_producto fp 
                        WHERE fp.id_producto = p.id_producto 
                        ORDER BY fp.id_foto ASC 
                        LIMIT 1) as foto_url
                FROM productos p 
                WHERE p.id_vendedor = :id_vendedor 
                ORDER BY fecha_creacion DESC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_vendedor' => $id_vendedor]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAvailableProducts() {
        $sql = "SELECT p.*, u.nombre as vendedor,
                       (SELECT fp.url_foto 
                        FROM fotos_producto fp 
                        WHERE fp.id_producto = p.id_producto 
                        ORDER BY fp.id_foto ASC 
                        LIMIT 1) as foto_url
                FROM productos p 
                JOIN usuarios u ON p.id_vendedor = u.id_usuario 
                WHERE p.disponible = 1 AND p.stock > 0 
                ORDER BY p.fecha_creacion DESC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getFavoritos($user_id) {
        $sql = "SELECT p.*, f.fecha_agregado,
                       (SELECT fp.url_foto 
                        FROM fotos_producto fp 
                        WHERE fp.id_producto = p.id_producto 
                        ORDER BY fp.id_foto ASC 
                        LIMIT 1) as foto_url
                FROM productos p 
                INNER JOIN productos_favoritos f ON p.id_producto = f.id_producto 
                WHERE f.id_usuario = ?
                ORDER BY f.fecha_agregado DESC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function addFavorito($user_id, $producto_id) {
        $sql = "INSERT INTO productos_favoritos (id_usuario, id_producto) VALUES (?, ?)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$user_id, $producto_id]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function removeFavorito($user_id, $producto_id) {
        $sql = "DELETE FROM productos_favoritos WHERE id_usuario = ? AND id_producto = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$user_id, $producto_id]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function isFavorito($user_id, $producto_id) {
        $sql = "SELECT COUNT(*) FROM productos_favoritos WHERE id_usuario = ? AND id_producto = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user_id, $producto_id]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function saveProductPhotos($photos) {
        $sql = "INSERT INTO fotos_producto (id_producto, url_foto) VALUES (:id_producto, :url_foto)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($photos as $photo) {
                $stmt->execute([
                    ':id_producto' => $photo['id_producto'],
                    ':url_foto' => $photo['url_foto']
                ]);
            }
            return true;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getProductPhotos($id_producto) {
        $sql = "SELECT * FROM fotos_producto WHERE id_producto = :id_producto ORDER BY id_foto";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_producto' => $id_producto]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function deleteProductPhoto($id_foto, $id_producto, $id_vendedor) {
        // Verificar que el producto pertenece al vendedor
        $sql = "SELECT p.id_producto FROM productos p WHERE p.id_producto = :id_producto AND p.id_vendedor = :id_vendedor";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_producto' => $id_producto,
                ':id_vendedor' => $id_vendedor
            ]);
            
            if (!$stmt->fetch()) {
                return false; // El producto no pertenece al vendedor
            }
            
            // Obtener la URL de la foto para eliminar el archivo
            $sql = "SELECT url_foto FROM fotos_producto WHERE id_foto = :id_foto AND id_producto = :id_producto";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_foto' => $id_foto,
                ':id_producto' => $id_producto
            ]);
            $foto = $stmt->fetch();
            
            if ($foto) {
                // Eliminar el archivo físico
                $filePath = '../' . $foto['url_foto'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                // Eliminar el registro de la base de datos
                $sql = "DELETE FROM fotos_producto WHERE id_foto = :id_foto AND id_producto = :id_producto";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute([
                    ':id_foto' => $id_foto,
                    ':id_producto' => $id_producto
                ]);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
?> 