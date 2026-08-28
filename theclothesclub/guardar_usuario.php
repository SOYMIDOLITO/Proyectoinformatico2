<?php

$conexion = mysqli_connect("localhost", "root", "", "theclothesclub");

if (!$conexion) {
    die("Error de conexión");
}

$id_usuario = $_POST["id_usuario"];
$nombre_usuario = $_POST["nombre_usuario"];
$contrasena = $_POST["contraseña"];

$sql = "INSERT INTO usuarios (id_usuario, nombre_usuario, contrasena)
        VALUES ('$id_usuario', '$nombre_usuario', '$contrasena')";

if (mysqli_query($conexion, $sql)) {

    echo "♥ Usuario registrado correctamente ♥";

} else {

    echo "Error al guardar el usuario: " . mysqli_error($conexion);

}

mysqli_close($conexion);

?>
