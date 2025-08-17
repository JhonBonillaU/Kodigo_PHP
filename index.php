<?php

require_once 'classes/GestorLibros.php';

$gestor = new GestorLibros();
$mensaje = '';

// Lógica para manejar acciones (sin cambios)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
        $gestor->agregarLibro($_POST['titulo'], $_POST['autor'], $_POST['categoria']);
        $mensaje = "Libro agregado exitosamente.";
    } elseif (isset($_POST['accion']) && $_POST['accion'] === 'editar') {
        $gestor->editarLibro($_POST['id'], $_POST['titulo'], $_POST['autor'], $_POST['categoria']);
        $mensaje = "Libro editado exitosamente.";
    } elseif (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
        $gestor->eliminarLibro($_POST['id']);
        $mensaje = "Libro eliminado.";
    } elseif (isset($_POST['accion']) && $_POST['accion'] === 'prestar') {
        if ($gestor->prestarLibro($_POST['id'])) {
            $mensaje = "Libro prestado exitosamente.";
        } else {
            $mensaje = "El libro no está disponible para préstamo.";
        }
    } elseif (isset($_POST['accion']) && $_POST['accion'] === 'devolver') {
        if ($gestor->devolverLibro($_POST['id'])) {
            $mensaje = "Libro devuelto exitosamente.";
        } else {
            $mensaje = "Error al devolver el libro.";
        }
    }
    // Redireccionar para evitar re-envío de formulario
    header("Location: index.php?mensaje=" . urlencode($mensaje));
    exit();
}

// Lógica de búsqueda (sin cambios)
$libros = [];
if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $libros = $gestor->buscarLibros($_GET['buscar']);
} else {
    $libros = $gestor->getTodosLosLibros();
}

// Manejar mensajes de éxito (sin cambios)
if (isset($_GET['mensaje'])) {
    $mensaje = urldecode($_GET['mensaje']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Libros - Bibliotech</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Gestión de Libros</h1>

    <?php if (!empty($mensaje)): ?>
        <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <h2>Agregar Libro</h2>
    <form action="index.php" method="POST">
        <input type="hidden" name="accion" value="agregar">
        Título: <input type="text" name="titulo" required>
        Autor: <input type="text" name="autor" required>
        Categoría: <input type="text" name="categoria" required>
        <button type="submit">Agregar Libro</button>
    </form>
    
    <hr>
    
    <h2>Buscar Libros</h2>
    <form action="index.php" method="GET">
        <input type="text" name="buscar" placeholder="Título, autor o categoría">
        <button type="submit">Buscar</button>
    </form>

    <hr>

    <h2>Listado de Libros</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Autor</th>
                <th>Categoría</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($libros as $libro): ?>
                <tr>
                    <td><?php echo htmlspecialchars($libro->getId()); ?></td>
                    <td><?php echo htmlspecialchars($libro->getTitulo()); ?></td>
                    <td><?php echo htmlspecialchars($libro->getAutor()->getNombre()); ?></td>
                    <td><?php echo htmlspecialchars($libro->getCategoria()->getNombre()); ?></td>
                    <td><?php echo htmlspecialchars($libro->getEstado()); ?></td>
                    <td class="acciones">
                        <form action="index.php" method="POST">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?php echo $libro->getId(); ?>">
                            <button type="submit">Eliminar</button>
                        </form>
                        
                        <?php if ($libro->getEstado() === 'disponible'): ?>
                            <form action="index.php" method="POST">
                                <input type="hidden" name="accion" value="prestar">
                                <input type="hidden" name="id" value="<?php echo $libro->getId(); ?>">
                                <button type="submit">Prestar</button>
                            </form>
                        <?php else: ?>
                            <form action="index.php" method="POST">
                                <input type="hidden" name="accion" value="devolver">
                                <input type="hidden" name="id" value="<?php echo $libro->getId(); ?>">
                                <button type="submit">Devolver</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>