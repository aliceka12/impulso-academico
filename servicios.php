<?php
/**
 * IMPULSO ACADÉMICO - Página Independiente: Servicios que Ofrecemos y Áreas de Especialización
 * Todas las imágenes y los 8 servicios oficiales se jalan de la Base de Datos MySQL.
 */
$paginaActual = 'servicios.php';
$tituloPagina = 'Servicios que Ofrecemos y Áreas de Especialización';
require_once __DIR__ . '/includes/header.php';

$serviciosActivos = $pdo->query("SELECT * FROM servicios WHERE estado = 'ACTIVO' ORDER BY orden ASC, id ASC")->fetchAll();
$imgServiciosBanner = $seccionesImg['servicios_banner'] ?? [
    'imagen_url' => 'uploads/img/pdf_img_6.jpg',
    'titulo' => 'Servicios que Ofrecemos',
    'subtitulo' => 'Te acompañamos en cada etapa de tu investigación'
];
$imgEspecialidades = $seccionesImg['especialidades_banner'] ?? [
    'imagen_url' => 'uploads/img/pdf_img_8.jpg',
    'titulo' => 'Áreas de Especialización',
    'subtitulo' => 'Contamos con expertos en diversas disciplinas para garantizar un acompañamiento de calidad'
];
?>

    <!-- ==================== CABECERA DE PÁGINA ==================== -->
    <section class="page-hero-banner">
        <span class="section-tag"><i class="fa-solid fa-book-open-reader"></i> Acompañamiento Integral</span>
        <h1>Servicios que Ofrecemos</h1>
        <p>Te acompañamos en cada etapa de tu investigación con especialistas metodológicos, estadísticos y temáticos.</p>
    </section>

    <!-- ==================== LOS 8 SERVICIOS OFICIALES (PÁGINA 4 DEL PDF + IMÁGENES BD) ==================== -->
    <section class="section">
        <div class="container">
            <div class="services-grid">
                <?php foreach ($serviciosActivos as $srv): ?>
                <div class="service-card reveal">
                    <div class="service-img-box">
                        <span class="service-cat-badge">
                            <strong>#<?= htmlspecialchars($srv['numero_paso'] ?? $srv['orden']) ?></strong> • <?= htmlspecialchars($srv['categoria']) ?>
                        </span>
                        <img src="<?= htmlspecialchars($srv['imagen_url']) ?>" alt="<?= htmlspecialchars($srv['titulo']) ?>">
                    </div>
                    <div class="service-content">
                        <h3><?= htmlspecialchars($srv['titulo']) ?></h3>
                        <p style="font-weight:700;color:#7CC2FF;margin-bottom:8px;"><?= htmlspecialchars($srv['resumen']) ?></p>
                        <p><?= htmlspecialchars($srv['descripcion']) ?></p>

                        <?php
                        $items = array_filter(array_map('trim', explode('|', $srv['incluye_items'] ?? '')));
                        if (count($items) > 0):
                        ?>
                        <ul class="service-Checklist">
                            <?php foreach ($items as $it): ?>
                            <li><i class="fa-solid fa-circle-check"></i> <span><?= htmlspecialchars($it) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>

                        <div style="display:flex;align-items:center;justify-content:space-between;background:rgba(3,17,38,0.75);border:1px solid rgba(251,191,36,0.35);padding:10px 14px;border-radius:10px;margin-bottom:14px;">
                            <span style="font-size:0.78rem;color:#CBD5E1;font-weight:700;">Inversión referencial:</span>
                            <strong style="color:#FBBF24;font-size:0.95rem;"><?= htmlspecialchars($srv['tarifa_referencial'] ?: 'Consultar') ?></strong>
                        </div>

                        <a href="contacto.php?servicio=<?= urlencode($srv['titulo']) ?>" class="btn btn-primary" style="width:100%;">
                            <i class="fa-solid fa-comments"></i> Coordinar por Interno
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ==================== PÁGINA 5 DEL PDF: ÁREAS DE ESPECIALIZACIÓN ==================== -->
    <section class="section section-alt" id="especialidades">
        <div class="container">
            <div class="reveal" style="margin-bottom:44px;border-radius:24px;overflow:hidden;border:2px solid var(--ia-border-bright);max-height:260px;box-shadow:var(--shadow-lg);">
                <img src="<?= htmlspecialchars($imgEspecialidades['imagen_url']) ?>" alt="<?= htmlspecialchars($imgEspecialidades['titulo']) ?>" style="width:100%;height:260px;object-fit:cover;display:block;">
            </div>

            <div class="section-header reveal">
                <span class="section-tag"><i class="fa-solid fa-layer-group"></i> Disciplinas Académicas</span>
                <h2 class="section-title">Áreas de Especialización</h2>
                <p class="section-subtitle">
                    Contamos con expertos en diversas disciplinas para garantizar un acompañamiento de calidad.
                </p>
            </div>

            <div class="steps-grid" style="grid-template-columns:repeat(auto-fit,minmax(250px,1fr));">
                <?php
                $areas = [
                    ['icono' => 'fa-briefcase', 'nombre' => 'Administración', 'desc' => 'Gestión empresarial, marketing, negocios internacionales, logística y talento humano.'],
                    ['icono' => 'fa-calculator', 'nombre' => 'Contabilidad', 'desc' => 'Finanzas corporativas, tributación, auditoría, costos y control interno.'],
                    ['icono' => 'fa-chart-line', 'nombre' => 'Economía', 'desc' => 'Econometría, políticas públicas, proyectos de inversión y macroeconomía.'],
                    ['icono' => 'fa-chalkboard-user', 'nombre' => 'Educación', 'desc' => 'Pedagogía, gestión educativa, didáctica, currículo y docencia universitaria.'],
                    ['icono' => 'fa-gears', 'nombre' => 'Ingeniería', 'desc' => 'Industrial, Sistemas, Civil, Ambiental, Empresarial, Minas y afines.'],
                    ['icono' => 'fa-gavel', 'nombre' => 'Derecho', 'desc' => 'Civil, Penal, Constitucional, Laboral, Administrativo y Corporativo.'],
                    ['icono' => 'fa-brain', 'nombre' => 'Psicología', 'desc' => 'Clínica, organizacional, educativa, psicométrica y comunitaria.'],
                    ['icono' => 'fa-heart-pulse', 'nombre' => 'Ciencias de la Salud', 'desc' => 'Medicina, Enfermería, Obstetricia, Odontología, Farmacia y Salud Pública.']
                ];
                foreach ($areas as $a):
                ?>
                <div class="step-card reveal">
                    <div class="step-number"><i class="fa-solid <?= $a['icono'] ?>"></i></div>
                    <h4><?= $a['nombre'] ?></h4>
                    <p><?= $a['desc'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
