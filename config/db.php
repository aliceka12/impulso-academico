<?php
/**
 * IMPULSO ACADÉMICO - Sistema de Gestión y Apoyo a la Investigación
 * Conexión a Base de Datos MySQL + Auto-Instalación con Contenido Oficial del Brochure 2026
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Lima');

define('DB_HOST', getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: '127.0.0.1'));
define('DB_PORT', getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: '3306'));
define('DB_NAME', getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: 'impulso_academico'));
define('DB_USER', getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root'));
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : ''));

function getPDO(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $dsnServer = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
        $pdoServer = new PDO($dsnServer, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        inicializarEsquemaYDatos($pdo);
        return $pdo;
    } catch (PDOException $e) {
        die("<div style='font-family:Segoe UI,sans-serif;max-width:650px;margin:60px auto;padding:30px;border-radius:16px;background:#fff;border:2px solid #0074F0;box-shadow:0 10px 30px rgba(4,33,82,0.12);'>"
            . "<h2 style='color:#042152;margin-top:0;'>⚠️ Error de Conexión a MySQL</h2>"
            . "<p style='color:#334155;line-height:1.6;'>Asegúrate de que el servicio <strong>MySQL</strong> esté activo en tu panel de <strong>Laragon</strong> o <strong>XAMPP</strong>.</p>"
            . "<pre style='background:#F1F5F9;padding:12px;border-radius:8px;color:#DC2626;overflow:auto;'>" . htmlspecialchars($e->getMessage()) . "</pre>"
            . "</div>");
    }
}

function inicializarEsquemaYDatos(PDO $pdo): void {
    $stmt = $pdo->query("SHOW TABLES LIKE 'tarifas_servicios'");
    if ($stmt->rowCount() > 0) {
        return;
    }

    $sqlFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        $pdo->exec($sql);
    }

    // 1. Usuario administrador por defecto: admin / impulso2026
    $passHash = password_hash('impulso2026', PASSWORD_DEFAULT);
    $stmtAdmin = $pdo->prepare("INSERT IGNORE INTO usuarios_admin (usuario, password_hash, nombre, cargo) VALUES (?, ?, ?, ?)");
    $stmtAdmin->execute(['admin', $passHash, 'Administrador Impulso Académico', 'Dirección General']);

    // 2. Configuración general oficial (Del PDF de Impulso Académico - Lima 2026)
    $configs = [
        ['nombre_empresa', 'Impulso Académico', 'Nombre comercial'],
        ['subtitulo_empresa', 'Sistema de Gestión y Apoyo a la Investigación', 'Subtítulo oficial del logo'],
        ['titulo_portada', 'ASESORÍA DE TESIS ACADÉMICA', 'Título oficial página 1'],
        ['subtitulo_portada', 'Acompañamiento profesional para pregrado y posgrado', 'Subtítulo oficial página 1'],
        ['lema_oficial', '¡Tu éxito es nuestro impulso!', 'Lema oficial página 10'],
        ['logo_url', 'uploads/img/logo.jpg', 'Logo definitivo jalado desde la BD'],
        ['quienes_somos_p1', 'En IMPULSO ACADÉMICO, somos un equipo de expertos dedicados a potenciar tus habilidades investigativas. Nos especializamos en brindar asesoría académica y metodológica de alta calidad, diseñada para estudiantes universitarios de pregrado y posgrado.', 'Quiénes somos párrafo 1 (Pág. 2)'],
        ['quienes_somos_p2', 'Ofrecemos un acompañamiento ético y personalizado, guiándote paso a paso en el desarrollo de tus trabajos de investigación, desde la conceptualización hasta la sustentación final.', 'Quiénes somos párrafo 2 (Pág. 2)'],
        ['mision', 'Brindar asesoría académica de calidad excepcional que fortalezca significativamente las competencias investigativas y el pensamiento crítico de nuestros estudiantes.', 'Misión oficial (Pág. 3)'],
        ['vision', 'Ser reconocida como la empresa líder en asesoría de tesis, destacando por nuestra excelencia y ética, a nivel nacional e internacional.', 'Visión oficial (Pág. 3)'],
        ['whatsapp_numero', '51953697434', 'Número principal de WhatsApp (Pág. 10)'],
        ['whatsapp_secundario', '51900439160', 'Segundo número de WhatsApp (Pág. 10)'],
        ['telefono_visible', '953 697 434 / 900 439 160', 'Teléfonos visibles en la web (Pág. 10)'],
        ['correo_contacto', 'contacto@impulsoacademico.pe', 'Correo institucional'],
        ['ciudad_anio', 'Lima – 2026', 'Ubicación y año (Pág. 1)'],
        ['redes_sociales', 'Facebook, Instagram y TikTok', 'Redes sociales (Pág. 10)'],
        ['moneda_simbolo', 'S/', 'Símbolo monetario de estados financieros'],
        ['mensaje_pagos_internos', 'No utilizamos pasarelas de cobro automático en la web: toda inversión, facilidad de pago por capítulos y cronograma se coordina directamente por interno con nuestro equipo.', 'Aviso de coordinación interna']
    ];
    $stmtCfg = $pdo->prepare("INSERT IGNORE INTO configuracion_web (clave, valor, descripcion) VALUES (?, ?, ?)");
    foreach ($configs as $c) {
        $stmtCfg->execute($c);
    }

    // 3. Galería de imágenes dinámicas de la web (Usando las fotos reales extraídas del PDF + ilustraciones en la BD)
    $imagenes = [
        [
            'logo',
            'Logo Oficial Impulso Académico',
            'Sistema de Gestión y Apoyo a la Investigación',
            'Identidad visual definitiva de Impulso Académico.',
            'uploads/img/logo.jpg',
            'Identidad',
            1,
            'ACTIVO'
        ],
        [
            'hero_principal',
            'Asesoría de Tesis Académica',
            'Acompañamiento profesional para pregrado y posgrado',
            'Presentado por Impulso Académico • Lima – 2026.',
            'uploads/img/pdf_img_2.jpg',
            'Portada Principal',
            1,
            'ACTIVO'
        ],
        [
            'quienes_somos',
            '¿Quiénes Somos?',
            'Equipo de expertos dedicados a potenciar tus habilidades investigativas',
            'Acompañamiento ético y personalizado desde la conceptualización hasta la sustentación final.',
            'uploads/img/pdf_img_3.jpg',
            'Institucional',
            1,
            'ACTIVO'
        ],
        [
            'mision_vision',
            'Nuestra Misión y Visión',
            'En Impulso Académico, nos movemos por un propósito claro',
            'Fortalecemos las competencias investigativas y el pensamiento crítico con excelencia y ética.',
            'uploads/img/pdf_img_5.jpg',
            'Propósito',
            1,
            'ACTIVO'
        ],
        [
            'servicios_banner',
            'Servicios que Ofrecemos',
            'Te acompañamos en cada etapa de tu investigación',
            'Desde la elección del tema hasta la reducción de similitud Turnitin y preparación para sustentación.',
            'uploads/img/pdf_img_6.jpg',
            'Servicios',
            1,
            'ACTIVO'
        ],
        [
            'especialidades_banner',
            'Áreas de Especialización',
            'Contamos con expertos en diversas disciplinas para garantizar un acompañamiento de calidad',
            'Administración, Contabilidad, Economía, Educación, Ingeniería, Derecho, Psicología y Ciencias de la Salud.',
            'uploads/img/pdf_img_8.jpg',
            'Especialidades',
            1,
            'ACTIVO'
        ],
        [
            'metodologia_banner',
            'Nuestra Metodología de Trabajo',
            'Un proceso claro y eficiente para tu éxito académico',
            'Diagnóstico inicial, plan personalizado, asesorías constantes, entregables revisados y seguimiento completo.',
            'uploads/img/pdf_img_9.jpg',
            'Metodología',
            1,
            'ACTIVO'
        ],
        [
            'modalidad_banner',
            'Modalidad de Atención',
            'Flexibilidad y comodidad, pensadas para ti',
            'Atención virtual completa vía Zoom, Google Meet y WhatsApp a nivel nacional e internacional.',
            'uploads/img/pdf_img_10.jpg',
            'Modalidad',
            1,
            'ACTIVO'
        ],
        [
            'contacto_banner',
            '¡Contáctanos! Tu éxito es nuestro impulso',
            'Estamos listos para ayudarte a alcanzar tus metas académicas',
            'Atención directa por WhatsApp 953697434 / 900439160 y en Facebook, Instagram y TikTok.',
            'uploads/img/pdf_img_11.jpg',
            'Contacto',
            1,
            'ACTIVO'
        ],
        [
            'galeria_exito',
            'Graduados de Pregrado y Posgrado',
            'Acompañamiento profesional desde el tema hasta la sustentación',
            'Estudiantes asesorados exitosamente con rigor metodológico y ético.',
            'uploads/img/pdf_img_6.jpg',
            'Éxito Académico',
            1,
            'ACTIVO'
        ],
        [
            'galeria_exito',
            'Asesoría Personalizada Multidisciplinaria',
            'Expertos en Ingeniería, Salud, Derecho, Negocios y Educación',
            'Sesiones de trabajo y revisión continua de entregables con nuestros especialistas.',
            'uploads/img/pdf_img_3.jpg',
            'Equipo Experto',
            2,
            'ACTIVO'
        ],
        [
            'galeria_exito',
            'Defensa y Sustentación con Confianza',
            'Seguimiento completo hasta la aprobación final del jurado',
            'Preparación integral con diapositivas profesionales y dominio del tema.',
            'uploads/img/pdf_img_9.jpg',
            'Sustentaciones',
            3,
            'ACTIVO'
        ]
    ];
    $stmtImg = $pdo->prepare("INSERT INTO galeria_imagenes (clave_seccion, titulo, subtitulo, descripcion, imagen_url, etiqueta, orden, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($imagenes as $img) {
        $stmtImg->execute($img);
    }

    // 4. Los 8 Servicios Oficiales de la Página 4 del PDF (Con sus imágenes en BD)
    $servicios = [
        [
            '1',
            'Elección y Delimitación del Tema',
            'Te ayudamos a encontrar un tema relevante y a definir sus límites de manera efectiva.',
            'Evaluamos la viabilidad, originalidad y acceso a fuentes de información para que inicies tu investigación con un tema sólido y alineado a las líneas de tu universidad.',
            'Etapa 1 • Inicio',
            'S/ 30 – 50 / hora',
            'uploads/img/servicio_plan_tesis.svg',
            'fa-lightbulb',
            'Propuesta de temas viables y actuales|Delimitación espacial, temporal y temática|Revisión de disponibilidad bibliográfica',
            1, 1, 'ACTIVO'
        ],
        [
            '2',
            'Planteamiento del Problema',
            'Formulación clara y concisa de tu pregunta de investigación y objetivos.',
            'Estructuramos la realidad problemática, formulación del problema general y específicos, objetivos de investigación, justificación y matriz de consistencia.',
            'Etapa 2 • Problema',
            'S/ 30 – 50 / hora',
            'uploads/img/servicio_tesis_completa.svg',
            'fa-bullseye',
            'Realidad problemática fundamentada|Problema y objetivos generales y específicos|Justificación teórica, práctica y metodológica',
            1, 2, 'ACTIVO'
        ],
        [
            '3',
            'Marco Teórico y Antecedentes',
            'Construcción de una base sólida para tu estudio con revisión bibliográfica exhaustiva.',
            'Búsqueda y redacción de antecedentes nacionales e internacionales en bases de datos indexadas (Scopus, Web of Science, SciELO) y desarrollo de bases teóricas.',
            'Etapa 3 • Fundamentación',
            'S/ 30 – 50 / hora',
            'uploads/img/servicio_plan_tesis.svg',
            'fa-book-bookmark',
            'Antecedentes nacionales e internacionales actualizados|Bases teóricas por variables y dimensiones|Definición de términos básicos',
            1, 3, 'ACTIVO'
        ],
        [
            '4',
            'Metodología de Investigación',
            'Diseño de la estrategia adecuada para recolectar y analizar tus datos.',
            'Definimos el enfoque, tipo, nivel y diseño de investigación, población y muestra, operacionalización de variables y validación de instrumentos de recolección.',
            'Etapa 4 • Diseño',
            'S/ 30 – 50 / hora',
            'uploads/img/servicio_observaciones.svg',
            'fa-compass-drafting',
            'Enfoque, tipo y diseño metodológico|Cálculo de muestra y muestreo|Operacionalización y validación de instrumentos',
            1, 4, 'ACTIVO'
        ],
        [
            '5',
            'Análisis Estadístico',
            'Soporte experto en herramientas como SPSS, Stata y Excel para interpretar tus resultados.',
            'Procesamiento descriptivo e inferencial, pruebas de normalidad, confiabilidad (Alfa de Cronbach) y contrastación de hipótesis con tablas y gráficos interpretados.',
            'Etapa 5 • Resultados',
            'S/ 400 – 800',
            'uploads/img/servicio_estadistica.svg',
            'fa-chart-column',
            'Procesamiento en SPSS, Stata y Excel|Pruebas de confiabilidad y normalidad|Contrastación de hipótesis e interpretación',
            1, 5, 'ACTIVO'
        ],
        [
            '6',
            'Redacción Académica (APA 7.ª ed.)',
            'Asesoramiento para una escritura clara, coherente y conforme a las normativas APA.',
            'Revisión integral de estilo científico, coherencia argumentativa, formato de tablas y figuras, y citación y referenciación rigurosa bajo normas APA 7.ª edición.',
            'Etapa 6 • Normativa',
            'Incluido / Por capítulo',
            'uploads/img/servicio_turnitin.svg',
            'fa-pen-nib',
            'Citas directas e indirectas en APA 7.ª ed.|Formato de tablas, figuras y anexos|Referencias bibliográficas automatizadas',
            1, 6, 'ACTIVO'
        ],
        [
            '7',
            'Preparación para Sustentación',
            'Desarrollo de habilidades para presentar y defender tu investigación con confianza.',
            'Elaboramos tus diapositivas de presentación (profesionales o básicas) y realizamos simulacros de defensa con retroalimentación personalizada para responder al jurado.',
            'Etapa 7 • Defensa',
            'S/ 200 – 300',
            'uploads/img/servicio_sustentacion.svg',
            'fa-chalkboard-user',
            'Diapositivas profesionales (S/ 250) o básicas (S/ 100)|Simulacro de defensa ante jurado|Asesoría en expresión oral y preguntas clave',
            1, 7, 'ACTIVO'
        ],
        [
            '8',
            'Reducción de Similitud - Turnitin',
            'Estrategias y técnicas para asegurar la originalidad de tu trabajo.',
            'Parafraseo académico ético y especializado para reducir el porcentaje de similitud Turnitin cumpliendo estrictamente el límite exigido por tu universidad.',
            'Etapa 8 • Originalidad',
            'S/ 100 – 150',
            'uploads/img/servicio_turnitin.svg',
            'fa-shield-halved',
            'Parafraseo académico conservando el sentido técnico|Reporte de similitud Turnitin|Levantamiento de observaciones (S/ 300 – 600)',
            1, 8, 'ACTIVO'
        ]
    ];
    $stmtSrv = $pdo->prepare("INSERT INTO servicios (numero_paso, titulo, resumen, descripcion, categoria, tarifa_referencial, imagen_url, icono, incluye_items, destacado, orden, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($servicios as $s) {
        $stmtSrv->execute($s);
    }

    // 5. Tarifas Oficiales de la Página 9 del PDF
    $tarifas = [
        ['Asesoría por Capítulo en Horas', 'S/ 30 – 50', 'Acompañamiento personalizado por hora según avance capitular', 1, 'ACTIVO'],
        ['Realización Completa de la Tesis', 'S/ 2000 – 3000', 'Acompañamiento integral desde el tema hasta la sustentación final', 2, 'ACTIVO'],
        ['Análisis Estadístico', 'S/ 400 – 800', 'Procesamiento en SPSS, Stata y Excel con interpretación de resultados', 3, 'ACTIVO'],
        ['Preparación para Sustentación', 'S/ 200 – 300', 'Entrenamiento y simulacro para defender tu tesis con confianza', 4, 'ACTIVO'],
        ['Levantamiento de Observaciones', 'S/ 300 – 600', 'Subsanación experta de correcciones de asesores y jurados', 5, 'ACTIVO'],
        ['Realización de Diapositivas de Presentación Profesionales', 'S/ 250', 'Diseño ejecutivo de alto impacto visual para sustentación', 6, 'ACTIVO'],
        ['Realización de Diapositivas de Presentación Básicas', 'S/ 100', 'Presentación estructurada y clara para defensa académica', 7, 'ACTIVO'],
        ['Reducción de Similitud – Turnitin', 'S/ 100 – 150', 'Parafraseo académico y estrategias para asegurar la originalidad', 8, 'ACTIVO']
    ];
    $stmtTar = $pdo->prepare("INSERT INTO tarifas_servicios (servicio_nombre, rango_inversion, detalle, orden, estado) VALUES (?, ?, ?, ?, ?)");
    foreach ($tarifas as $t) {
        $stmtTar->execute($t);
    }

    // 6. Campañas de Descuento Iniciales (Administrables desde el Panel)
    $campanas = [
        [
            'Campaña Impulso Académico 2026: ¡Tu Tesis Completa con Descuento Especial!',
            'Obtén 20% OFF en Realización Completa de Tesis + Diagnóstico Inicial Gratuito',
            'Coordina tu cronograma de pagos en cuotas por interno por WhatsApp (953697434 / 900439160). Válido para Pregrado y Posgrado.',
            20,
            'IMPULSO2026',
            '🔥 CAMPAÑA OFICIAL LIMA 2026',
            'uploads/campanas/campana_octubre.svg',
            date('Y-m-01'),
            date('Y-m-d', strtotime('+25 days')),
            1,
            1,
            'INACTIVA'
        ],
        [
            'Pack Análisis Estadístico (SPSS/Stata) + Reducción Turnitin',
            '25% de Descuento contratando Procesamiento Estadístico + Originalidad Turnitin',
            'Soporte experto en SPSS, Stata y Excel más reducción de similitud con formato APA 7.ª edición.',
            25,
            'STATS25',
            '⚡ PROMO ESTADÍSTICA + TURNITIN',
            'uploads/campanas/campana_pack_estadistica.svg',
            date('Y-m-01'),
            date('Y-m-d', strtotime('+30 days')),
            0,
            0,
            'INACTIVA'
        ]
    ];
    $stmtCamp = $pdo->prepare("INSERT INTO campanas_descuento (titulo, subtitulo, descripcion, porcentaje_descuento, codigo_promo, etiqueta_oferta, imagen_url, fecha_inicio, fecha_fin, mostrar_banner_superior, mostrar_popup, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($campanas as $camp) {
        $stmtCamp->execute($camp);
    }

    // 7. Videos Promocionales Iniciales (Administrables desde el Panel)
    $videos = [
        [
            'Conoce nuestra Metodología de Trabajo en 5 Pasos • Impulso Académico',
            '1. Diagnóstico Inicial • 2. Plan Personalizado • 3. Asesorías Constantes • 4. Entregables Revisados • 5. Seguimiento Completo hasta tu sustentación.',
            'Metodología Oficial',
            'ARCHIVO',
            'uploads/videos/promo_impulso.mp4',
            'uploads/img/pdf_img_2.jpg',
            '01:15',
            1,
            'ACTIVO',
            1
        ],
        [
            'Análisis Estadístico (SPSS, Stata, Excel) y Reducción de Similitud Turnitin',
            'Conoce cómo aseguramos la calidad y originalidad de tu investigación bajo normativa APA 7.ª edición.',
            'Servicios Destacados',
            'ARCHIVO',
            'uploads/videos/promo_turnitin.mp4',
            'uploads/img/pdf_img_5.jpg',
            '01:05',
            0,
            'ACTIVO',
            2
        ]
    ];
    $stmtVid = $pdo->prepare("INSERT INTO videos_promocionales (titulo, descripcion, categoria, tipo_fuente, video_url, poster_url, duracion, destacado, estado, orden) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($videos as $v) {
        $stmtVid->execute($v);
    }

    // 8. Clientes y Estados Financieros reales (Alineados a las tarifas oficiales del PDF)
    $clientes = [
        [
            'IA-2026-001', 'Carlos Eduardo', 'Mendoza Ríos', '72849102', '987112233', 'cmendoza@email.com',
            'Universidad Nacional Mayor de San Marcos', 'Ingeniería', 'Pregrado',
            'Realización Completa de la Tesis',
            'Mejora de la gestión de almacenes mediante Lean Warehousing para reducir costos logísticos en una empresa distribuidora, Lima 2026',
            'Dr. Ing. Roberto Salazar', 'Redacción APA 7 / Turnitin', 85,
            2800.00, 200.00, 2600.00, 2000.00, 600.00, 4, 'AL_DIA',
            date('Y-m-d', strtotime('-45 days')), date('Y-m-d', strtotime('+15 days')),
            'Cliente puntual. Ya aprobó capítulos I, II y III. Falta liquidar última cuota de S/ 600 contra entrega de Turnitin final.'
        ],
        [
            'IA-2026-002', 'María Fernanda', 'Quispe Paredes', '46918273', '991223344', 'mquispe.posgrado@email.com',
            'Universidad César Vallejo', 'Administración', 'Maestría',
            'Realización Completa de la Tesis',
            'Gobierno digital y calidad de atención al ciudadano en una municipalidad distrital de Lima Metropolitana, 2026',
            'Mg. Lucía Fernández', 'Listo para Sustentar', 100,
            3000.00, 0.00, 3000.00, 3000.00, 0.00, 3, 'PAGADO_TOTAL',
            date('Y-m-d', strtotime('-60 days')), date('Y-m-d', strtotime('-2 days')),
            'Tesis 100% terminada + Diapositivas Profesionales entregadas. Pago liquidado en su totalidad.'
        ],
        [
            'IA-2026-003', 'Jorge Luis', 'Vargas Huamán', '71029384', '944556677', 'jvargas.med@email.com',
            'Universidad de San Martín de Porres', 'Ciencias de la Salud', 'Pregrado',
            'Análisis Estadístico',
            'Factores asociados a la adherencia terapéutica en pacientes hipertensos atendidos en un hospital nacional, 2026',
            'Lic. Estad. Marcos Villavicencio', 'Análisis Estadístico', 65,
            750.00, 50.00, 700.00, 400.00, 300.00, 2, 'PENDIENTE',
            date('Y-m-d', strtotime('-15 days')), date('Y-m-d', strtotime('+7 days')),
            'Procesamiento en SPSS y Stata al 65%. Segundo abono de S/ 300 programado para esta semana.'
        ],
        [
            'IA-2026-004', 'Andrea Valeria', 'Torres Castro', '74829103', '965432189', 'andrea.torres@email.com',
            'Pontificia Universidad Católica del Perú', 'Derecho', 'Maestría',
            'Realización Completa de la Tesis',
            'La responsabilidad civil derivada del uso de sistemas de inteligencia artificial en la contratación financiera en el Perú',
            'Dr. Javier Montenegro', 'Marco Teórico', 50,
            2600.00, 200.00, 2400.00, 1200.00, 1200.00, 2, 'AL_DIA',
            date('Y-m-d', strtotime('-12 days')), date('Y-m-d', strtotime('+18 days')),
            'Planteamiento del problema y marco teórico aprobados. En desarrollo del diseño metodológico.'
        ],
        [
            'IA-2026-005', 'Diego Alejandro', 'Rojas Lozano', '70918274', '955887766', 'drojas.adm@email.com',
            'Universidad Tecnológica del Perú', 'Contabilidad', 'Pregrado',
            'Levantamiento de Observaciones',
            'Control interno y gestión de inventarios en una empresa comercializadora de Lima, 2026',
            'Mg. Lucía Fernández', 'Levantando Observaciones', 80,
            550.00, 0.00, 550.00, 300.00, 250.00, 2, 'VENCIDO',
            date('Y-m-d', strtotime('-20 days')), date('Y-m-d', strtotime('-1 days')),
            'Observaciones de jurado ya levantadas al 80%. Recordar por WhatsApp el saldo de S/ 250.'
        ]
    ];
    $stmtCli = $pdo->prepare("INSERT INTO clientes (
        codigo_cliente, nombres, apellidos, dni, telefono, email, universidad, carrera, nivel_academico,
        servicio_contratado, titulo_tesis, asesor_asignado, estado_tesis, porcentaje_avance,
        costo_total, descuento_aplicado, monto_final, total_abonado, saldo_pendiente, numero_cuotas,
        estado_financiero, fecha_inicio, fecha_entrega_estimada, notas_internas
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($clientes as $cli) {
        $stmtCli->execute($cli);
    }

    // 9. Movimientos Financieros (Ingresos y Egresos)
    $movimientos = [
        ['INGRESO', 'Pago Inicial', 1, '1ra y 2da Cuota - Tesis Completa Ingeniería (Carlos Mendoza)', 1300.00, 'Transferencia BCP', 'OP-849201', date('Y-m-d', strtotime('-30 days')), 'CONFIRMADO', 'Abono inicial y marco teórico'],
        ['INGRESO', 'Abono de Cliente', 1, '3ra Cuota - Avance Capítulo Estadístico (Carlos Mendoza)', 700.00, 'Yape', 'YP-392014', date('Y-m-d', strtotime('-10 days')), 'CONFIRMADO', 'Entregado capítulo de resultados'],
        ['INGRESO', 'Pago Inicial', 2, '1ra y 2da Cuota - Tesis Completa Maestría (María Quispe)', 2000.00, 'Transferencia Interbank', 'IBK-550192', date('Y-m-d', strtotime('-45 days')), 'CONFIRMADO', 'Abono inicial y desarrollo de campo'],
        ['INGRESO', 'Liquidación Final', 2, 'Liquidación Final 3ra Cuota - Tesis Maestría (María Quispe)', 1000.00, 'Transferencia BCP', 'OP-991042', date('Y-m-d', strtotime('-4 days')), 'CONFIRMADO', 'Tesis aprobada al 100%'],
        ['INGRESO', 'Pago Inicial', 3, '1ra Cuota - Análisis Estadístico SPSS/Stata (Jorge Vargas)', 400.00, 'Plin', 'PL-772810', date('Y-m-d', strtotime('-14 days')), 'CONFIRMADO', 'Inicio de procesamiento estadístico'],
        ['INGRESO', 'Pago Inicial', 4, '1ra Cuota (50%) - Tesis Derecho PUCP (Andrea Torres)', 1200.00, 'Transferencia BBVA', 'BBVA-110293', date('Y-m-d', strtotime('-11 days')), 'CONFIRMADO', 'Inicio de Tesis de Maestría'],
        ['INGRESO', 'Pago Inicial', 5, '1ra Cuota - Levantamiento de Observaciones (Diego Rojas)', 300.00, 'Yape', 'YP-661920', date('Y-m-d', strtotime('-19 days')), 'CONFIRMADO', 'Inicio de levantamiento de observaciones'],
        // Egresos operativos
        ['EGRESO', 'Honorarios de Asesor', null, 'Pago de honorarios metodológicos - Equipo de Expertos Impulso Académico', 1650.00, 'Transferencia BCP', 'EG-2026-01', date('Y-m-d', strtotime('-8 days')), 'CONFIRMADO', 'Liquidación quincenal de asesores'],
        ['EGRESO', 'Licencias / Turnitin / SPSS', null, 'Renovación mensual Licencias Turnitin, SPSS y Stata', 380.00, 'Transferencia Interbank', 'EG-2026-02', date('Y-m-d', strtotime('-12 days')), 'CONFIRMADO', 'Licencias operativas de software'],
        ['EGRESO', 'Publicidad y Marketing', null, 'Pauta en Facebook, Instagram y TikTok - Impulso Académico', 490.00, 'Transferencia BCP', 'EG-2026-03', date('Y-m-d', strtotime('-5 days')), 'CONFIRMADO', 'Campaña redes sociales Lima 2026']
    ];
    $stmtMov = $pdo->prepare("INSERT INTO movimientos_financieros (tipo, categoria, cliente_id, concepto, monto, metodo_pago, referencia_operacion, fecha_movimiento, estado, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($movimientos as $m) {
        $stmtMov->execute($m);
    }

    // 10. Solicitudes Web de ejemplo
    $solicitudes = [
        [
            'Valeria Gómez Sánchez', '982341567', 'valeria.gomez@email.com',
            'Universidad Peruana de Ciencias Aplicadas (UPC)', 'Administración', 'Pregrado',
            'Realización Completa de la Tesis', 'IMPULSO2026',
            'Hola, vi la presentación de Impulso Académico y deseo coordinar por interno la Realización Completa de mi Tesis (S/ 2000 - 3000) aplicando el código IMPULSO2026.',
            'NUEVO'
        ],
        [
            'Dr. Hernán Paredes Silva', '998712345', 'hparedes@email.com',
            'Universidad Nacional Federico Villarreal', 'Educación', 'Doctorado',
            'Análisis Estadístico', 'STATS25',
            'Necesito Análisis Estadístico en SPSS/Stata (S/ 400 - 800) y Reducción de Similitud Turnitin (S/ 100 - 150). Quedo atento al WhatsApp.',
            'EN_COORDINACION'
        ]
    ];
    $stmtSol = $pdo->prepare("INSERT INTO solicitudes_web (nombre_completo, telefono, email, universidad, carrera, nivel_academico, servicio_interes, codigo_promo, mensaje, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($solicitudes as $sol) {
        $stmtSol->execute($sol);
    }
}

function recalcularFinanzasCliente(PDO $pdo, int $clienteId): void {
    $stmtCli = $pdo->prepare("SELECT costo_total, descuento_aplicado, monto_final, fecha_entrega_estimada, estado_financiero FROM clientes WHERE id = ?");
    $stmtCli->execute([$clienteId]);
    $cli = $stmtCli->fetch();
    if (!$cli) {
        return;
    }

    $montoFinal = (float)$cli['monto_final'];
    if ($montoFinal <= 0) {
        $montoFinal = max(0, (float)$cli['costo_total'] - (float)$cli['descuento_aplicado']);
    }

    $stmtSum = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) AS total_pagado FROM movimientos_financieros WHERE cliente_id = ? AND tipo = 'INGRESO' AND estado = 'CONFIRMADO'");
    $stmtSum->execute([$clienteId]);
    $totalAbonado = (float)$stmtSum->fetchColumn();

    $saldoPendiente = max(0, $montoFinal - $totalAbonado);

    $nuevoEstado = $cli['estado_financiero'];
    if ($saldoPendiente <= 0.01 && $montoFinal > 0) {
        $nuevoEstado = 'PAGADO_TOTAL';
        $saldoPendiente = 0.00;
    } elseif ($nuevoEstado === 'PAGADO_TOTAL' && $saldoPendiente > 0.01) {
        $nuevoEstado = 'PENDIENTE';
    }

    $stmtUpd = $pdo->prepare("UPDATE clientes SET monto_final = ?, total_abonado = ?, saldo_pendiente = ?, estado_financiero = ? WHERE id = ?");
    $stmtUpd->execute([$montoFinal, $totalAbonado, $saldoPendiente, $nuevoEstado, $clienteId]);
}

function obtenerConfiguracion(PDO $pdo): array {
    $rows = $pdo->query("SELECT clave, valor FROM configuracion_web")->fetchAll();
    $cfg = [];
    foreach ($rows as $r) {
        $cfg[$r['clave']] = $r['valor'];
    }
    return $cfg;
}
