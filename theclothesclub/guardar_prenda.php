<?php
session_start();
require_once __DIR__ . "/conexion.php";

// Función auxiliar para determinar si la petición es AJAX / Fetch
function esPeticionJson() {
    return (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
}

// 1. Verificación de sesión
if (!isset($_SESSION["id_usuario"])) {
    if (esPeticionJson() || $_SERVER["REQUEST_METHOD"] === "POST") {
        header("Content-Type: application/json; charset=utf-8");
        http_response_code(401);
        echo json_encode([
            "ok" => false,
            "mensaje" => "Tenés que iniciar sesión para guardar prendas."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        header("Location: index.html");
        exit;
    }
}

$id_usuario = (int)$_SESSION["id_usuario"];
$mensaje_error = "";
$mensaje_exito = "";

// 2. Procesamiento del formulario (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $es_json = esPeticionJson();

    $nombre = trim($_POST["nombre"] ?? "");
    $tipo = trim($_POST["tipo"] ?? "");
    $color = trim($_POST["color"] ?? "");
    $estilo = trim($_POST["estilo"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");

    // Temporada puede venir como array (select multiple) o como string simple
    $temporada_input = $_POST["temporada"] ?? "";
    if (is_array($temporada_input)) {
        $temporada = implode(", ", array_map("trim", $temporada_input));
    } else {
        $temporada = trim((string)$temporada_input);
    }

    // Etiquetas puede venir como JSON string (desde el modal de mi_armario) o texto separado por comas
    $etiquetas_raw = $_POST["etiquetas"] ?? "";
    $etiquetas = [];
    if (is_array($etiquetas_raw)) {
        $etiquetas = $etiquetas_raw;
    } elseif (is_string($etiquetas_raw) && $etiquetas_raw !== "") {
        $decoded = json_decode($etiquetas_raw, true);
        if (is_array($decoded)) {
            $etiquetas = $decoded;
        } else {
            $etiquetas = array_map("trim", explode(",", $etiquetas_raw));
        }
    }

    // Validación de campos obligatorios
    if ($nombre === "" || $tipo === "") {
        if ($es_json) {
            header("Content-Type: application/json; charset=utf-8");
            http_response_code(400);
            echo json_encode(["ok" => false, "mensaje" => "El nombre y el tipo de prenda son obligatorios."], JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            $mensaje_error = "El nombre y el tipo de prenda son obligatorios.";
        }
    }

    // Procesamiento y guardado de la foto
    $foto_relativa = null;
    $archivo_subido_fisico = null;

    if (empty($mensaje_error) && isset($_FILES["foto"]) && $_FILES["foto"]["error"] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
            $mensaje_error = "Hubo un error al subir la imagen (Código: " . $_FILES["foto"]["error"] . ").";
        } elseif ($_FILES["foto"]["size"] > 8 * 1024 * 1024) {
            $mensaje_error = "La imagen no puede superar los 8 MB.";
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES["foto"]["tmp_name"]);
            finfo_close($finfo);

            $tipos_permitidos = [
                "image/jpeg" => "jpg",
                "image/png"  => "png",
                "image/webp" => "webp",
                "image/gif"  => "gif"
            ];

            if (!isset($tipos_permitidos[$mime])) {
                $mensaje_error = "Formato de imagen inválido. Solo se admiten JPG, PNG, WEBP o GIF.";
            } else {
                $carpeta = __DIR__ . "/imagenes/";
                if (!is_dir($carpeta)) {
                    mkdir($carpeta, 0777, true);
                }

                $extension = $tipos_permitidos[$mime];
                $nombre_archivo = "prenda_" . date("Ymd_His") . "_" . bin2hex(random_bytes(4)) . "." . $extension;
                $ruta_destino = $carpeta . $nombre_archivo;

                if (move_uploaded_file($_FILES["foto"]["tmp_name"], $ruta_destino)) {
                    $foto_relativa = "imagenes/" . $nombre_archivo;
                    $archivo_subido_fisico = $ruta_destino;
                } else {
                    $mensaje_error = "No se pudo guardar la imagen en el servidor. Revisá los permisos de la carpeta 'imagenes/'.";
                }
            }
        }
    }

    // Inserción en la base de datos
    if (empty($mensaje_error)) {
        mysqli_begin_transaction($conexion);

        try {
            // 1. Insertar la prenda
            $sql_prenda = "INSERT INTO Prendas (id_usuario, nombre_prenda, foto, tipo, color, estilo, temporada, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conexion, $sql_prenda);
            if (!$stmt) {
                throw new Exception("Error al preparar consulta de prenda: " . mysqli_error($conexion));
            }

            mysqli_stmt_bind_param(
                $stmt,
                "isssssss",
                $id_usuario,
                $nombre,
                $foto_relativa,
                $tipo,
                $color,
                $estilo,
                $temporada,
                $descripcion
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error al ejecutar guardado de prenda: " . mysqli_stmt_error($stmt));
            }

            $id_prenda = mysqli_insert_id($conexion);
            mysqli_stmt_close($stmt);

            // 2. Insertar etiquetas y relacionar
            foreach ($etiquetas as $etiqueta) {
                $etiqueta = trim((string)$etiqueta);
                if ($etiqueta === "") {
                    continue;
                }

                // Buscar si la etiqueta ya existe
                $buscar = mysqli_prepare($conexion, "SELECT id_etiqueta FROM Etiquetas WHERE nombre_etiqueta = ? LIMIT 1");
                mysqli_stmt_bind_param($buscar, "s", $etiqueta);
                mysqli_stmt_execute($buscar);
                mysqli_stmt_bind_result($buscar, $id_etiqueta_existente);
                
                if (mysqli_stmt_fetch($buscar)) {
                    $id_etiqueta = $id_etiqueta_existente;
                    mysqli_stmt_close($buscar);
                } else {
                    mysqli_stmt_close($buscar);
                    // Insertar nueva etiqueta
                    $crear = mysqli_prepare($conexion, "INSERT INTO Etiquetas (nombre_etiqueta) VALUES (?)");
                    mysqli_stmt_bind_param($crear, "s", $etiqueta);
                    mysqli_stmt_execute($crear);
                    $id_etiqueta = mysqli_insert_id($conexion);
                    mysqli_stmt_close($crear);
                }

                // Asociar prenda con etiqueta (evitar duplicados con IGNORE)
                $relacion = mysqli_prepare($conexion, "INSERT IGNORE INTO Prenda_Etiqueta (id_prenda, id_etiqueta) VALUES (?, ?)");
                mysqli_stmt_bind_param($relacion, "ii", $id_prenda, $id_etiqueta);
                mysqli_stmt_execute($relacion);
                mysqli_stmt_close($relacion);
            }

            mysqli_commit($conexion);

            if ($es_json) {
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode([
                    "ok" => true,
                    "mensaje" => "¡Prenda guardada correctamente!",
                    "id_prenda" => $id_prenda,
                    "foto" => $foto_relativa
                ], JSON_UNESCAPED_UNICODE);
                exit;
            } else {
                header("Location: mi_armario.php?guardado=1");
                exit;
            }

        } catch (Throwable $e) {
            mysqli_rollback($conexion);
            // Si la foto se subió pero falló la BD, borrar el archivo
            if ($archivo_subido_fisico && file_exists($archivo_subido_fisico)) {
                @unlink($archivo_subido_fisico);
            }

            if ($es_json) {
                header("Content-Type: application/json; charset=utf-8");
                http_response_code(500);
                echo json_encode([
                    "ok" => false,
                    "mensaje" => "Error al guardar en la base de datos: " . $e->getMessage()
                ], JSON_UNESCAPED_UNICODE);
                exit;
            } else {
                $mensaje_error = "Error al guardar en la base de datos: " . $e->getMessage();
            }
        }
    } else {
        if ($es_json) {
            header("Content-Type: application/json; charset=utf-8");
            http_response_code(400);
            echo json_encode(["ok" => false, "mensaje" => $mensaje_error], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardar Prenda - The Clothes Club</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 20px;
            font-family: "Trebuchet MS", Arial, sans-serif;
            background-color: #21030B;
            color: #fff;
            background-image: url('fondo2.webp');
            background-repeat: repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: rgba(49, 0, 11, 0.95);
            border: 2px solid #CB1857;
            border-radius: 12px;
            padding: 30px;
            width: 100%;
            max-width: 580px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.7);
        }
        h1 {
            color: #FFB3CE;
            font-size: 26px;
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 1px dashed #CB1857;
            padding-bottom: 15px;
            letter-spacing: 1px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #FFDEE9;
            font-size: 14px;
        }
        input[type="text"], textarea, select {
            width: 100%;
            padding: 10px 12px;
            margin-top: 6px;
            border: 1px solid #9C5480;
            border-radius: 6px;
            background: #180108;
            color: #fff;
            font-family: inherit;
            font-size: 14px;
            outline: none;
        }
        input[type="text"]:focus, textarea:focus, select:focus {
            border-color: #FF6FA3;
            box-shadow: 0 0 8px rgba(255, 111, 163, 0.4);
        }
        textarea { resize: vertical; min-height: 70px; }
        input[type="file"] {
            margin-top: 8px;
            width: 100%;
            padding: 8px;
            background: #180108;
            border: 1px dashed #CB1857;
            border-radius: 6px;
            color: #ddd;
        }
        .btn-submit {
            display: block;
            width: 100%;
            margin-top: 25px;
            padding: 12px;
            background: #CB1857;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }
        .btn-submit:hover {
            background: #E8296E;
            transform: translateY(-1px);
        }
        .btn-back {
            display: inline-block;
            margin-top: 15px;
            color: #FFB3CE;
            text-decoration: none;
            font-size: 13px;
            text-align: center;
            width: 100%;
        }
        .btn-back:hover { text-decoration: underline; }
        .alerta-error {
            background: #5A0A1F;
            border: 1px solid #FF4B72;
            color: #FFD4DE;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>✿ NUEVA PRENDA ✿</h1>

        <?php if (!empty($mensaje_error)): ?>
            <div class="alerta-error">⚠️ <?php echo htmlspecialchars($mensaje_error); ?></div>
        <?php endif; ?>

        <form action="guardar_prenda.php" method="POST" enctype="multipart/form-data">
            <label for="nombre">Nombre de la prenda: *</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ej: Remera Vintage Oversize" required>

            <label for="tipo">Tipo de prenda: *</label>
            <select id="tipo" name="tipo" required>
                <option value="">-- Seleccioná un tipo --</option>
                <option value="Remera">Remera</option>
                <option value="Pantalón">Pantalón</option>
                <option value="Falda">Falda</option>
                <option value="Vestido">Vestido</option>
                <option value="Buzo">Buzo</option>
                <option value="Campera">Campera</option>
                <option value="Abrigo">Abrigo</option>
                <option value="Calzado">Calzado</option>
                <option value="Accesorio">Accesorio</option>
                <option value="Otro">Otro</option>
            </select>

            <label for="color">Color:</label>
            <input type="text" id="color" name="color" placeholder="Ej: rosa, negro, azul...">

            <label for="estilo">Estilo:</label>
            <select id="estilo" name="estilo">
                <option value="Casual">Casual</option>
                <option value="Formal">Formal</option>
                <option value="Deportivo">Deportivo</option>
                <option value="Elegante">Elegante</option>
                <option value="Y2K">Y2K</option>
                <option value="Vintage">Vintage</option>
                <option value="Alternativo">Alternativo</option>
                <option value="Otro">Otro</option>
            </select>

            <label for="temporada">Temporada:</label>
            <select id="temporada" name="temporada">
                <option value="Todas">Todas las temporadas</option>
                <option value="Primavera">Primavera</option>
                <option value="Verano">Verano</option>
                <option value="Otoño">Otoño</option>
                <option value="Invierno">Invierno</option>
            </select>

            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" placeholder="Detalles, talle, textura, cómo combinarla..."></textarea>

            <label for="etiquetas">Etiquetas (separadas por coma):</label>
            <input type="text" id="etiquetas" name="etiquetas" placeholder="oversize, algodón, rayas, fiesta">

            <label for="foto">Foto de la prenda:</label>
            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp,image/gif">

            <button type="submit" class="btn-submit">Guardar Prenda</button>
            <a href="mi_armario.php" class="btn-back">← Volver a mi armario</a>
        </form>
    </div>
</body>
</html>
