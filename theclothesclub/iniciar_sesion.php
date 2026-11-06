<?php
session_start();

$conexion = mysqli_connect("localhost", "root", "", "theclothesclub");

if (!$conexion) {
    die("No se pudo conectar con la base de datos.");
}

$id_usuario = $_POST["id_usuario"] ?? "";
$nombre_usuario = trim($_POST["nombre_usuario"] ?? "");
$contrasena = $_POST["contrasena"] ?? "";

$sql = "SELECT id_usuario, nombre_usuario, contrasena FROM usuarios WHERE id_usuario = ? AND nombre_usuario = ?";
$consulta = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($consulta, "is", $id_usuario, $nombre_usuario);
mysqli_stmt_execute($consulta);
$resultado = mysqli_stmt_get_result($consulta);
$usuario = mysqli_fetch_assoc($resultado);

if ($usuario) {
    $contrasena_valida = password_verify($contrasena, $usuario["contrasena"]);

    // Compatibilidad con cuentas creadas antes de activar el cifrado de contraseñas.
    // Si la contraseña anterior coincide, se actualiza automáticamente al formato seguro.
    if (!$contrasena_valida && hash_equals($usuario["contrasena"], $contrasena)) {
        $contrasena_valida = true;
        $nueva_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
        $actualizar = mysqli_prepare($conexion, "UPDATE usuarios SET contrasena = ? WHERE id_usuario = ?");
        mysqli_stmt_bind_param($actualizar, "si", $nueva_contrasena, $id_usuario);
        mysqli_stmt_execute($actualizar);
        mysqli_stmt_close($actualizar);
    }

    if ($contrasena_valida) {
        session_regenerate_id(true);
        $_SESSION["id_usuario"] = $usuario["id_usuario"];
        $_SESSION["nombre_usuario"] = $usuario["nombre_usuario"];
        echo "OK";
    } else {
        echo "El ID, el usuario o la contraseña son incorrectos.";
    }
} else {
    echo "El ID, el usuario o la contraseña son incorrectos.";
}

mysqli_stmt_close($consulta);
mysqli_close($conexion);
?>
