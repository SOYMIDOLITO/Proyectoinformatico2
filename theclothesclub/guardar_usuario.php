<?php
$conexion = mysqli_connect("localhost", "root", "", "theclothesclub");

if (!$conexion) {
    die("No se pudo conectar con la base de datos.");
}

$id_usuario = $_POST["id_usuario"] ?? "";
$nombre_usuario = trim($_POST["nombre_usuario"] ?? "");
$contrasena = $_POST["contrasena"] ?? "";

if (!preg_match("/^[0-9]{4}$/", $id_usuario) || $nombre_usuario == "" || strlen($contrasena) < 6) {
    die("Revisá los datos. El ID debe tener 4 números y la contraseña al menos 6 caracteres.");
}

// La contraseña se guarda protegida, no como texto visible.
$contrasena_segura = password_hash($contrasena, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (id_usuario, nombre_usuario, contrasena) VALUES (?, ?, ?)";
$consulta = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($consulta, "iss", $id_usuario, $nombre_usuario, $contrasena_segura);

if (mysqli_stmt_execute($consulta)) {
    echo "REGISTRO_OK";
} else {
    if (mysqli_errno($conexion) == 1062) {
        echo "Ese ID de usuario ya está registrado. Elegí otro.";
    } else {
        echo "No se pudo registrar el usuario. Revisá la tabla Usuarios y sus columnas.";
    }
}

mysqli_stmt_close($consulta);
mysqli_close($conexion);
?>
