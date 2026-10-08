<?php

include("setup.php");

// Validar metodo HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

// Validar presencia de campos requeridos
if (empty($_POST['reg_nombre']) || empty($_POST['reg_email']) || empty($_POST['reg_password'])) {
    header('Location: ../index.php?reg_error=missing_fields');
    exit();
}

$nombre   = trim($_POST['reg_nombre']);
$email    = trim($_POST['reg_email']);
$password = trim($_POST['reg_password']);

// Validacion de longitud de nombre
if (strlen($nombre) < 2 || strlen($nombre) > 100) {
    header('Location: ../index.php?reg_error=invalid_name');
    exit();
}

// Validacion estricta de formato de correo electronico
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../index.php?reg_error=invalid_email');
    exit();
}

// Validacion de longitud minima de contrasena
if (strlen($password) < 4) {
    header('Location: ../index.php?reg_error=short_password');
    exit();
}

$con = conectar();
mysqli_set_charset($con, 'utf8');

// Comprobacion previa de unicidad para evitar duplicados en la base de datos
$check_sql = "SELECT Id FROM usuarios WHERE email = ? LIMIT 1";
$check_stmt = mysqli_prepare($con, $check_sql);

if ($check_stmt) {
    mysqli_stmt_bind_param($check_stmt, "s", $email);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        mysqli_stmt_close($check_stmt);
        mysqli_close($con);
        header('Location: ../index.php?reg_error=email_exists');
        exit();
    }
    mysqli_stmt_close($check_stmt);
}

// Generacion segura de hash de contrasena (Bcrypt)
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insercion con sentencia preparada
$insert_sql = "INSERT INTO usuarios (nombre, email, password, estado) VALUES (?, ?, ?, '1')";
$insert_stmt = mysqli_prepare($con, $insert_sql);

if ($insert_stmt) {
    mysqli_stmt_bind_param($insert_stmt, "sss", $nombre, $email, $password_hash);
    if (mysqli_stmt_execute($insert_stmt)) {
        mysqli_stmt_close($insert_stmt);
        mysqli_close($con);
        header('Location: ../index.php?reg_success=1');
        exit();
    }
    mysqli_stmt_close($insert_stmt);
}

mysqli_close($con);
header('Location: ../index.php?reg_error=db_error');
exit();
