<?php

include("setup/setup.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar metodo HTTP y autenticacion activa
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['nombre'])) {
    header('Location: index.php');
    exit();
}

// Validar identificador del restaurante en sesion
$id_restaurante = null;
if (isset($_SESSION['id']) && filter_var($_SESSION['id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
    $id_restaurante = (int)$_SESSION['id'];
} else {
    header('Location: index.php');
    exit();
}

// Sanitizar y validar contenido del comentario
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
if ($comentario === '' || strlen($comentario) > 1000) {
    header('Location: index.php?id=' . $id_restaurante . '&comment_error=invalid_length');
    exit();
}

// Forzar identidad autenticada desde el servidor para mitigar suplantacion
$usuario = $_SESSION['nombre'];

$con = conectar();
mysqli_set_charset($con, 'utf8');

// Insercion segura mediante sentencia preparada
$sql = "INSERT INTO comentarios (usuario, comentario, id_restaurante) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($con, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssi", $usuario, $comentario, $id_restaurante);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

mysqli_close($con);

header('Location: index.php?id=' . $id_restaurante . '&comment_success=1');
exit();