<?php
session_start();
require_once __DIR__ . "/conexion.php";
header("Content-Type: application/json; charset=utf-8");
if (isset($_SESSION["id_usuario"])) {
    echo json_encode([
        "autenticado" => true,
        "id_usuario" => $_SESSION["id_usuario"],
        "nombre_usuario" => $_SESSION["nombre_usuario"] ?? "Usuario"
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["autenticado" => false]);
}
exit;
