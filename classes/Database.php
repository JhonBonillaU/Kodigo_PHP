<?php

class Database {
    private static $db;

    public static function connect() {
        if (self::$db === null) {
            try {
                $db_file = __DIR__ . '/../data/bibliotech.db';
                self::$db = new PDO("sqlite:$db_file");
                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$db->exec("CREATE TABLE IF NOT EXISTS autores (
                                    id INTEGER PRIMARY KEY,
                                    nombre TEXT NOT NULL
                                )");
                self::$db->exec("CREATE TABLE IF NOT EXISTS categorias (
                                    id INTEGER PRIMARY KEY,
                                    nombre TEXT NOT NULL
                                )");
                self::$db->exec("CREATE TABLE IF NOT EXISTS libros (
                                    id INTEGER PRIMARY KEY,
                                    titulo TEXT NOT NULL,
                                    autor_id INTEGER,
                                    categoria_id INTEGER,
                                    estado TEXT NOT NULL DEFAULT 'disponible',
                                    FOREIGN KEY(autor_id) REFERENCES autores(id),
                                    FOREIGN KEY(categoria_id) REFERENCES categorias(id)
                                )");
                self::$db->exec("CREATE TABLE IF NOT EXISTS prestamos (
                                    id INTEGER PRIMARY KEY,
                                    libro_id INTEGER,
                                    fecha_prestamo TEXT NOT NULL,
                                    FOREIGN KEY(libro_id) REFERENCES libros(id)
                                )");
            } catch (PDOException $e) {
                die("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }
        return self::$db;
    }
}