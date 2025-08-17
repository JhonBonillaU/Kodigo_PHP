<?php

class Libro {
    private $id;
    private $titulo;
    private $autor;
    private $categoria;
    private $estado;

    public function __construct($id, $titulo, Autor $autor, Categoria $categoria, $estado = 'disponible') {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->autor = $autor;
        $this->categoria = $categoria;
        $this->estado = $estado;
    }

    // Getters y Setters
    public function getId() { return $this->id; }
    public function getTitulo() { return $this->titulo; }
    public function getAutor() { return $this->autor; }
    public function getCategoria() { return $this->categoria; }
    public function getEstado() { return $this->estado; }

    public function setTitulo($titulo) { $this->titulo = $titulo; }
    public function setAutor(Autor $autor) { $this->autor = $autor; }
    public function setCategoria(Categoria $categoria) { $this->categoria = $categoria; }
    public function setEstado($estado) { $this->estado = $estado; }
}
