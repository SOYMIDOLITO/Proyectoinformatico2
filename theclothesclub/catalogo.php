<?php
require_once __DIR__ . '/conexion.php';
session_start();
if (!isset($_SESSION['id_usuario'])) { $_SESSION['id_usuario'] = 1; }
$id_usuario = (int)$_SESSION['id_usuario'];

// ARMARIO: prendas del usuario (panel izquierdo)
$sql_arm = "SELECT p.id_prenda, p.nombre_prenda, p.tipo, p.color,
            GROUP_CONCAT(e.nombre_etiqueta ORDER BY e.nombre_etiqueta SEPARATOR ',') AS etiquetas
            FROM prendas p
            LEFT JOIN prenda_etiqueta pe ON p.id_prenda = pe.id_prenda
            LEFT JOIN etiquetas e       ON pe.id_etiqueta = e.id_etiqueta
            WHERE p.id_usuario = $id_usuario
            GROUP BY p.id_prenda
            ORDER BY p.tipo, p.nombre_prenda";
$res_arm = mysqli_query($conexion, $sql_arm);
$prendas_armario = $res_arm ? mysqli_fetch_all($res_arm, MYSQLI_ASSOC) : [];

// CATALOGO: prendas externas (panel derecho)
$sql_cat = "SELECT p.id_prenda, p.nombre_prenda, p.tipo, p.color, p.estilo, p.temporada, p.descripcion,
            GROUP_CONCAT(e.nombre_etiqueta ORDER BY e.nombre_etiqueta SEPARATOR ',') AS etiquetas
            FROM prendas p
            LEFT JOIN prenda_etiqueta pe ON p.id_prenda = pe.id_prenda
            LEFT JOIN etiquetas e       ON pe.id_etiqueta = e.id_etiqueta
            WHERE p.id_usuario = 2
            GROUP BY p.id_prenda
            ORDER BY p.tipo, p.nombre_prenda";
$res_cat = mysqli_query($conexion, $sql_cat);
$prendas_catalogo = $res_cat ? mysqli_fetch_all($res_cat, MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo y Coincidencias - The Clothes Club</title>
    <style>
        * { box-sizing: border-box; }
        html, body { min-height: 100%; margin: 0; padding: 0; width: 100%; font-family: "Trebuchet MS", Arial, sans-serif; color: #fff; }
        .imagen-fondo-fijada { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; object-fit: cover; z-index: -1; pointer-events: none; }
        .page { width: 1100px; max-width: 96%; margin: 20px auto; border: 3px solid #8F4D75; background-color: rgba(135,82,123,0.35); }

        header { min-height: 160px; position: relative; text-align: center; padding: 25px 20px;
                 background-color: #BD5579; border-bottom: 2px solid #9C5480;
                 display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden; }
        header:before { content: "✦"; position: absolute; left: 25px; top: 35px; color: #fff; font-size: 45px; }
        header:after  { content: "✦"; position: absolute; right: 30px; top: 80px; color: #fff; font-size: 45px; }
        .logo { font-family: script,cursive; font-size: 50px; font-weight: bold; letter-spacing: 3px; color: #fff;
                animation: brillo 2s infinite alternate ease-in-out; }
        @keyframes brillo {
            0%,100% { text-shadow: 0 0 5px #fff, 0 0 10px #EDABBE, 0 0 20px #DE5C8F; }
            50%      { text-shadow: 0 0 15px #fff, 0 0 25px #EDABBE, 0 0 40px #DE5C8F, 0 0 50px #fff; }
        }
        .subtitle { font-size: 18px; font-style: italic; color: #EDABBE; margin-top: 5px; }

        nav { background-color: #AF719D; border-bottom: 2px solid #EDABBE; padding: 10px 15px;
              display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }
        nav a { padding: 8px 14px; text-decoration: none; color: #fff; font-weight: bold; font-size: 14px;
                border: 1px solid #fff; background-color: #87527B; transition: background 0.2s, transform 0.2s; }
        nav a:hover  { background-color: #DE5C8F; transform: translateY(-2px); }
        nav a.activo { background-color: #CB1857; border: 2px solid #EDABBE; }

        .contenedor { display: grid; grid-template-columns: 270px 1fr; gap: 16px; padding: 18px; }

        .panel { background-color: #D46A8F; border: 2px solid #EDABBE; padding: 18px; box-shadow: 0 4px 12px rgba(49,0,11,.35); }
        .panel h2 { margin-top: 0; color: #fff; font-size: 20px; border-bottom: 2px solid #EDABBE; padding-bottom: 8px; }
        .panel p  { color: #fff; font-size: 13px; margin: 0 0 10px; }

        #listaArmario { list-style: none; padding: 0; margin: 0; max-height: 480px; overflow-y: auto; }
        #listaArmario li { padding: 9px 12px; cursor: pointer; border-bottom: 1px solid rgba(237,171,190,.3);
                           font-size: 13px; transition: background .15s; }
        #listaArmario li:hover    { background: rgba(255,255,255,.15); }
        #listaArmario li.activo   { background: #CB1857; font-weight: bold; }

        .tags-sel { margin-top: 10px; display: flex; flex-wrap: wrap; gap: 4px; min-height: 22px; }
        .tag-s { background: #CB1857; color: #fff; font-size: 11px; padding: 2px 8px; border-radius: 10px; }

        .subtitulo { font-size: 13px; color: #EDABBE; margin-bottom: 12px; font-style: italic; min-height: 18px; }

        .grid-cat { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px,1fr)); gap: 12px; }
        .tarjeta { background-color: #8D537C; border: 2px solid #EDABBE; padding: 12px; text-align: center;
                   transition: opacity .3s, border-color .3s, box-shadow .3s; }
        .tarjeta h3  { font-size: 13px; margin: 7px 0 3px; color: #fff; min-height: 34px; }
        .tarjeta .subtipo { font-size: 11px; color: #EDABBE; margin-bottom: 5px; }
        .tarjeta .tags  { display: flex; flex-wrap: wrap; gap: 3px; justify-content: center; margin-top: 5px; }
        .tarjeta .tag   { background: #87527B; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 8px; }
        .tarjeta .coinc { font-size: 11px; color: #CDEBFC; margin-top: 5px; min-height: 15px; font-style: italic; }
        .tarjeta.coincide    { border-color: #CDEBFC; box-shadow: 0 0 10px rgba(205,235,252,.6); }
        .tarjeta.no-coincide { opacity: .28; }
        .icono { font-size: 34px; margin: 5px 0; }

        #aviso { display: none; color: #EDABBE; font-style: italic; text-align: center; padding: 20px; }

        footer { background-color: #AF719D; padding: 18px; text-align: center; border-top: 2px solid #EDABBE; }
        footer a { color: #fff; text-decoration: none; margin: 0 5px; font-weight: bold; }
        footer a:hover { color: #CDEBFC; }
        .copyright { color: #EDABBE; font-size: 12px; margin-top: 5px; }

        @media(max-width:768px) { .contenedor { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<img src="fondo.jpg" class="imagen-fondo-fijada" alt="Fondo">
<div class="page">

    <header>
        <div class="logo">THE CLOTHES CLUB</div>
        <div class="subtitle">tu armario, tu estilo, tu mundo</div>
    </header>

    <nav>
        <a href="index.html">✦ Inicio</a>
        <a href="mi_armario.php">♥ Mi Armario</a>
        <a href="catalogo.php" class="activo">1. Catálogo y Coincidencias</a>
        <a href="outfits.html">2. Armador de Outfits</a>
    </nav>

    <div class="contenedor">

        <!-- PANEL IZQ: mi armario -->
        <div class="panel">
            <h2>✿ Mi Armario</h2>
            <p>Elegí una prenda tuya para ver qué combina en el catálogo:</p>

            <ul id="listaArmario">
                <li data-tags="" onclick="filtrar(this)" class="activo">— Mostrar todo —</li>
                <?php foreach ($prendas_armario as $p): ?>
                <li data-tags="<?= htmlspecialchars($p['etiquetas'] ?? '') ?>"
                    data-nombre="<?= htmlspecialchars($p['nombre_prenda']) ?>"
                    onclick="filtrar(this)">
                    <?= htmlspecialchars($p['nombre_prenda']) ?>
                    <small style="display:block;color:#EDABBE;font-size:10px;">
                        <?= htmlspecialchars($p['tipo']) ?> · <?= htmlspecialchars($p['color']) ?>
                    </small>
                </li>
                <?php endforeach; ?>
                <?php if (empty($prendas_armario)): ?>
                <li style="color:#EDABBE;cursor:default;">No hay prendas en el armario.</li>
                <?php endif; ?>
            </ul>

            <div class="tags-sel" id="tagsSel"></div>
        </div>

        <!-- PANEL DER: catálogo -->
        <div class="panel">
            <h2>✿ Catálogo — prendas que combinan</h2>
            <div class="subtitulo" id="subtitulo">Seleccioná una prenda del armario para filtrar.</div>
            <div id="aviso">No hay prendas en el catálogo que coincidan con esta selección.</div>

            <div class="grid-cat" id="gridCat">
                <?php
                $iconos = ['abrigo'=>'🧥','remera'=>'👕','pantalon'=>'👖','falda'=>'👗',
                           'vestido'=>'👗','calzado'=>'👟','otro'=>'🛍️'];
                foreach ($prendas_catalogo as $p):
                    $tags = array_values(array_filter(array_map('trim', explode(',', $p['etiquetas'] ?? ''))));
                    $icono = $iconos[$p['tipo']] ?? '🛍️';
                ?>
                <div class="tarjeta" data-tags="<?= htmlspecialchars($p['etiquetas'] ?? '') ?>">
                    <div class="icono"><?= $icono ?></div>
                    <h3><?= htmlspecialchars($p['nombre_prenda']) ?></h3>
                    <div class="subtipo"><?= htmlspecialchars($p['tipo']) ?> · <?= htmlspecialchars($p['color']) ?></div>
                    <div class="tags">
                        <?php foreach ($tags as $t): ?>
                        <span class="tag">#<?= htmlspecialchars($t) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="coinc"></div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($prendas_catalogo)): ?>
                <p style="color:#EDABBE;">No hay prendas en el catálogo.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <footer>
        <div>
            <a href="index.html">inicio</a> |
            <a href="mi_armario.php">mi armario</a> |
            <a href="outfits.html">outfits</a> |
            <a href="catalogo.php">catalogo</a>
        </div>
        <div class="copyright">© 2000-2005 THE CLOTHES CLUB - todos los derechos reservados</div>
    </footer>
</div>

<script>
function filtrar(li) {
    // Marcar activo
    document.querySelectorAll('#listaArmario li').forEach(el => el.classList.remove('activo'));
    li.classList.add('activo');

    const etStr  = li.dataset.tags  || '';
    const nombre = li.dataset.nombre || '';
    const etArmario = etStr ? etStr.split(',').map(t => t.trim().toLowerCase()).filter(Boolean) : [];

    // Tags seleccionadas
    document.getElementById('tagsSel').innerHTML =
        etArmario.map(t => `<span class="tag-s">#${t}</span>`).join('');

    // Subtítulo
    document.getElementById('subtitulo').textContent = etArmario.length
        ? `Mostrando prendas que combinan con "${nombre}"`
        : 'Seleccioná una prenda del armario para filtrar.';

    const tarjetas = document.querySelectorAll('.tarjeta');
    let hayCoinc = false;

    tarjetas.forEach(card => {
        const tagsCard = card.dataset.tags
            ? card.dataset.tags.split(',').map(t => t.trim().toLowerCase()).filter(Boolean)
            : [];

        if (!etArmario.length) {
            card.classList.remove('coincide', 'no-coincide');
            card.querySelector('.coinc').textContent = '';
            card.style.order = '';
        } else {
            const comunes = etArmario.filter(t => tagsCard.includes(t));
            if (comunes.length) {
                card.classList.add('coincide');
                card.classList.remove('no-coincide');
                card.querySelector('.coinc').textContent = '✓ ' + comunes.join(', ');
                card.style.order = -comunes.length;
                hayCoinc = true;
            } else {
                card.classList.add('no-coincide');
                card.classList.remove('coincide');
                card.querySelector('.coinc').textContent = '';
                card.style.order = 1;
            }
        }
    });

    document.getElementById('aviso').style.display =
        (etArmario.length && !hayCoinc) ? 'block' : 'none';
}
</script>
</body>
</html>
