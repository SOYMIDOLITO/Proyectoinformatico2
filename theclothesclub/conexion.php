<?php
$servidor   = "localhost";
$usuario_db = "root";
$clave_db   = "";
$nombre_db  = "theclothesclub";

$conexion = @mysqli_connect($servidor, $usuario_db, $clave_db, $nombre_db);
if (!$conexion) {
    $conexion_servidor = @mysqli_connect($servidor, $usuario_db, $clave_db);
    if (!$conexion_servidor) {
        $error_msg = "No se pudo conectar a MySQL. Asegurate de que el módulo MySQL esté iniciado en XAMPP.";
    } else {
        $error_msg = "MySQL está activo, pero la base de datos '$nombre_db' no existe aún. Por favor importá 'theclothesclub.sql' en phpMyAdmin.";
        mysqli_close($conexion_servidor);
    }
    $isJson = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
           || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST');
    if ($isJson) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(["ok" => false, "mensaje" => $error_msg], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        die("<div style='font-family:Arial,sans-serif;padding:20px;background:#ffebee;color:#c62828;border:1px solid #ef9a9a;border-radius:8px;margin:20px;'><strong>Error:</strong> $error_msg</div>");
    }
}
mysqli_set_charset($conexion, "utf8mb4");
