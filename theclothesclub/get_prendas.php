<?php
require_once __DIR__ . '/conexion.php';
session_start();
if (!isset($_SESSION['id_usuario'])) { $_SESSION['id_usuario'] = 1; }
$id_usuario = (int)$_SESSION['id_usuario'];

$sql = "SELECT id_prenda, nombre_prenda, tipo, color, estilo, foto FROM prendas WHERE id_usuario = ? ORDER BY tipo, nombre_prenda";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id_usuario);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$prendas = [];
while ($row = mysqli_fetch_assoc($res)) { $prendas[] = $row; }
mysqli_stmt_close($stmt);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($prendas, JSON_UNESCAPED_UNICODE);

