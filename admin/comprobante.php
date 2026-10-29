<?php
/**
 * IMPULSO ACADÉMICO - Visor e Impresión de Boletas, Facturas y Cotizaciones
 * El RUC se jala dinámicamente desde configuracion_web ('ruc_empresa') para actualizarse cuando la empresa tenga RUC.
 */
require_once dirname(__DIR__) . '/config/db.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();
$cfg = obtenerConfiguracion($pdo);

$id = (int)($_GET['id'] ?? 1);
$stmt = $pdo->prepare("SELECT * FROM comprobantes_pago WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc) {
    $doc = $pdo->query("SELECT * FROM comprobantes_pago ORDER BY id ASC LIMIT 1")->fetch();
}

$tipoDoc = $doc['tipo_doc'] ?? 'BOLETA';
$tituloComprobante = 'BOLETA DE VENTA';
$colorAcento = '#0074F0';
if ($tipoDoc === 'FACTURA') {
    $tituloComprobante = 'FACTURA COMERCIAL';
    $colorAcento = '#042152';
} elseif ($tipoDoc === 'COTIZACION') {
    $tituloComprobante = 'COTIZACIÓN ACADÉMICA';
    $colorAcento = '#D97706';
}

$rucEmpresa = trim($cfg['ruc_empresa'] ?? 'EN TRÁMITE');
$razonSocial = trim($cfg['razon_social_facturacion'] ?? 'IMPULSO ACADÉMICO - SISTEMA DE GESTIÓN Y APOYO A LA INVESTIGACIÓN');
$direccionFiscal = trim($cfg['direccion_fiscal'] ?? 'Lima – Perú');
$logoUrl = '../' . ($cfg['logo_url'] ?? 'uploads/img/logo.jpg');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impulso Académico</title>
    <link rel="icon" type="image/jpeg" href="<?= htmlspecialchars($logoUrl) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', Arial, sans-serif;
            background: #02122C;
            color: #0F172A;
            padding: 28px 16px;
        }
        .toolbar-no-print {
            max-width: 210mm;
            margin: 0 auto 20px;
            background: #041F4A;
            border: 1.5px solid #0074F0;
            border-radius: 16px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            color: #fff;
            box-shadow: 0 15px 35px rgba(0,0,0,0.45);
        }
        .btn-bar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 0.84rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-print { background: linear-gradient(135deg, #FBBF24, #D97706); color: #02122C; }
        .btn-back { background: rgba(255,255,255,0.12); color: #fff; border: 1px solid rgba(255,255,255,0.22); }

        .sheet-a4 {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 14px;
            padding: 16mm 16mm 14mm 16mm;
            box-shadow: 0 25px 60px rgba(0,0,0,0.55);
        }
        .comp-header {
            display: grid;
            grid-template-columns: 1.45fr 1fr;
            gap: 18px;
            align-items: center;
            padding-bottom: 18px;
            border-bottom: 3px solid #042152;
            margin-bottom: 18px;
        }
        .brand-side {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .brand-logo {
            width: 74px;
            height: 74px;
            border-radius: 14px;
            border: 2.5px solid #0074F0;
            object-fit: cover;
            flex-shrink: 0;
        }
        .ruc-box {
            border: 2.5px solid <?= $colorAcento ?>;
            border-radius: 12px;
            overflow: hidden;
            text-align: center;
        }
        .ruc-top {
            padding: 8px 12px;
            background: #F8FAFC;
            font-weight: 800;
            font-size: 0.85rem;
            color: #042152;
        }
        .ruc-middle {
            padding: 9px 12px;
            background: <?= $colorAcento ?>;
            color: #FFFFFF;
            font-weight: 900;
            font-size: 0.96rem;
            letter-spacing: 0.06em;
        }
        .ruc-bottom {
            padding: 9px 12px;
            background: #FFFFFF;
            font-weight: 900;
            font-size: 1.05rem;
            color: #0F172A;
        }
        .client-box {
            background: #F8FAFC;
            border: 1.5px solid #CBD5E1;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 18px;
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 10px 18px;
            font-size: 0.83rem;
        }
        .client-field strong {
            color: #042152;
            display: inline-block;
            min-width: 125px;
        }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 0.84rem;
        }
        table.items-table th {
            background: #042152;
            color: #FFFFFF;
            padding: 10px 12px;
            text-align: left;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        table.items-table td {
            padding: 12px;
            border-bottom: 1px solid #E2E8F0;
            color: #1E293B;
        }
        .totals-grid {
            display: grid;
            grid-template-columns: 1.25fr 0.95fr;
            gap: 18px;
            align-items: start;
        }
        .payment-info {
            background: #F1F5F9;
            border-left: 4px solid #0074F0;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 0.8rem;
            color: #334155;
            line-height: 1.55;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.86rem;
        }
        .totals-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #E2E8F0;
        }
        .totals-table tr.grand-total td {
            background: #042152;
            color: #FFFFFF;
            font-weight: 900;
            font-size: 1rem;
            border-radius: 6px;
        }
        .doc-footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 2px solid #E2E8F0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.78rem;
            color: #475569;
        }
        @page {
            size: A4 portrait;
            margin: 0mm;
        }
        @media print {
            html, body {
                background: #FFFFFF !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .toolbar-no-print {
                display: none !important;
            }
            .sheet-a4 {
                max-width: 100% !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 14mm 15mm !important;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar-no-print">
        <div style="display:flex;align-items:center;gap:10px;">
            <a href="index.php?tab=documentacion" class="btn-bar btn-back">
                <i class="fa-solid fa-arrow-left"></i> Volver a Documentación
            </a>
            <span style="font-weight:800;font-size:0.9rem;">
                <?= htmlspecialchars($tituloComprobante) ?> • <?= htmlspecialchars($doc['nro_documento']) ?>
            </span>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="button" onclick="window.print();" class="btn-bar btn-print">
                <i class="fa-solid fa-print"></i> Imprimir / Guardar PDF
            </button>
        </div>
    </div>

    <div class="sheet-a4">
        <div class="comp-header">
            <div class="brand-side">
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo Impulso Académico" class="brand-logo">
                <div>
                    <div style="font-size:1.35rem;font-weight:900;color:#042152;line-height:1.15;">IMPULSO ACADÉMICO</div>
                    <div style="font-size:0.78rem;font-weight:700;color:#0074F0;margin-top:2px;"><?= htmlspecialchars($razonSocial) ?></div>
                    <div style="font-size:0.76rem;color:#475569;margin-top:5px;">
                        📍 <?= htmlspecialchars($direccionFiscal) ?> &nbsp;|&nbsp; 📞 WhatsApp: <?= htmlspecialchars($cfg['telefono_visible'] ?? '953 697 434 / 900 439 160') ?><br>
                        ✉️ <?= htmlspecialchars($cfg['correo_contacto'] ?? 'contacto@impulsoacademico.pe') ?>
                    </div>
                </div>
            </div>
            <div class="ruc-box">
                <div class="ruc-top">R.U.C.: <?= htmlspecialchars($rucEmpresa) ?></div>
                <div class="ruc-middle"><?= htmlspecialchars($tituloComprobante) ?></div>
                <div class="ruc-bottom">N° <?= htmlspecialchars($doc['nro_documento']) ?></div>
            </div>
        </div>

        <div class="client-box">
            <div class="client-field"><strong>Señor(es) / Cliente:</strong> <?= htmlspecialchars($doc['cliente_nombre']) ?></div>
            <div class="client-field"><strong>Fecha de Emisión:</strong> <?= date('d/m/Y', strtotime($doc['fecha_emision'])) ?></div>
            <div class="client-field"><strong><?= htmlspecialchars($doc['cliente_doc_tipo'] ?: 'DNI/RUC') ?>:</strong> <?= htmlspecialchars($doc['cliente_doc_numero'] ?: '—') ?></div>
            <div class="client-field"><strong>Celular / Contacto:</strong> <?= htmlspecialchars($doc['cliente_celular'] ?: '—') ?></div>
            <div class="client-field"><strong>Universidad:</strong> <?= htmlspecialchars($doc['universidad'] ?: '—') ?></div>
            <div class="client-field"><strong>Área de Carrera:</strong> <?= htmlspecialchars($doc['area_carrera']) ?> (<?= htmlspecialchars($doc['area_academica']) ?>)</div>
            <div class="client-field"><strong>Cuenta de Pago:</strong> <?= htmlspecialchars($doc['cuenta_pago']) ?></div>
            <div class="client-field"><strong>Estado:</strong> <span style="font-weight:800;color:#059669;"><?= htmlspecialchars($doc['estado_doc']) ?></span></div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:14%;">Código Act.</th>
                    <th style="width:10%;text-align:center;">Cant.</th>
                    <th style="width:46%;">Descripción de Actividad Académica</th>
                    <th style="width:15%;text-align:right;">Valor Unit.</th>
                    <th style="width:15%;text-align:right;">Importe Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight:800;color:#0074F0;"><?= htmlspecialchars($doc['codigo_actividad']) ?></td>
                    <td style="text-align:center;font-weight:700;"><?= (int)$doc['cantidad'] ?></td>
                    <td>
                        <div style="font-weight:800;color:#0F172A;"><?= htmlspecialchars($doc['descripcion']) ?></div>
                        <div style="font-size:0.75rem;color:#64748B;margin-top:2px;">
                            Área de Carrera: <?= htmlspecialchars($doc['area_carrera']) ?> • Nivel / Grado: <?= htmlspecialchars($doc['area_academica']) ?>
                        </div>
                    </td>
                    <td style="text-align:right;font-weight:700;">S/ <?= number_format((float)$doc['precio_unitario'], 2) ?></td>
                    <td style="text-align:right;font-weight:900;color:#042152;">S/ <?= number_format((float)$doc['importe_total'], 2) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="totals-grid">
            <div class="payment-info">
                <strong style="color:#042152;display:block;margin-bottom:4px;">Información de Pago y Observaciones:</strong>
                • <strong>Cuenta Corriente de Pago:</strong> <?= htmlspecialchars($doc['cuenta_pago']) ?><br>
                • <strong>Detalle:</strong> <?= htmlspecialchars($doc['observaciones'] ?: 'Coordinación interna directa con Impulso Académico.') ?><br>
                • <em>Nota: El número de RUC se actualiza automáticamente desde el Panel de Control en cuanto se registre el RUC oficial.</em>
            </div>
            <div>
                <table class="totals-table">
                    <tr>
                        <td style="color:#475569;font-weight:700;">Subtotal / Op. Base:</td>
                        <td style="text-align:right;font-weight:800;">S/ <?= number_format((float)$doc['subtotal'], 2) ?></td>
                    </tr>
                    <tr>
                        <td style="color:#475569;font-weight:700;">I.G.V. (18%):</td>
                        <td style="text-align:right;font-weight:800;">S/ <?= number_format((float)$doc['igv'], 2) ?></td>
                    </tr>
                    <tr class="grand-total">
                        <td>IMPORTE TOTAL:</td>
                        <td style="text-align:right;">S/ <?= number_format((float)$doc['importe_total'], 2) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="doc-footer">
            <div><strong>Impulso Académico</strong> — Sistema de Gestión y Apoyo a la Investigación</div>
            <div>¡Tu éxito es nuestro impulso!</div>
        </div>
    </div>
</body>
</html>
