<?php

include("setup/setup.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['nombre']) || !isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

$id_restaurante = filter_var($_SESSION['id'], FILTER_VALIDATE_INT);
if ($id_restaurante === false || $id_restaurante <= 0) {
    header('Location: index.php');
    exit();
}

$usuario = $_SESSION['nombre'];
$comentario = trim($_POST['comentario'] ?? '');

if (empty($comentario) || strlen($comentario) > 1000) {
    header('Location: index.php?id=' . $id_restaurante);
    exit();
}

$con = conectar();
$sql = "INSERT INTO comentarios (usuario, comentario, id_restaurante) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($con, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssi", $usuario, $comentario, $id_restaurante);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
mysqli_close($con);

header('Location: index.php?id=' . $id_restaurante);
exit();
?>