<?php
/**
 * IMPULSO ACADÉMICO - Cabecera Compartida Multipágina 100% Responsiva + Fondo en Movimiento
 */
require_once dirname(__DIR__) . '/config/db.php';

$pdo = getPDO();
$cfg = obtenerConfiguracion($pdo);

// 1. Obtener todas las imágenes activas desde la base de datos
$stmtImgs = $pdo->query("SELECT * FROM galeria_imagenes WHERE estado = 'ACTIVO' ORDER BY orden ASC, id ASC");
$todasImagenes = $stmtImgs->fetchAll();

$seccionesImg = [];
$galeriaExito = [];
foreach ($todasImagenes as $item) {
    if ($item['clave_seccion'] === 'galeria_exito') {
        $galeriaExito[] = $item;
    } else {
        $seccionesImg[$item['clave_seccion']] = $item;
    }
}

$imgLogo = $seccionesImg['logo']['imagen_url'] ?? ($cfg['logo_url'] ?? 'uploads/img/logo.jpg');

// Imágenes de alta resolución desde la BD para el fondo animado en movimiento continuo
$bgSlide1 = $seccionesImg['quienes_somos']['imagen_url'] ?? 'uploads/img/pdf_img_3.jpg';
$bgSlide2 = $seccionesImg['mision_vision']['imagen_url'] ?? 'uploads/img/pdf_img_5.jpg';
$bgSlide3 = $seccionesImg['hero_principal']['imagen_url'] ?? 'uploads/img/pdf_img_2.jpg';

// 2. Obtener campañas de descuento activas (Si no hay descuentos activos en este momento, devuelve vacío)
$campanasActivas = $pdo->query("SELECT * FROM campanas_descuento WHERE estado = 'ACTIVA' ORDER BY id DESC")->fetchAll();
$campanaBanner = null;
foreach ($campanasActivas as $c) {
    if ((int)$c['mostrar_banner_superior'] === 1) {
        $campanaBanner = $c;
        break;
    }
}

$wsp1 = preg_replace('/\D/', '', $cfg['whatsapp_numero'] ?? '51953697434');
$wsp2 = preg_replace('/\D/', '', $cfg['whatsapp_secundario'] ?? '51900439160');
$whatsappLink1 = "https://wa.me/{$wsp1}?text=" . rawurlencode("Hola Impulso Académico, deseo información sobre asesoría de tesis y coordinar un presupuesto personalizado.");
$whatsappLink2 = "https://wa.me/{$wsp2}?text=" . rawurlencode("Hola Impulso Académico, deseo información sobre asesoría de tesis y coordinar un presupuesto personalizado.");

$paginaActual = $paginaActual ?? basename($_SERVER['PHP_SELF']);
$tituloPagina = $tituloPagina ?? ($cfg['titulo_portada'] ?? 'Asesoría de Tesis Académica');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?> | <?= htmlspecialchars($cfg['nombre_empresa'] ?? 'Impulso Académico') ?></title>
    <link rel="icon" type="image/jpeg" href="<?= htmlspecialchars($imgLogo) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body>

    <!-- ==================== FONDO DINÁMICO CON IMÁGENES EN MOVIMIENTO VISIBLE (DESDE BD) ==================== -->
    <div class="moving-bg-container" aria-hidden="true">
        <div class="moving-bg-slide slide-1" style="background-image: url('<?= htmlspecialchars($bgSlide1) ?>');"></div>
        <div class="moving-bg-slide slide-2" style="background-image: url('<?= htmlspecialchars($bgSlide2) ?>');"></div>
        <div class="moving-bg-slide slide-3" style="background-image: url('<?= htmlspecialchars($bgSlide3) ?>');"></div>
        <div class="moving-bg-overlay"></div>
        <div class="moving-bg-orb orb-1"></div>
        <div class="moving-bg-orb orb-2"></div>
    </div>

    <!-- ==================== BARRA SUPERIOR DE CAMPAÑA (SOLO SI EL ADMIN ACTIVA UN DESCUENTO) ==================== -->
    <?php if ($campanaBanner): ?>
    <div class="campaign-topbar">
        <div class="campaign-topbar-inner">
            <span class="campaign-pill">
                <i class="fa-solid fa-bolt"></i> <?= (int)$campanaBanner['porcentaje_descuento'] ?>% DESCUENTO ACTIVO
            </span>
            <span>
                <strong><?= htmlspecialchars($campanaBanner['titulo']) ?>:</strong>
                <?= htmlspecialchars($campanaBanner['subtitulo']) ?>
            </span>
            <?php if (!empty($campanaBanner['codigo_promo'])): ?>
            <span>
                Código: <strong class="campaign-code-badge"><?= htmlspecialchars($campanaBanner['codigo_promo']) ?></strong>
            </span>
            <?php endif; ?>
            <a href="contacto.php?promo=<?= urlencode($campanaBanner['codigo_promo'] ?? '') ?>" class="campaign-topbar-btn">
                Reclamar Descuento <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- ==================== NAVBAR 100% RESPONSIVO ==================== -->
    <header class="navbar" id="mainNavbar">
        <div class="navbar-container">
            <a href="index.php" class="brand-logo">
                <div class="brand-logo-img-wrap">
                    <img src="<?= htmlspecialchars($imgLogo) ?>" alt="Logo Impulso Académico">
                </div>
                <div class="brand-text">
                    <span class="brand-title">Impulso <span>Académico</span></span>
                    <span class="brand-subtitle">Gestión y Apoyo a la Investigación</span>
                </div>
            </a>

            <ul class="nav-links" id="navLinks">
                <li><a href="index.php" class="<?= $paginaActual === 'index.php' ? 'active' : '' ?>">Inicio</a></li>
                <li><a href="nosotros.php" class="<?= $paginaActual === 'nosotros.php' ? 'active' : '' ?>">¿Quiénes Somos?</a></li>
                <li><a href="servicios.php" class="<?= $paginaActual === 'servicios.php' ? 'active' : '' ?>">Servicios</a></li>
                <li><a href="metodologia.php" class="<?= $paginaActual === 'metodologia.php' ? 'active' : '' ?>">Metodología</a></li>
                <li><a href="tarifas.php" class="<?= $paginaActual === 'tarifas.php' ? 'active' : '' ?>">Tarifas</a></li>
                <li><a href="contacto.php" class="<?= $paginaActual === 'contacto.php' ? 'active' : '' ?>">Contáctanos</a></li>
                <!-- Botones visibles dentro del menú desplegable en móviles/tablets -->
                <li class="nav-mobile-actions">
                    <a href="admin/index.php" class="btn btn-nav-panel">
                        <i class="fa-solid fa-chart-pie"></i> Panel de Control
                    </a>
                    <a href="contacto.php" class="btn btn-nav-cta">
                        <i class="fa-solid fa-paper-plane"></i> Solicitar Asesoría
                    </a>
                </li>
            </ul>

            <div class="nav-actions">
                <a href="admin/index.php" class="btn btn-nav-panel desktop-only-btn" title="Panel de Control y Estados Financieros">
                    <i class="fa-solid fa-chart-pie"></i> <span>Panel Control</span>
                </a>
                <a href="contacto.php" class="btn btn-nav-cta desktop-only-btn">
                    <i class="fa-solid fa-paper-plane"></i> <span>Solicitar Asesoría</span>
                </a>
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Abrir Menú">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </header>
