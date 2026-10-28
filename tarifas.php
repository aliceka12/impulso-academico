<?php
/**
 * IMPULSO ACADÉMICO - Página Independiente: Tarifas de Nuestros Servicios
 * Tabla oficial de inversión referencial desde la Base de Datos (Sin pasarela de pagos).
 */
$paginaActual = 'tarifas.php';
$tituloPagina = 'Tarifas de Nuestros Servicios';
require_once __DIR__ . '/includes/header.php';

$tarifasActivas = $pdo->query("SELECT * FROM tarifas_servicios WHERE estado = 'ACTIVO' ORDER BY orden ASC, id ASC")->fetchAll();
?>

    <!-- ==================== CABECERA DE PÁGINA ==================== -->
    <section class="page-hero-banner">
        <span class="section-tag"><i class="fa-solid fa-coins"></i> Planes Adaptados a tus Necesidades</span>
        <h1>Tarifas de Nuestros Servicios</h1>
        <p>Inversión en tu futuro académico con planes adaptados a tus necesidades. Contacta con nosotros para un presupuesto personalizado.</p>
    </section>

    <!-- ==================== TABLA OFICIAL DE TARIFAS (PÁGINA 9 DEL PDF) ==================== -->
    <section class="section">
        <div class="container">
            <div class="reveal visible" style="max-width:1000px;margin:0 auto;background:var(--ia-card-bg);backdrop-filter:blur(16px);border-radius:26px;overflow:hidden;border:2px solid var(--ia-border-bright);box-shadow:var(--shadow-lg);">
                <div style="padding:24px 30px;background:rgba(2,18,44,0.88);border-bottom:1.5px solid var(--ia-border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;">
                    <div style="color:#fff;font-weight:800;font-size:1.15rem;">
                        <i class="fa-solid fa-table-list" style="color:#3898FF;"></i> Tarifas Referenciales de Nuestros Servicios (S/)
                    </div>
                    <span style="background:rgba(0,116,240,0.28);border:1px solid #3898FF;color:#85C1FF;padding:6px 16px;border-radius:999px;font-size:0.82rem;font-weight:800;">
                        Coordinación Directa por Interno (Sin Pasarela de Pagos)
                    </span>
                </div>

                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;color:#fff;">
                        <thead>
                            <tr style="background:rgba(0,116,240,0.24);text-align:left;">
                                <th style="padding:18px 28px;font-size:0.92rem;color:#85C1FF;">Servicio Académico</th>
                                <th style="padding:18px 28px;font-size:0.92rem;color:#85C1FF;">Inversión Referencial (S/)</th>
                                <th style="padding:18px 28px;font-size:0.92rem;color:#85C1FF;text-align:right;">Coordinación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tarifasActivas as $t): ?>
                            <tr style="border-bottom:1px solid rgba(56,152,255,0.18);transition:background 0.2s;">
                                <td style="padding:18px 28px;">
                                    <strong style="display:block;font-size:1.05rem;color:#fff;"><?= htmlspecialchars($t['servicio_nombre']) ?></strong>
                                    <?php if (!empty($t['detalle'])): ?>
                                    <small style="color:#9EC3F0;"><?= htmlspecialchars($t['detalle']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:18px 28px;font-weight:900;font-size:1.2rem;color:#3898FF;white-space:nowrap;">
                                    <?= htmlspecialchars($t['rango_inversion']) ?>
                                </td>
                                <td style="padding:18px 28px;text-align:right;">
                                    <a href="contacto.php?servicio=<?= urlencode($t['servicio_nombre']) ?>" class="btn btn-primary" style="padding:9px 18px;font-size:0.84rem;">
                                        <i class="fa-solid fa-paper-plane"></i> Solicitar Presupuesto
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="padding:22px 30px;background:rgba(2,18,44,0.85);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;color:#C7DDF9;font-size:0.94rem;">
                    <span>
                        <i class="fa-solid fa-handshake-angle" style="color:#3898FF;"></i>
                        <strong>Contacta con nosotros para un presupuesto personalizado.</strong> Coordinamos facilidades de pago en cuotas según el avance de tu tesis.
                    </span>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <a href="<?= htmlspecialchars($whatsappLink1) ?>" target="_blank" class="btn btn-whatsapp" style="padding:10px 18px;font-size:0.86rem;">
                            <i class="fa-brands fa-whatsapp"></i> WhatsApp: 953 697 434
                        </a>
                        <a href="contacto.php" class="btn btn-outline-light" style="padding:10px 18px;font-size:0.86rem;">
                            Formulario de Contacto
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
