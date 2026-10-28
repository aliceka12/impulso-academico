<?php
/**
 * IMPULSO ACADÉMICO - Página Independiente: ¡Contáctanos! y Coordinación Interna
 * Sin pasarela de pagos: coordinación directa por interno con nuestros asesores.
 */
$paginaActual = 'contacto.php';
$tituloPagina = '¡Contáctanos! • Presupuesto Personalizado';
require_once __DIR__ . '/includes/header.php';

$serviciosActivos = $pdo->query("SELECT * FROM servicios WHERE estado = 'ACTIVO' ORDER BY orden ASC, id ASC")->fetchAll();
$imgContacto = $seccionesImg['contacto_banner'] ?? [
    'imagen_url' => 'uploads/img/pdf_img_11.jpg',
    'titulo' => '¡Contáctanos!',
    'subtitulo' => '¡Tu éxito es nuestro impulso!'
];

$servicioPreseleccionado = trim($_GET['servicio'] ?? '');
$promoPreseleccionada = trim($_GET['promo'] ?? '');
?>

    <!-- ==================== CABECERA DE PÁGINA ==================== -->
    <section class="page-hero-banner">
        <span class="section-tag"><i class="fa-solid fa-headset"></i> Atención Personalizada</span>
        <h1>¡Contáctanos!</h1>
        <p>Estamos listos para ayudarte a alcanzar tus metas académicas. ¡Tu éxito es nuestro impulso!</p>
    </section>

    <!-- ==================== PÁGINA 10 DEL PDF: CONTACTO Y COORDINACIÓN INTERNA ==================== -->
    <section class="section">
        <div class="container">
            <div class="contact-layout">
                <div class="contact-info-panel reveal visible">
                    <div>
                        <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
                            <img src="<?= htmlspecialchars($imgLogo) ?>" alt="Logo Impulso Académico" style="width:70px;height:70px;border-radius:50%;border:2px solid #0074F0;object-fit:cover;">
                            <div>
                                <h3 style="font-size:1.35rem;font-weight:900;">Impulso <span style="color:#3898FF;">Académico</span></h3>
                                <p style="font-size:0.82rem;color:#9EC3F0;"><?= htmlspecialchars($cfg['subtitulo_empresa'] ?? '') ?></p>
                            </div>
                        </div>

                        <h2 style="font-size:2rem;font-weight:900;line-height:1.2;margin-bottom:10px;">
                            Coordinación Directa con Nuestros Especialistas
                        </h2>
                        <p style="color:#C7DDF9;font-size:0.96rem;margin-bottom:20px;">
                            En <strong>Impulso Académico</strong> no utilizamos pasarelas de cobro en la web: evaluamos tu caso y coordinamos directamente contigo por interno el presupuesto y las facilidades de pago.
                        </p>

                        <div style="border-radius:18px;overflow:hidden;border:2px solid var(--ia-border-bright);margin-bottom:22px;max-height:200px;">
                            <img src="<?= htmlspecialchars($imgContacto['imagen_url']) ?>" alt="Contacto Impulso Académico" style="width:100%;height:200px;object-fit:cover;display:block;">
                        </div>

                        <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:22px;">
                            <div style="display:flex;align-items:center;gap:14px;">
                                <div style="width:46px;height:46px;border-radius:12px;background:rgba(0,116,240,0.3);display:flex;align-items:center;justify-content:center;color:#3898FF;font-size:1.3rem;">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </div>
                                <div>
                                    <small style="color:#9EC3F0;display:block;">Teléfono / WhatsApp Oficiales</small>
                                    <strong style="font-size:1.12rem;color:#fff;">953 697 434 &nbsp;/&nbsp; 900 439 160</strong>
                                </div>
                            </div>

                            <div style="display:flex;align-items:center;gap:14px;">
                                <div style="width:46px;height:46px;border-radius:12px;background:rgba(0,116,240,0.3);display:flex;align-items:center;justify-content:center;color:#3898FF;font-size:1.3rem;">
                                    <i class="fa-solid fa-share-nodes"></i>
                                </div>
                                <div>
                                    <small style="color:#9EC3F0;display:block;">Síguenos en Redes Sociales</small>
                                    <strong style="color:#fff;">Encuéntranos en Facebook, Instagram y TikTok</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <a href="<?= htmlspecialchars($whatsappLink1) ?>" target="_blank" class="btn btn-whatsapp" style="padding:12px;font-size:0.88rem;">
                            <i class="fa-brands fa-whatsapp"></i> 953 697 434
                        </a>
                        <a href="<?= htmlspecialchars($whatsappLink2) ?>" target="_blank" class="btn btn-whatsapp" style="padding:12px;font-size:0.88rem;">
                            <i class="fa-brands fa-whatsapp"></i> 900 439 160
                        </a>
                    </div>
                </div>

                <div class="contact-form-panel reveal visible">
                    <div>
                        <span class="section-tag"><i class="fa-solid fa-file-pen"></i> Presupuesto Personalizado</span>
                        <h3 style="font-size:1.65rem;font-weight:900;color:#fff;margin-bottom:6px;">
                            Solicita tu Asesoría por Interno
                        </h3>
                        <p style="font-size:0.92rem;color:#B8D4F8;margin-bottom:20px;">
                            Completa tus datos y un asesor especialista de tu carrera se comunicará contigo por WhatsApp o llamada.
                        </p>

                        <div id="formFeedback" style="display:none;padding:14px 16px;border-radius:12px;margin-bottom:18px;font-size:0.9rem;"></div>

                        <form id="formSolicitudInterna">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Nombre Completo *</label>
                                    <input type="text" name="nombre_completo" class="form-control" placeholder="Ej. Carlos Mendoza" required>
                                </div>
                                <div class="form-group">
                                    <label>Teléfono / WhatsApp *</label>
                                    <input type="text" name="telefono" class="form-control" placeholder="Ej. 953 697 434" required>
                                </div>
                                <div class="form-group">
                                    <label>Universidad</label>
                                    <input type="text" name="universidad" class="form-control" placeholder="Ej. UNMSM, PUCP, UCV, UPC, USMP...">
                                </div>
                                <div class="form-group">
                                    <label>Área / Carrera</label>
                                    <select name="carrera" class="form-control">
                                        <option value="Administración">Administración</option>
                                        <option value="Contabilidad">Contabilidad</option>
                                        <option value="Economía">Economía</option>
                                        <option value="Educación">Educación</option>
                                        <option value="Ingeniería">Ingeniería</option>
                                        <option value="Derecho">Derecho</option>
                                        <option value="Psicología">Psicología</option>
                                        <option value="Ciencias de la Salud">Ciencias de la Salud</option>
                                        <option value="Otra Especialidad">Otra Especialidad</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Nivel Académico</label>
                                    <select name="nivel_academico" class="form-control">
                                        <option value="Pregrado">Pregrado</option>
                                        <option value="Maestría">Posgrado - Maestría</option>
                                        <option value="Doctorado">Posgrado - Doctorado</option>
                                        <option value="Segunda Especialidad">Segunda Especialidad</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Código Promocional (Si hay campaña activa)</label>
                                    <input type="text" name="codigo_promo" id="inputCodigoPromo" class="form-control" value="<?= htmlspecialchars($promoPreseleccionada) ?>" placeholder="Opcional">
                                </div>
                                <div class="form-group full">
                                    <label>Servicio o Etapa Requerida</label>
                                    <select name="servicio_interes" id="selectServicio" class="form-control">
                                        <option value="Realización Completa de la Tesis" <?= $servicioPreseleccionado === 'Realización Completa de la Tesis' ? 'selected' : '' ?>>Realización Completa de la Tesis (S/ 2000 – 3000)</option>
                                        <option value="Asesoría por Capítulo en Horas" <?= $servicioPreseleccionado === 'Asesoría por Capítulo en Horas' ? 'selected' : '' ?>>Asesoría por Capítulo en Horas (S/ 30 – 50)</option>
                                        <?php foreach ($serviciosActivos as $s): ?>
                                        <option value="<?= htmlspecialchars($s['titulo']) ?>" <?= $servicioPreseleccionado === $s['titulo'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($s['titulo']) ?> (<?= htmlspecialchars($s['tarifa_referencial']) ?>)
                                        </option>
                                        <?php endforeach; ?>
                                        <option value="Levantamiento de Observaciones" <?= $servicioPreseleccionado === 'Levantamiento de Observaciones' ? 'selected' : '' ?>>Levantamiento de Observaciones (S/ 300 – 600)</option>
                                        <option value="Realización de Diapositivas de Presentación Profesionales" <?= $servicioPreseleccionado === 'Realización de Diapositivas de Presentación Profesionales' ? 'selected' : '' ?>>Diapositivas de Presentación Profesionales (S/ 250)</option>
                                        <option value="Realización de Diapositivas de Presentación Básicas" <?= $servicioPreseleccionado === 'Realización de Diapositivas de Presentación Básicas' ? 'selected' : '' ?>>Diapositivas de Presentación Básicas (S/ 100)</option>
                                    </select>
                                </div>
                                <div class="form-group full">
                                    <label>Cuéntanos sobre tu investigación o requerimiento</label>
                                    <textarea name="mensaje" id="inputMensaje" rows="3" class="form-control" placeholder="¿En qué etapa te encuentras y para cuándo necesitas presentar tu avance?"></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:1rem;">
                                <i class="fa-solid fa-paper-plane"></i> Enviar Solicitud para Coordinación Interna
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
