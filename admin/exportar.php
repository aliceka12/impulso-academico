<?php
/**
 * IMPULSO ACADÉMICO - Exportador y Descargador de Reportes Financieros (Excel .xls)
 * Permite descargar:
 *  - tipo=estado_financiero (Resumen de Estado Financiero + Ingresos y Egresos)
 *  - tipo=ingresos_egresos (Libro Mayor de Ingresos y Egresos)
 *  - tipo=clientes (Cartera de Clientes, Avance de Tesis y Saldos)
 */
require_once dirname(__DIR__) . '/config/db.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();
$tipo = $_GET['tipo'] ?? 'estado_financiero';
$fechaHoy = date('Y-m-d_Hi');

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

$filename = match ($tipo) {
    'ingresos_egresos' => "Libro_Ingresos_y_Egresos_Impulso_{$fechaHoy}.xls",
    'clientes'         => "Cartera_Clientes_Impulso_{$fechaHoy}.xls",
    default            => "Estado_Financiero_Impulso_Academico_{$fechaHoy}.xls"
};

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF"; // BOM UTF-8 para Excel
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 24px; }
        th, td { border: 1px solid #94A3B8; padding: 8px 12px; }
        .title-banner { background-color: #031126; color: #FFFFFF; font-size: 15pt; font-weight: bold; text-align: center; padding: 12px; }
        .subtitle-banner { background-color: #062558; color: #FBBF24; font-size: 11pt; font-weight: bold; text-align: center; padding: 6px; }
        .th-head { background-color: #0074F0; color: #FFFFFF; font-weight: bold; text-align: left; }
        .row-total { background-color: #DBEAFE; font-weight: bold; color: #031126; }
        .ingreso { color: #059669; font-weight: bold; }
        .egreso { color: #DC2626; font-weight: bold; }
        .right { text-align: right; }
    </style>
</head>
<body>

    <table>
        <tr>
            <td colspan="6" class="title-banner">IMPULSO ACADÉMICO - SISTEMA DE GESTIÓN Y APOYO A LA INVESTIGACIÓN</td>
        </tr>
        <tr>
            <td colspan="6" class="subtitle-banner">
                REPORTE OFICIAL • FECHA DE EMISIÓN: <?= date('d/m/Y H:i') ?> • LIMA - 2026
            </td>
        </tr>
    </table>

    <?php if ($tipo === 'estado_financiero'): ?>
    <!-- 1. TABLA DE ESTADO FINANCIERO -->
    <table>
        <thead>
            <tr>
                <th colspan="3" class="th-head" style="background-color:#031126;">1. RESUMEN EJECUTIVO DEL ESTADO FINANCIERO</th>
            </tr>
            <tr>
                <th class="th-head">Rubro Financiero</th>
                <th class="th-head">Detalle / Composición</th>
                <th class="th-head right">Monto (S/)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>(+) Total Contratos Acordados con Clientes</strong></td>
                <td>Valor total de las asesorías contratadas (neto de descuentos)</td>
                <td class="right">S/ <?= number_format($totalCarteraContratos, 2) ?></td>
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
                    echo $partes ? implode(' | ', $partes) : 'Sin egresos registrados';
                    ?>
                </td>
                <td class="right egreso">- S/ <?= number_format($totalEgresosOperativos, 2) ?></td>
            </tr>
            <tr class="row-total">
                <td>(=) UTILIDAD NETA DISPONIBLE EN CAJA / BANCOS</td>
                <td>Flujo de caja libre actual (Ingresos Cobrados - Egresos)</td>
                <td class="right">S/ <?= number_format($utilidadNetaCaja, 2) ?></td>
            </tr>
            <tr>
                <td><strong>(+) Cuentas por Cobrar (Cuotas Pendientes de Clientes)</strong></td>
                <td>Saldo pendiente por cobrar conforme avance de entregas</td>
                <td class="right"><strong>S/ <?= number_format($totalCuentasPorCobrar, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>

    <?php if ($tipo === 'estado_financiero' || $tipo === 'ingresos_egresos'): ?>
    <!-- 2. TABLA DE INGRESOS Y EGRESOS -->
    <table>
        <thead>
            <tr>
                <th colspan="6" class="th-head" style="background-color:#031126;">DETALLE DE MOVIMIENTOS: INGRESOS Y EGRESOS</th>
            </tr>
            <tr>
                <th class="th-head">Fecha</th>
                <th class="th-head">Tipo</th>
                <th class="th-head">Categoría</th>
                <th class="th-head">Concepto / Cliente</th>
                <th class="th-head">Medio de Pago / N° Operación</th>
                <th class="th-head right">Monto (S/)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movimientos as $m): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($m['fecha_movimiento'])) ?></td>
                <td class="<?= $m['tipo'] === 'INGRESO' ? 'ingreso' : 'egreso' ?>"><?= htmlspecialchars($m['tipo']) ?></td>
                <td><?= htmlspecialchars($m['categoria']) ?></td>
                <td>
                    <?= htmlspecialchars($m['concepto']) ?>
                    <?php if (!empty($m['cli_nombres'])): ?>
                        (<?= htmlspecialchars($m['codigo_cliente'] . ' - ' . $m['cli_nombres'] . ' ' . $m['cli_apellidos']) ?>)
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($m['metodo_pago'] . ($m['referencia_operacion'] ? ' / ' . $m['referencia_operacion'] : '')) ?></td>
                <td class="right <?= $m['tipo'] === 'INGRESO' ? 'ingreso' : 'egreso' ?>">
                    <?= $m['tipo'] === 'INGRESO' ? '' : '-' ?>S/ <?= number_format((float)$m['monto'], 2) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr class="row-total">
                <td colspan="5" class="right">TOTAL INGRESOS COBRADOS:</td>
                <td class="right ingreso">S/ <?= number_format($totalIngresosCobrados, 2) ?></td>
            </tr>
            <tr class="row-total">
                <td colspan="5" class="right">TOTAL EGRESOS OPERATIVOS:</td>
                <td class="right egreso">- S/ <?= number_format($totalEgresosOperativos, 2) ?></td>
            </tr>
            <tr class="row-total">
                <td colspan="5" class="right">UTILIDAD NETA / SALDO EN CAJA:</td>
                <td class="right">S/ <?= number_format($utilidadNetaCaja, 2) ?></td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>

    <?php if ($tipo === 'clientes'): ?>
    <!-- 3. TABLA DE CARTERA DE CLIENTES -->
    <table>
        <thead>
            <tr>
                <th colspan="9" class="th-head" style="background-color:#031126;">CARTERA DE CLIENTES, AVANCE DE TESIS Y ESTADOS DE PAGO</th>
            </tr>
            <tr>
                <th class="th-head">Código</th>
                <th class="th-head">Cliente</th>
                <th class="th-head">Teléfono</th>
                <th class="th-head">Universidad / Carrera</th>
                <th class="th-head">Servicio y Etapa</th>
                <th class="th-head">Avance</th>
                <th class="th-head right">Monto Final</th>
                <th class="th-head right">Total Abonado</th>
                <th class="th-head right">Saldo Pendiente</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clientes as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['codigo_cliente']) ?></td>
                <td><?= htmlspecialchars($c['nombres'] . ' ' . $c['apellidos']) ?></td>
                <td><?= htmlspecialchars($c['telefono']) ?></td>
                <td><?= htmlspecialchars($c['universidad'] . ' - ' . $c['carrera'] . ' (' . $c['nivel_academico'] . ')') ?></td>
                <td><?= htmlspecialchars($c['servicio_contratado'] . ' [' . $c['estado_tesis'] . ']') ?></td>
                <td><?= (int)$c['porcentaje_avance'] ?>%</td>
                <td class="right">S/ <?= number_format((float)$c['monto_final'], 2) ?></td>
                <td class="right ingreso">S/ <?= number_format((float)$c['total_abonado'], 2) ?></td>
                <td class="right egreso">S/ <?= number_format((float)$c['saldo_pendiente'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

</body>
</html>
