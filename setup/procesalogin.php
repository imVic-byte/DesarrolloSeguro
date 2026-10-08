<?php

include("setup.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar metodo y campos obligatorios
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['frmusuario']) || empty($_POST['frmpassword'])) {
    header('Location: ../index.php?login_error=missing_fields');
    exit();
}

$usuario = trim($_POST['frmusuario']);
$password = trim($_POST['frmpassword']);

// Sanitizacion y validacion estricta de formato de correo
if (!filter_var($usuario, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../index.php?login_error=invalid_email');
    exit();
}

$con = conectar();
mysqli_set_charset($con, 'utf8');

// Consulta preparada para mitigar inyecciones SQL y resolver unicidad (LIMIT 1, estado activo)
$sql = "SELECT Id, nombre, email, password, estado FROM usuarios WHERE email = ? AND estado = '1' LIMIT 1";
$stmt = mysqli_prepare($con, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) === 1) {
        $datos = mysqli_fetch_assoc($result);

        // Verificacion segura de contrasena (compatible con hash Bcrypt y texto plano legado)
        $password_valida = false;
        if (password_verify($password, $datos['password'])) {
            $password_valida = true;
        } elseif ($password === $datos['password']) {
            $password_valida = true;
        }

        if ($password_valida) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $datos['Id'];
            $_SESSION['nombre'] = $datos['nombre'];
            mysqli_stmt_close($stmt);
            mysqli_close($con);
            header('Location: ../index.php');
            exit();
        }
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($con);
header('Location: ../index.php?login_error=invalid_credentials');
exit();