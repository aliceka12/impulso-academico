<?php
/**
 * IMPULSO ACADÉMICO - Página Independiente: ¿Quiénes Somos?, Misión, Visión y Valores Fundamentales
 * Imágenes y textos jalados directamente de la Base de Datos MySQL.
 */
$paginaActual = 'nosotros.php';
$tituloPagina = '¿Quiénes Somos? • Misión, Visión y Valores';
require_once __DIR__ . '/includes/header.php';

$imgQuienes = $seccionesImg['quienes_somos'] ?? [
    'imagen_url' => 'uploads/img/pdf_img_3.jpg',
    'titulo' => '¿Quiénes Somos?',
    'subtitulo' => 'Equipo de expertos dedicados a potenciar tus habilidades investigativas'
];
$imgMisionVision = $seccionesImg['mision_vision'] ?? [
    'imagen_url' => 'uploads/img/pdf_img_5.jpg',
    'titulo' => 'Nuestra Misión y Visión',
    'subtitulo' => 'En Impulso Académico, nos movemos por un propósito claro'
];
?>

    <!-- ==================== CABECERA DE PÁGINA ==================== -->
    <section class="page-hero-banner">
        <span class="section-tag"><i class="fa-solid fa-building-columns"></i> Identidad Institucional</span>
        <h1>¿Quiénes Somos?</h1>
        <p>Conoce nuestra historia, nuestro propósito institucional y los principios éticos que guían cada asesoría en Impulso Académico.</p>
    </section>

    <!-- ==================== SECCIÓN 1: ¿QUIÉNES SOMOS? (PÁGINA 2 DEL PDF) ==================== -->
    <section class="section">
        <div class="container">
            <div class="hero-container">
                <div class="reveal visible">
                    <span class="section-tag"><i class="fa-solid fa-users-gear"></i> Equipo de Expertos</span>
                    <h2 class="section-title" style="font-size:2.4rem;">Impulsamos tus Habilidades Investigativas</h2>
                    <p style="font-size:1.08rem;color:#E2EEFF;margin-bottom:18px;line-height:1.8;">
                        <?= htmlspecialchars($cfg['quienes_somos_p1'] ?? 'En IMPULSO ACADÉMICO, somos un equipo de expertos dedicados a potenciar tus habilidades investigativas. Nos especializamos en brindar asesoría académica y metodológica de alta calidad, diseñada para estudiantes universitarios de pregrado y posgrado.') ?>
                    </p>
                    <p style="font-size:1.08rem;color:#B8D4F8;margin-bottom:28px;line-height:1.8;">
                        <?= htmlspecialchars($cfg['quienes_somos_p2'] ?? 'Ofrecemos un acompañamiento ético y personalizado, guiándote paso a paso en el desarrollo de tus trabajos de investigación, desde la conceptualización hasta la sustentación final.') ?>
                    </p>

                    <div style="display:flex;gap:14px;flex-wrap:wrap;">
                        <a href="servicios.php" class="btn btn-primary">
                            <i class="fa-solid fa-list-check"></i> Ver Servicios que Ofrecemos
                        </a>
                        <a href="contacto.php" class="btn btn-outline-light">
                            <i class="fa-solid fa-comments"></i> Hablar con un Especialista
                        </a>
                    </div>
                </div>

                <div class="reveal">
                    <div class="hero-main-image-card">
                        <img src="<?= htmlspecialchars($imgQuienes['imagen_url']) ?>" alt="<?= htmlspecialchars($imgQuienes['titulo']) ?>" style="max-height:460px;object-fit:cover;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== SECCIÓN 2: NUESTRA MISIÓN Y VISIÓN (PÁGINA 3 DEL PDF) ==================== -->
    <section class="section section-alt">
        <div class="container">
            <div class="hero-container">
                <div class="reveal">
                    <div class="hero-main-image-card">
                        <img src="<?= htmlspecialchars($imgMisionVision['imagen_url']) ?>" alt="<?= htmlspecialchars($imgMisionVision['titulo']) ?>" style="max-height:450px;object-fit:cover;">
                    </div>
                </div>

                <div class="reveal">
                    <span class="section-tag"><i class="fa-solid fa-compass"></i> Propósito Claro</span>
                    <h2 class="section-title">Nuestra Misión y Visión</h2>
                    <p style="color:#B8D4F8;margin-bottom:24px;font-size:1.04rem;">
                        En Impulso Académico, nos movemos por un propósito claro:
                    </p>

                    <div style="background:rgba(6,34,80,0.85);border:1.5px solid rgba(56,152,255,0.45);border-left:5px solid #3898FF;border-radius:18px;padding:26px;margin-bottom:20px;box-shadow:var(--shadow-md);">
                        <h3 style="font-size:1.35rem;font-weight:800;color:#3898FF;margin-bottom:8px;">
                            <i class="fa-solid fa-bullseye"></i> Misión
                        </h3>
                        <p style="color:#E2EEFF;font-size:1rem;">
                            <?= htmlspecialchars($cfg['mision'] ?? 'Brindar asesoría académica de calidad excepcional que fortalezca significativamente las competencias investigativas y el pensamiento crítico de nuestros estudiantes.') ?>
                        </p>
                    </div>

                    <div style="background:rgba(6,34,80,0.85);border:1.5px solid rgba(56,152,255,0.45);border-left:5px solid #0074F0;border-radius:18px;padding:26px;box-shadow:var(--shadow-md);">
                        <h3 style="font-size:1.35rem;font-weight:800;color:#7CC2FF;margin-bottom:8px;">
                            <i class="fa-solid fa-eye"></i> Visión
                        </h3>
                        <p style="color:#E2EEFF;font-size:1rem;">
                            <?= htmlspecialchars($cfg['vision'] ?? 'Ser reconocida como la empresa líder en asesoría de tesis, destacando por nuestra excelencia y ética, a nivel nacional e internacional.') ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== SECCIÓN 3: NUESTROS VALORES FUNDAMENTALES (PÁGINA 8 DEL PDF) ==================== -->
    <section class="section">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-tag"><i class="fa-solid fa-shield-heart"></i> Nuestros Pilares</span>
                <h2 class="section-title">Nuestros Valores Fundamentales</h2>
                <p class="section-subtitle">
                    Principios que guían cada asesoría en Impulso Académico.
                </p>
            </div>

            <div class="steps-grid">
                <div class="step-card reveal">
                    <div class="step-number"><i class="fa-solid fa-scale-balanced"></i></div>
                    <h4>Ética Académica</h4>
                    <p>Promovemos la integridad y la originalidad en cada trabajo de investigación.</p>
                </div>

                <div class="step-card reveal">
                    <div class="step-number"><i class="fa-solid fa-handshake"></i></div>
                    <h4>Responsabilidad</h4>
                    <p>Compromiso total con tus objetivos académicos y el cumplimiento estricto de los plazos acordados.</p>
                </div>

                <div class="step-card reveal">
                    <div class="step-number"><i class="fa-solid fa-lock"></i></div>
                    <h4>Confidencialidad</h4>
                    <p>Total discreción y protección absoluta de tu información personal y académica.</p>
                </div>

                <div class="step-card reveal">
                    <div class="step-number"><i class="fa-solid fa-user-graduate"></i></div>
                    <h4>Calidad Profesional</h4>
                    <p>Experiencia y conocimiento especializado al servicio de tu éxito investigativo.</p>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
