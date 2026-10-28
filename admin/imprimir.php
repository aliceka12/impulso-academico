<?php
/**
 * IMPULSO ACADÉMICO - Vista Limpia de Impresión / PDF
 * Imprime ÚNICAMENTE el reporte deseado con pie de página "Impulso Académico"
 * (Sin URL localhost/... del navegador y sin filas cortadas entre páginas).
 */
require_once dirname(__DIR__) . '/config/db.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();
$cfg = obtenerConfiguracion($pdo);
$tipo = $_GET['tipo'] ?? 'estado_financiero';

$clientes = $pdo->query("SELECT * FROM clientes ORDER BY id DESC")->fetchAll();
$movimientos = $pdo->query("
    SELECT m.*, c.nombres AS cli_nombres, c.apellidos AS cli_apellidos, c.codigo_cliente
    FROM movimientos_financieros m
    LEFT JOIN clientes c ON m.cliente_id = c.id
    ORDER BY m.fecha_movimiento DESC, m.id DESC
")->fetchAll();

$totalCarteraContratos = 0.0;
$totalCuentasPorCobrar = 0.0;
foreach ($clientes as $c) {
    $totalCarteraContratos += (float)$c['monto_final'];
    $totalCuentasPorCobrar += (float)$c['saldo_pendiente'];
}

$totalIngresosCobrados = 0.0;
$totalEgresosOperativos = 0.0;
$egresosPorCategoria = [];
foreach ($movimientos as $m) {
    if ($m['estado'] !== 'CONFIRMADO') continue;
    if ($m['tipo'] === 'INGRESO') {
        $totalIngresosCobrados += (float)$m['monto'];
    } else {
        $totalEgresosOperativos += (float)$m['monto'];
        $cat = $m['categoria'] ?: 'Otros Egresos';
        $egresosPorCategoria[$cat] = ($egresosPorCategoria[$cat] ?? 0) + (float)$m['monto'];
    }
}
$utilidadNetaCaja = $totalIngresosCobrados - $totalEgresosOperativos;
$logoUrl = '../' . ($cfg['logo_url'] ?? 'uploads/img/logo.jpg');

$tituloReporte = match ($tipo) {
    'ingresos_egresos' => 'Reporte Oficial de Ingresos y Egresos',
    'clientes'         => 'Reporte de Cartera de Clientes y Saldos',
    default            => 'Estado Financiero General y Movimientos'
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impulso Académico</title>
    <link rel="icon" type="image/jpeg" href="<?= htmlspecialchars($logoUrl) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Suprimir encabezados y pies de página automáticos del navegador (como localhost/...) */
        @page {
            size: A4 portrait;
            margin: 0mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            background: #031126;
            color: #0F172A;
            padding: 28px;
        }

        /* Barra de controles flotante (No sale en la hoja impresa) */
        .print-toolbar {
            max-width: 980px;
            margin: 0 auto 20px;
            background: #062558;
            border: 1.5px solid #3898FF;
            padding: 14px 20px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            color: #fff;
        }
        .toolbar-btns {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 15px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 0.84rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .btn-gold { background: #F59E0B; color: #031126; }
        .btn-blue { background: #0074F0; color: #fff; }
        .btn-green { background: #10B981; color: #fff; }
        .btn-outline { background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.3); }

        /* Hoja de Reporte A4 */
        .report-sheet {
            max-width: 980px;
            margin: 0 auto;
            background: #FFFFFF;
            color: #0F172A;
            padding: 34px 40px;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }
        .report-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #0074F0;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .report-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .report-brand img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 2px solid #0074F0;
            object-fit: cover;
        }
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 22px;
        }
        .kpi-box {
            border: 1.5px solid #CBD5E1;
            border-radius: 12px;
            padding: 11px 14px;
            background: #F8FAFC !important;
        }
        .kpi-box small {
            display: block;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 4px;
        }
        .kpi-box strong {
            font-size: 1.18rem;
            font-weight: 900;
        }
        h3.sec-title {
            font-size: 0.96rem;
            font-weight: 800;
            color: #031126;
            background: #EFF6FF !important;
            border-left: 4px solid #0074F0;
            padding: 8px 14px;
            margin: 18px 0 10px;
            page-break-after: avoid;
        }
        table.rep-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.82rem;
            margin-bottom: 18px;
        }
        table.rep-table thead {
            display: table-header-group;
        }
        table.rep-table tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        table.rep-table th {
            background: #031126 !important;
            color: #FFFFFF !important;
            text-align: left;
            padding: 9px 12px;
            font-weight: 800;
            border: 1px solid #031126;
        }
        table.rep-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #CBD5E1;
            color: #0F172A;
        }
        .right { text-align: right !important; }
        .ingreso { color: #059669 !important; font-weight: 800; }
        .egreso { color: #DC2626 !important; font-weight: 800; }
        .total-row td {
            background: #EFF6FF !important;
            font-weight: 900;
            color: #031126 !important;
            border-top: 2px solid #0074F0;
        }

        /* Pie de página institucional limpio (Dice únicamente Impulso Académico) */
        .report-custom-footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1.5px solid #CBD5E1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.8rem;
            font-weight: 800;
            color: #031126;
        }

        @media print {
            body {
                background: #FFFFFF !important;
                padding: 0 !important;
            }
            .print-toolbar {
                display: none !important;
            }
            .report-sheet {
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                padding: 14mm 16mm !important;
            }
        }
    </style>
</head>
<body>

    <!-- Barra de acciones (No aparece al imprimir) -->
    <div class="print-toolbar">
        <div>
            <strong><i class="fa-solid fa-file-invoice-dollar" style="color:#FBBF24;"></i> Vista de Impresión y Descarga:</strong>
            <span style="font-size:0.86rem;color:#93C5FD;margin-left:6px;"><?= htmlspecialchars($tituloReporte) ?></span>
        </div>
        <div class="toolbar-btns">
            <a href="imprimir.php?tipo=estado_financiero" class="btn <?= $tipo === 'estado_financiero' ? 'btn-blue' : 'btn-outline' ?>">Estado Financiero</a>
            <a href="imprimir.php?tipo=ingresos_egresos" class="btn <?= $tipo === 'ingresos_egresos' ? 'btn-blue' : 'btn-outline' ?>">Solo Ingresos y Egresos</a>
            <a href="imprimir.php?tipo=clientes" class="btn <?= $tipo === 'clientes' ? 'btn-blue' : 'btn-outline' ?>">Solo Clientes</a>
            <button onclick="window.print()" class="btn btn-gold">
                <i class="fa-solid fa-print"></i> Imprimir / Guardar PDF
            </button>
            <a href="exportar.php?tipo=<?= urlencode($tipo) ?>" class="btn btn-green">
                <i class="fa-solid fa-download"></i> Descargar Excel (.xls)
            </a>
            <a href="index.php" class="btn btn-outline">
                <i class="fa-solid fa-arrow-left"></i> Volver al Panel
            </a>
        </div>
    </div>

    <!-- Documento Limpio para Imprimir -->
    <div class="report-sheet">
        <div class="report-header">
            <div class="report-brand">
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo Impulso Académico">
                <div>
                    <h1 style="font-size:1.35rem;font-weight:900;color:#031126;">IMPULSO ACADÉMICO</h1>
                    <p style="font-size:0.82rem;color:#0074F0;font-weight:700;">Sistema de Gestión y Apoyo a la Investigación</p>
                </div>
            </div>
            <div style="text-align:right;font-size:0.82rem;color:#475569;">
                <strong style="font-size:0.98rem;color:#031126;display:block;"><?= htmlspecialchars($tituloReporte) ?></strong>
                Fecha: <?= date('d/m/Y H:i') ?><br>
                Lima – 2026
            </div>
        </div>

        <!-- KPIs Financieros -->
        <div class="kpi-row">
            <div class="kpi-box">
                <small>Total Contratos</small>
                <strong style="color:#031126;">S/ <?= number_format($totalCarteraContratos, 2) ?></strong>
            </div>
            <div class="kpi-box">
                <small>Ingresos Cobrados</small>
                <strong class="ingreso">S/ <?= number_format($totalIngresosCobrados, 2) ?></strong>
            </div>
            <div class="kpi-box">
                <small>Egresos Operativos</small>
                <strong class="egreso">- S/ <?= number_format($totalEgresosOperativos, 2) ?></strong>
            </div>
            <div class="kpi-box">
                <small>Utilidad Neta en Caja</small>
                <strong style="color:#0074F0;">S/ <?= number_format($utilidadNetaCaja, 2) ?></strong>
            </div>
        </div>

        <?php if ($tipo === 'estado_financiero'): ?>
        <h3 class="sec-title">1. Resumen Ejecutivo del Estado Financiero</h3>
        <table class="rep-table">
            <thead>
                <tr>
                    <th>Rubro del Estado Financiero</th>
                    <th>Detalle / Composición</th>
                    <th class="right">Monto (S/)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>(+) Total Contratos Acordados con Clientes</strong></td>
                    <td>Valor total de las asesorías contratadas (neto de descuentos)</td>
                    <td class="right"><strong>S/ <?= number_format($totalCarteraContratos, 2) ?></strong></td>
                </tr>
                <tr>
                    <td class="ingreso">(+) Ingresos Efectivamente Cobrados</td>
                    <td>Pagos iniciales, abonos por capítulos y liquidaciones recibidas</td>
                    <td class="right ingreso">S/ <?= number_format($totalIngresosCobrados, 2) ?></td>
                </tr>
                <tr>
                    <td class="egreso">(-) Total Egresos y Costos Operativos</td>
                    <td>
                        <?php
                        $partes = [];
                        foreach ($egresosPorCategoria as $cat => $mto) {
                            $partes[] = htmlspecialchars($cat) . ': S/ ' . number_format($mto, 2);
                        }
                        echo $partes ? implode(' • ', $partes) : 'Sin egresos registrados';
                        ?>
                    </td>
                    <td class="right egreso">- S/ <?= number_format($totalEgresosOperativos, 2) ?></td>
                </tr>
                <tr class="total-row">
                    <td>(=) UTILIDAD NETA DISPONIBLE EN CAJA / BANCOS</td>
                    <td>Flujo de caja libre actual (Ingresos Cobrados - Egresos)</td>
                    <td class="right">S/ <?= number_format($utilidadNetaCaja, 2) ?></td>
                </tr>
                <tr>
                    <td><strong>(+) Cuentas por Cobrar (Cuotas Pendientes de Clientes)</strong></td>
                    <td>Saldo pendiente por cobrar conforme se entreguen los capítulos</td>
                    <td class="right"><strong style="color:#D97706;">S/ <?= number_format($totalCuentasPorCobrar, 2) ?></strong></td>
                </tr>
            </tbody>
        </table>
        <?php endif; ?>

        <?php if ($tipo === 'estado_financiero' || $tipo === 'ingresos_egresos'): ?>
        <h3 class="sec-title"><?= $tipo === 'estado_financiero' ? '2. Detalle de Ingresos y Egresos' : 'Libro de Ingresos y Egresos' ?></h3>
        <table class="rep-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Categoría</th>
                    <th>Concepto / Cliente</th>
                    <th>Medio / Operación</th>
                    <th class="right">Monto (S/)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movimientos as $m): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($m['fecha_movimiento'])) ?></td>
                    <td class="<?= $m['tipo'] === 'INGRESO' ? 'ingreso' : 'egreso' ?>"><?= htmlspecialchars($m['tipo']) ?></td>
                    <td><?= htmlspecialchars($m['categoria']) ?></td>
                    <td>
                        <strong><?= htmlspecialchars($m['concepto']) ?></strong>
                        <?php if (!empty($m['cli_nombres'])): ?>
                            <br><small style="color:#475569;">Cliente: <?= htmlspecialchars($m['codigo_cliente'] . ' - ' . $m['cli_nombres'] . ' ' . $m['cli_apellidos']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($m['metodo_pago'] . ($m['referencia_operacion'] ? ' (' . $m['referencia_operacion'] . ')' : '')) ?></td>
                    <td class="right <?= $m['tipo'] === 'INGRESO' ? 'ingreso' : 'egreso' ?>">
                        <?= $m['tipo'] === 'INGRESO' ? '+' : '-' ?> S/ <?= number_format((float)$m['monto'], 2) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="5" class="right">UTILIDAD NETA EN CAJA (INGRESOS - EGRESOS):</td>
                    <td class="right">S/ <?= number_format($utilidadNetaCaja, 2) ?></td>
                </tr>
            </tbody>
        </table>
        <?php endif; ?>

        <?php if ($tipo === 'clientes'): ?>
        <h3 class="sec-title">Cartera de Clientes, Avance de Tesis y Saldos Pendientes</h3>
        <table class="rep-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Universidad / Carrera</th>
                    <th>Servicio / Etapa</th>
                    <th>Avance</th>
                    <th class="right">Total Final</th>
                    <th class="right">Abonado</th>
                    <th class="right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $c): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($c['codigo_cliente']) ?></strong></td>
                    <td><?= htmlspecialchars($c['nombres'] . ' ' . $c['apellidos']) ?><br><small><?= htmlspecialchars($c['telefono']) ?></small></td>
                    <td><?= htmlspecialchars($c['universidad']) ?><br><small><?= htmlspecialchars($c['carrera'] . ' • ' . $c['nivel_academico']) ?></small></td>
                    <td><?= htmlspecialchars($c['servicio_contratado']) ?><br><small><?= htmlspecialchars($c['estado_tesis']) ?></small></td>
                    <td><strong><?= (int)$c['porcentaje_avance'] ?>%</strong></td>
                    <td class="right">S/ <?= number_format((float)$c['monto_final'], 2) ?></td>
                    <td class="right ingreso">S/ <?= number_format((float)$c['total_abonado'], 2) ?></td>
                    <td class="right egreso">S/ <?= number_format((float)$c['saldo_pendiente'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- Pie de página limpio: Solo dice Impulso Académico -->
        <div class="report-custom-footer">
            <span>Impulso Académico</span>
            <span>Sistema de Gestión y Apoyo a la Investigación</span>
        </div>
    </div>

    <?php if (isset($_GET['autoprint'])): ?>
    <script>
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 300);
        });
    </script>
    <?php endif; ?>
</body>
</html>
