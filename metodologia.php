<?php
/**
 * IMPULSO ACADÉMICO - Página Independiente: Nuestra Metodología de Trabajo y Modalidad de Atención
 * Imágenes jaladas directamente de la Base de Datos MySQL.
 */
$paginaActual = 'metodologia.php';
$tituloPagina = 'Nuestra Metodología de Trabajo y Modalidad de Atención';
require_once __DIR__ . '/includes/header.php';

$imgMetodologia = $seccionesImg['metodologia_banner'] ?? [
    'imagen_url' => 'uploads/img/pdf_img_9.jpg',
    'titulo' => 'Nuestra Metodología de Trabajo',
    'subtitulo' => 'Un proceso claro y eficiente para tu éxito académico'
];
$imgModalidad = $seccionesImg['modalidad_banner'] ?? [
    'imagen_url' => 'uploads/img/pdf_img_10.jpg',
    'titulo' => 'Modalidad de Atención',
    'subtitulo' => 'Flexibilidad y comodidad, pensadas para ti'
];
?>

    <!-- ==================== CABECERA DE PÁGINA ==================== -->
    <section class="page-hero-banner">
        <span class="section-tag"><i class="fa-solid fa-route"></i> Proceso Claro y Eficiente</span>
        <h1>Nuestra Metodología de Trabajo</h1>
        <p>Un proceso estructurado paso a paso y una modalidad de atención flexible pensada para tu éxito académico.</p>
    </section>

    <!-- ==================== PÁGINA 6 DEL PDF: METODOLOGÍA EN 5 PASOS ==================== -->
    <section class="section">
        <div class="container">
            <div class="hero-container">
                <div class="reveal visible">
                    <span class="section-tag"><i class="fa-solid fa-list-ol"></i> 5 Pasos Hacia tu Sustentación</span>
                    <h2 class="section-title">¿Cómo Trabajamos tu Tesis?</h2>
                    <p style="color:#B8D4F8;margin-bottom:26px;">
                        Te acompañamos en cada fase con entregas verificables y sesiones personalizadas:
                    </p>

                    <div style="display:flex;flex-direction:column;gap:14px;">
                        <div class="step-card" style="display:flex;gap:18px;align-items:center;padding:18px 22px;">
                            <div class="step-number" style="margin:0;flex-shrink:0;">1</div>
                            <div>
                                <h4>1. Diagnóstico Inicial</h4>
                                <p>Evaluamos tu avance actual y necesidades específicas.</p>
                            </div>
                        </div>

                        <div class="step-card" style="display:flex;gap:18px;align-items:center;padding:18px 22px;">
                            <div class="step-number" style="margin:0;flex-shrink:0;">2</div>
                            <div>
                                <h4>2. Plan Personalizado</h4>
                                <p>Creamos una hoja de ruta a medida para tu investigación.</p>
                            </div>
                        </div>

                        <div class="step-card" style="display:flex;gap:18px;align-items:center;padding:18px 22px;">
                            <div class="step-number" style="margin:0;flex-shrink:0;">3</div>
                            <div>
                                <h4>3. Asesorías Constantes</h4>
                                <p>Sesiones virtuales constantes para resolver tus dudas y explicar cada avance.</p>
                            </div>
                        </div>

                        <div class="step-card" style="display:flex;gap:18px;align-items:center;padding:18px 22px;">
                            <div class="step-number" style="margin:0;flex-shrink:0;">4</div>
                            <div>
                                <h4>4. Entregables Revisados</h4>
                                <p>Garantizamos la calidad con revisiones y correcciones expertas.</p>
                            </div>
                        </div>

                        <div class="step-card" style="display:flex;gap:18px;align-items:center;padding:18px 22px;">
                            <div class="step-number" style="margin:0;flex-shrink:0;">5</div>
                            <div>
                                <h4>5. Seguimiento Completo</h4>
                                <p>Te acompañamos hasta la exitosa sustentación de tu tesis.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="reveal">
                    <div class="hero-main-image-card">
                        <img src="<?= htmlspecialchars($imgMetodologia['imagen_url']) ?>" alt="<?= htmlspecialchars($imgMetodologia['titulo']) ?>" style="max-height:520px;object-fit:cover;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== PÁGINA 7 DEL PDF: MODALIDAD DE ATENCIÓN ==================== -->
    <section class="section section-alt">
        <div class="container">
            <div class="reveal" style="margin-bottom:44px;border-radius:24px;overflow:hidden;border:2px solid var(--ia-border-bright);max-height:250px;box-shadow:var(--shadow-lg);">
                <img src="<?= htmlspecialchars($imgModalidad['imagen_url']) ?>" alt="<?= htmlspecialchars($imgModalidad['titulo']) ?>" style="width:100%;height:250px;object-fit:cover;display:block;">
            </div>

            <div class="section-header reveal">
                <span class="section-tag"><i class="fa-solid fa-laptop-file"></i> Cobertura Nacional e Internacional</span>
                <h2 class="section-title">Modalidad de Atención</h2>
                <p class="section-subtitle">
                    Flexibilidad y comodidad, pensadas para ti.
                </p>
            </div>

            <div class="steps-grid" style="grid-template-columns:repeat(auto-fit,minmax(270px,1fr));">
                <div class="step-card reveal">
                    <div class="step-number"><i class="fa-solid fa-video"></i></div>
                    <h4>Virtual Completa</h4>
                    <p>A través de plataformas como Zoom, Google Meet y WhatsApp para tu máxima comodidad.</p>
                </div>

                <div class="step-card reveal">
                    <div class="step-number"><i class="fa-solid fa-earth-americas"></i></div>
                    <h4>Atención Remota</h4>
                    <p>Servicio disponible para estudiantes a nivel nacional e internacional, sin importar tu ubicación.</p>
                </div>

                <div class="step-card reveal">
                    <div class="step-number"><i class="fa-regular fa-clock"></i></div>
                    <h4>Horarios Flexibles</h4>
                    <p>Nos adaptamos a tu disponibilidad para que la asesoría se ajuste perfectamente a tu rutina.</p>
                </div>

                <div class="step-card reveal">
                    <div class="step-number"><i class="fa-solid fa-user-check"></i></div>
                    <h4>Atención Personalizada</h4>
                    <p>Cada estudiante recibe un trato individualizado y enfocado en sus requerimientos únicos.</p>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
