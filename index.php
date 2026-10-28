<?php
/**
 * IMPULSO ACADÉMICO - Página Principal (Inicio)
 * Diseño Multipágina Oscuro con Imagen de Fondo en Movimiento.
 * Los videos, imágenes y campañas (cuando se activan desde el panel) se jalan de MySQL.
 */
$paginaActual = 'index.php';
$tituloPagina = 'Inicio • Asesoría de Tesis Académica';
require_once __DIR__ . '/includes/header.php';

$imgHero = $seccionesImg['hero_principal'] ?? [
    'imagen_url' => 'uploads/img/pdf_img_2.jpg',
    'titulo' => 'Asesoría de Tesis Académica',
    'subtitulo' => 'Acompañamiento profesional para pregrado y posgrado'
];

// Obtener videos promocionales activos desde la BD
$videosActivos = $pdo->query("SELECT * FROM videos_promocionales WHERE estado = 'ACTIVO' ORDER BY destacado DESC, orden ASC, id DESC")->fetchAll();
$videoPrincipal = $videosActivos[0] ?? null;
?>

    <!-- ==================== HERO PRINCIPAL ==================== -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-content reveal visible">
                <div class="hero-badge">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span>PRESENTADO POR: IMPULSO ACADÉMICO • <?= htmlspecialchars($cfg['ciudad_anio'] ?? 'Lima – 2026') ?></span>
                </div>
                <h1 class="hero-title">
                    ASESORÍA DE <span class="highlight">TESIS ACADÉMICA</span>
                </h1>
                <p class="hero-description" style="font-size:1.25rem;font-weight:700;color:#fff;margin-bottom:14px;">
                    <?= htmlspecialchars($cfg['subtitulo_portada'] ?? 'Acompañamiento profesional para pregrado y posgrado') ?>
                </p>
                <p class="hero-description">
                    Guiamos tu investigación paso a paso desde la conceptualización hasta la sustentación final con ética académica, confidencialidad y especialistas de tu carrera.
                </p>

                <div class="internal-payment-notice">
                    <i class="fa-solid fa-handshake-angle"></i>
                    <div>
                        <strong>Coordinación Directa por Interno (Sin Pasarela de Pagos):</strong>
                        <?= htmlspecialchars($cfg['mensaje_pagos_internos'] ?? 'Todos los presupuestos y facilidades de pago se coordinan directamente por interno con nuestro equipo.') ?>
                    </div>
                </div>

                <div class="hero-cta-group">
                    <a href="contacto.php" class="btn btn-primary" style="padding:14px 28px;font-size:1rem;">
                        <i class="fa-solid fa-file-signature"></i> Solicitar Presupuesto Personalizado
                    </a>
                    <a href="servicios.php" class="btn btn-outline-light" style="padding:14px 24px;font-size:1rem;">
                        <i class="fa-solid fa-Compass"></i> Explorar Servicios
                    </a>
                    <a href="<?= htmlspecialchars($whatsappLink1) ?>" target="_blank" class="btn btn-whatsapp" style="padding:14px 24px;font-size:1rem;">
                        <i class="fa-brands fa-whatsapp"></i> 953 697 434
                    </a>
                </div>

                <div class="hero-stats">
                    <div class="stat-box">
                        <h3 data-counter="8" data-suffix=" Etapas">8 Etapas</h3>
                        <p>Cobertura Integral de Tesis</p>
                    </div>
                    <div class="stat-box">
                        <h3 data-counter="8" data-suffix=" Áreas">8 Áreas</h3>
                        <p>Disciplinas Especializadas</p>
                    </div>
                    <div class="stat-box">
                        <h3 data-counter="100" data-suffix="%">100%</h3>
                        <p>Ética y Confidencialidad</p>
                    </div>
                </div>
            </div>

            <!-- Imagen Principal del Hero + Logo Definitivo jalados desde la Base de Datos -->
            <div class="hero-visual reveal">
                <div class="hero-main-image-card">
                    <div class="floating-logo-medal" title="Logo Oficial Impulso Académico">
                        <img src="<?= htmlspecialchars($imgLogo) ?>" alt="Emblema Impulso Académico">
                    </div>
                    <img src="<?= htmlspecialchars($imgHero['imagen_url']) ?>" alt="<?= htmlspecialchars($imgHero['titulo']) ?>" style="max-height:470px;object-fit:cover;object-position:top;">
                    <div class="floating-badge-bottom">
                        <i class="fa-solid fa-circle-check" style="color:#3898FF;font-size:1.4rem;"></i>
                        <div>
                            <div style="line-height:1.1;color:#fff;"><?= htmlspecialchars($cfg['lema_oficial'] ?? '¡Tu éxito es nuestro impulso!') ?></div>
                            <small style="color:#85C1FF;font-weight:600;">Pregrado y Posgrado • Lima 2026</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== SECCIÓN DE CAMPAÑAS DE DESCUENTO (SOLO APARECE SI EL ADMIN ACTIVA UNA EN EL PANEL) ==================== -->
    <?php if (count($campanasActivas) > 0): ?>
    <section class="section section-alt" id="campanas">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-tag"><i class="fa-solid fa-fire"></i> Campaña Especial Activa</span>
                <h2 class="section-title">Promociones y Descuentos Vigentes</h2>
                <p class="section-subtitle">
                    Aprovecha nuestras campañas activas y coordina tus facilidades de pago directamente por interno.
                </p>
            </div>

            <div class="campaigns-grid">
                <?php foreach ($campanasActivas as $camp): ?>
                <article class="campaign-card reveal">
                    <div class="campaign-ribbon">
                        -<?= (int)$camp['porcentaje_descuento'] ?>% OFF
                    </div>
                    <div class="campaign-img-wrap">
                        <img src="<?= htmlspecialchars($camp['imagen_url']) ?>" alt="<?= htmlspecialchars($camp['titulo']) ?>">
                    </div>
                    <div class="campaign-body">
                        <span class="campaign-label">
                            <?= htmlspecialchars($camp['etiqueta_oferta'] ?: 'CAMPAÑA ESPECIAL') ?>
                        </span>
                        <h3><?= htmlspecialchars($camp['titulo']) ?></h3>
                        <p><strong><?= htmlspecialchars($camp['subtitulo']) ?></strong> — <?= htmlspecialchars($camp['descripcion']) ?></p>

                        <div class="campaign-meta">
                            <div>
                                <small style="display:block;color:#85C1FF;font-size:0.75rem;">CÓDIGO PROMOCIONAL</small>
                                <strong style="color:#fff;font-size:1.05rem;font-family:monospace;">
                                    <?= htmlspecialchars($camp['codigo_promo'] ?: 'IMPULSO2026') ?>
                                </strong>
                            </div>
                            <?php if (!empty($camp['fecha_fin'])): ?>
                            <div style="text-align:right;">
                                <small style="display:block;color:#85C1FF;font-size:0.75rem;">VIGENCIA</small>
                                <strong style="color:#3898FF;font-size:0.9rem;">
                                    <i class="fa-regular fa-calendar-check"></i> Hasta <?= date('d/m/Y', strtotime($camp['fecha_fin'])) ?>
                                </strong>
                            </div>
                            <?php endif; ?>
                        </div>

                        <a href="contacto.php?promo=<?= urlencode($camp['codigo_promo'] ?? '') ?>" class="btn btn-primary" style="width:100%;">
                            <i class="fa-solid fa-ticket"></i> Reclamar <?= (int)$camp['porcentaje_descuento'] ?>% OFF por Interno
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ==================== ACCESOS DIRECTOS A LAS PÁGINAS INDEPENDIENTES ==================== -->
    <section class="section section-alt">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-tag"><i class="fa-solid fa-compass"></i> Explora Impulso Académico</span>
                <h2 class="section-title">Todo lo que Necesitas para tu Investigación</h2>
                <p class="section-subtitle">
                    Navega por nuestras secciones dedicadas para conocer a nuestro equipo, servicios, metodología y planes de inversión.
                </p>
            </div>

            <div class="steps-grid">
                <a href="nosotros.php" class="step-card reveal">
                    <div class="step-number"><i class="fa-solid fa-users"></i></div>
                    <h4>¿Quiénes Somos?</h4>
                    <p>Conoce nuestro equipo de expertos, nuestra Misión, Visión y los 4 Valores Fundamentales que guían cada asesoría.</p>
                    <span style="display:inline-block;margin-top:14px;color:#3898FF;font-weight:800;font-size:0.86rem;">
                        Ir a ¿Quiénes Somos? <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </a>

                <a href="servicios.php" class="step-card reveal">
                    <div class="step-number"><i class="fa-solid fa-book-open-reader"></i></div>
                    <h4>Servicios y Especialidades</h4>
                    <p>Revisa las 8 etapas de investigación que cubrimos y las 8 áreas académicas en las que contamos con especialistas.</p>
                    <span style="display:inline-block;margin-top:14px;color:#3898FF;font-weight:800;font-size:0.86rem;">
                        Ver Servicios y Áreas <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </a>

                <a href="metodologia.php" class="step-card reveal">
                    <div class="step-number"><i class="fa-solid fa-route"></i></div>
                    <h4>Metodología y Modalidad</h4>
                    <p>Descubre nuestro proceso de trabajo en 5 pasos y nuestra modalidad de atención virtual remota y personalizada.</p>
                    <span style="display:inline-block;margin-top:14px;color:#3898FF;font-weight:800;font-size:0.86rem;">
                        Ver Metodología <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </a>

                <a href="tarifas.php" class="step-card reveal">
                    <div class="step-number"><i class="fa-solid fa-coins"></i></div>
                    <h4>Tarifas de Servicios</h4>
                    <p>Consulta la inversión referencial por capítulo, tesis completa, análisis estadístico, diapositivas y Turnitin.</p>
                    <span style="display:inline-block;margin-top:14px;color:#3898FF;font-weight:800;font-size:0.86rem;">
                        Ver Tarifas Oficiales <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </a>
            </div>
        </div>
    </section>

    <!-- ==================== SECCIÓN DE VIDEOS PROMOCIONALES (DESDE BD) ==================== -->
    <?php if ($videoPrincipal): ?>
    <section class="section" id="videos">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-tag"><i class="fa-solid fa-circle-play"></i> Multimedia desde Base de Datos</span>
                <h2 class="section-title">Videos Promocionales e Informativos</h2>
                <p class="section-subtitle">
                    Visualiza nuestros videos promocionales gestionados desde el Panel de Control de Impulso Académico.
                </p>
            </div>

            <div class="video-showcase-layout reveal">
                <div class="main-video-player-card">
                    <div class="video-wrapper" id="mainVideoContainer">
                        <?php if ($videoPrincipal['tipo_fuente'] === 'EXTERNO' && (strpos($videoPrincipal['video_url'], 'youtube.com') !== false || strpos($videoPrincipal['video_url'], 'youtu.be') !== false || strpos($videoPrincipal['video_url'], 'vimeo.com') !== false)): ?>
                            <iframe src="<?= htmlspecialchars($videoPrincipal['video_url']) ?>" allowfullscreen></iframe>
                        <?php else: ?>
                            <video id="activePromoVideo" controls poster="<?= htmlspecialchars($videoPrincipal['poster_url'] ?: 'uploads/img/pdf_img_2.jpg') ?>">
                                <source src="<?= htmlspecialchars($videoPrincipal['video_url']) ?>" type="video/mp4">
                                Tu navegador no soporta la reproducción de video HTML5.
                            </video>
                        <?php endif; ?>
                    </div>
                    <div class="video-info-bar">
                        <span id="mainVideoCat" style="display:inline-block;background:#0074F0;color:#fff;font-size:0.75rem;font-weight:800;padding:3px 12px;border-radius:999px;margin-bottom:8px;">
                            <?= htmlspecialchars($videoPrincipal['categoria']) ?>
                        </span>
                        <h3 id="mainVideoTitle"><?= htmlspecialchars($videoPrincipal['titulo']) ?></h3>
                        <p id="mainVideoDesc"><?= htmlspecialchars($videoPrincipal['descripcion']) ?></p>
                    </div>
                </div>

                <div class="video-playlist">
                    <div style="font-weight:800;color:#85C1FF;font-size:0.9rem;margin-bottom:4px;">
                        <i class="fa-solid fa-film"></i> SELECCIONA UN VIDEO PROMOCIONAL (<?= count($videosActivos) ?>)
                    </div>
                    <?php foreach ($videosActivos as $idx => $vid): ?>
                    <div class="playlist-item <?= $idx === 0 ? 'active' : '' ?>"
                         data-tipo="<?= htmlspecialchars($vid['tipo_fuente']) ?>"
                         data-url="<?= htmlspecialchars($vid['video_url']) ?>"
                         data-poster="<?= htmlspecialchars($vid['poster_url'] ?: 'uploads/img/pdf_img_2.jpg') ?>"
                         data-titulo="<?= htmlspecialchars($vid['titulo']) ?>"
                         data-desc="<?= htmlspecialchars($vid['descripcion']) ?>"
                         data-cat="<?= htmlspecialchars($vid['categoria']) ?>">
                        <div class="playlist-thumb">
                            <img src="<?= htmlspecialchars($vid['poster_url'] ?: 'uploads/img/pdf_img_2.jpg') ?>" alt="<?= htmlspecialchars($vid['titulo']) ?>">
                            <div class="playlist-play-icon"><i class="fa-solid fa-play"></i></div>
                        </div>
                        <div class="playlist-meta">
                            <h4><?= htmlspecialchars($vid['titulo']) ?></h4>
                            <span><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($vid['duracion'] ?: 'Video Promo') ?> • <?= htmlspecialchars($vid['categoria']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ==================== GALERÍA DINÁMICA DESDE LA BASE DE DATOS ==================== -->
    <?php if (count($galeriaExito) > 0): ?>
    <section class="section section-alt">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-tag"><i class="fa-solid fa-award"></i> Galería desde Base de Datos</span>
                <h2 class="section-title">Acompañamiento Académico de Excelencia</h2>
                <p class="section-subtitle">
                    Imágenes institucionales cargadas dinámicamente desde la base de datos de Impulso Académico.
                </p>
            </div>

            <div class="services-grid">
                <?php foreach ($galeriaExito as $g): ?>
                <div class="service-card reveal">
                    <div class="service-img-box">
                        <span class="service-cat-badge"><?= htmlspecialchars($g['etiqueta'] ?: 'Impulso Académico') ?></span>
                        <img src="<?= htmlspecialchars($g['imagen_url']) ?>" alt="<?= htmlspecialchars($g['titulo']) ?>">
                    </div>
                    <div class="service-content">
                        <h3><?= htmlspecialchars($g['titulo']) ?></h3>
                        <p style="color:#3898FF;font-weight:700;margin-bottom:8px;"><?= htmlspecialchars($g['subtitulo']) ?></p>
                        <p><?= htmlspecialchars($g['descripcion']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
