<?php

require_once 'Database.php';
require_once 'Libro.php';
require_once 'Autor.php';
require_once 'Categoria.php';

class GestorLibros {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    // Método para agregar un libro
    public function agregarLibro($titulo, $autorNombre, $categoriaNombre) {
        $autorId = $this->obtenerAutorId($autorNombre);
        $categoriaId = $this->obtenerCategoriaId($categoriaNombre);
        $stmt = $this->db->prepare("INSERT INTO libros (titulo, autor_id, categoria_id) VALUES (?, ?, ?)");
        return $stmt->execute([$titulo, $autorId, $categoriaId]);
    }

    // Método para editar un libro
    public function editarLibro($id, $titulo, $autorNombre, $categoriaNombre) {
        $autorId = $this->obtenerAutorId($autorNombre);
        $categoriaId = $this->obtenerCategoriaId($categoriaNombre);
        $stmt = $this->db->prepare("UPDATE libros SET titulo = ?, autor_id = ?, categoria_id = ? WHERE id = ?");
        return $stmt->execute([$titulo, $autorId, $categoriaId, $id]);
    }

    // Método para eliminar un libro
    public function eliminarLibro($id) {
        $stmt = $this->db->prepare("DELETE FROM libros WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Método para buscar libros por título, autor o categoría
    public function buscarLibros($query) {
        $query = "%" . strtolower($query) . "%";
        $stmt = $this->db->prepare("SELECT 
                                        l.id, l.titulo, l.estado,
                                        a.id AS autor_id, a.nombre AS autor_nombre,
                                        c.id AS categoria_id, c.nombre AS categoria_nombre
                                    FROM libros l
                                    INNER JOIN autores a ON l.autor_id = a.id
                                    INNER JOIN categorias c ON l.categoria_id = c.id
                                    WHERE LOWER(l.titulo) LIKE ? OR LOWER(a.nombre) LIKE ? OR LOWER(c.nombre) LIKE ?");
        $stmt->execute([$query, $query, $query]);
        $libros = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $autor = new Autor($row['autor_id'], $row['autor_nombre']);
            $categoria = new Categoria($row['categoria_id'], $row['categoria_nombre']);
            $libros[] = new Libro($row['id'], $row['titulo'], $autor, $categoria, $row['estado']);
        }
        return $libros;
    }

    // Obtener un solo libro por su ID
    public function getLibroById($id) {
        $stmt = $this->db->prepare("SELECT 
                                        l.id, l.titulo, l.estado,
                                        a.id AS autor_id, a.nombre AS autor_nombre,
                                        c.id AS categoria_id, c.nombre AS categoria_nombre
                                    FROM libros l
                                    INNER JOIN autores a ON l.autor_id = a.id
                                    INNER JOIN categorias c ON l.categoria_id = c.id
                                    WHERE l.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $autor = new Autor($row['autor_id'], $row['autor_nombre']);
            $categoria = new Categoria($row['categoria_id'], $row['categoria_nombre']);
            return new Libro($row['id'], $row['titulo'], $autor, $categoria, $row['estado']);
        }
        return null;
    }

    // Obtener todos los libros
    public function getTodosLosLibros() {
        $stmt = $this->db->query("SELECT 
                                        l.id, l.titulo, l.estado,
                                        a.id AS autor_id, a.nombre AS autor_nombre,
                                        c.id AS categoria_id, c.nombre AS categoria_nombre
                                    FROM libros l
                                    INNER JOIN autores a ON l.autor_id = a.id
                                    INNER JOIN categorias c ON l.categoria_id = c.id");
        $libros = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $autor = new Autor($row['autor_id'], $row['autor_nombre']);
            $categoria = new Categoria($row['categoria_id'], $row['categoria_nombre']);
            $libros[] = new Libro($row['id'], $row['titulo'], $autor, $categoria, $row['estado']);
        }
        return $libros;
    }

    // Préstamo y devolución
    public function prestarLibro($id) {
        $libro = $this->getLibroById($id);
        if ($libro && $libro->getEstado() === 'disponible') {
            $this->db->beginTransaction();
            try {
                // Actualizar estado del libro
                $stmt = $this->db->prepare("UPDATE libros SET estado = 'prestado' WHERE id = ?");
                $stmt->execute([$id]);

                // Registrar el préstamo
                $stmt = $this->db->prepare("INSERT INTO prestamos (libro_id, fecha_prestamo) VALUES (?, ?)");
                $stmt->execute([$id, date('Y-m-d')]);

                $this->db->commit();
                return true;
            } catch (Exception $e) {
                $this->db->rollBack();
                return false;
            }
        }
        return false;
    }
    
    // Método para devolver un libro
    public function devolverLibro($id) {
        $this->db->beginTransaction();
        try {
            // Actualizar estado del libro
            $stmt = $this->db->prepare("UPDATE libros SET estado = 'disponible' WHERE id = ?");
            $stmt->execute([$id]);
            // (Opcional) Eliminar el registro de préstamo
            $stmt = $this->db->prepare("DELETE FROM prestamos WHERE libro_id = ?");
            $stmt->execute([$id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // Métodos privados para manejar autores y categorías
    private function obtenerAutorId($nombre) {
        $stmt = $this->db->prepare("SELECT id FROM autores WHERE nombre = ?");
        $stmt->execute([$nombre]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row['id'];
        }
        $stmt = $this->db->prepare("INSERT INTO autores (nombre) VALUES (?)");
        $stmt->execute([$nombre]);
        return $this->db->lastInsertId();
    }

    private function obtenerCategoriaId($nombre) {
        $stmt = $this->db->prepare("SELECT id FROM categorias WHERE nombre = ?");
        $stmt->execute([$nombre]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row['id'];
        }
        $stmt = $this->db->prepare("INSERT INTO categorias (nombre) VALUES (?)");
        $stmt->execute([$nombre]);
        return $this->db->lastInsertId();
    }
}