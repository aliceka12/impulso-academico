<?php
/**
 * IMPULSO ACADÉMICO - Sistema de Gestión y Apoyo a la Investigación
 * Panel de Control: Clientes, Estados Financieros, Campañas de Descuento, Videos e Imágenes en BD.
 */
require_once dirname(__DIR__) . '/config/db.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();
$cfg = obtenerConfiguracion($pdo);
$activeTab = $_GET['tab'] ?? 'dashboard';

// 1. Consultas para KPIs y Estados Financieros
$clientes = $pdo->query("SELECT * FROM clientes ORDER BY id DESC")->fetchAll();
$movimientos = $pdo->query("
    SELECT m.*, c.nombres AS cli_nombres, c.apellidos AS cli_apellidos, c.codigo_cliente
    FROM movimientos_financieros m
    LEFT JOIN clientes c ON m.cliente_id = c.id
    ORDER BY m.fecha_movimiento DESC, m.id DESC
")->fetchAll();

$campanas = $pdo->query("SELECT * FROM campanas_descuento ORDER BY id DESC")->fetchAll();
$videos = $pdo->query("SELECT * FROM videos_promocionales ORDER BY destacado DESC, id DESC")->fetchAll();
$imagenesWeb = $pdo->query("SELECT * FROM galeria_imagenes ORDER BY clave_seccion ASC, orden ASC")->fetchAll();
$serviciosWeb = $pdo->query("SELECT * FROM servicios ORDER BY orden ASC")->fetchAll();
$solicitudes = $pdo->query("SELECT * FROM solicitudes_web ORDER BY id DESC")->fetchAll();

// Cálculos de Estados Financieros
$totalCarteraContratos = 0.0;
$totalCuentasPorCobrar = 0.0;
$conteoEstadosFin = ['AL_DIA' => 0, 'PAGADO_TOTAL' => 0, 'PENDIENTE' => 0, 'VENCIDO' => 0];

foreach ($clientes as $c) {
    $totalCarteraContratos += (float)$c['monto_final'];
    $totalCuentasPorCobrar += (float)$c['saldo_pendiente'];
    $ef = $c['estado_financiero'] ?? 'AL_DIA';
    if (isset($conteoEstadosFin[$ef])) {
        $conteoEstadosFin[$ef]++;
    }
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
$margenOperativo = $totalIngresosCobrados > 0 ? round(($utilidadNetaCaja / $totalIngresosCobrados) * 100, 1) : 0;

$solicitudesNuevas = 0;
foreach ($solicitudes as $s) {
    if ($s['estado'] === 'NUEVO') $solicitudesNuevas++;
}

$logoUrl = '../' . ($cfg['logo_url'] ?? 'uploads/img/logo.jpg');
$flashMsg = $_SESSION['flash_msg'] ?? null;
$flashErr = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_msg'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control | Impulso Académico</title>
    <link rel="icon" type="image/jpeg" href="<?= htmlspecialchars($logoUrl) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --ia-midnight: #02122C;
            --ia-navy: #041F4A;
            --ia-card: #062558;
            --ia-blue: #0074F0;
            --ia-sky: #3898FF;
            --ia-ice: rgba(0, 116, 240, 0.18);
            --ia-bg: #02122C;
            --ia-border: rgba(56, 152, 255, 0.28);
            --ia-success: #10B981;
            --ia-warning: #F59E0B;
            --ia-danger: #EF4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: radial-gradient(circle at 80% 15%, rgba(0,116,240,0.22), transparent 45%), #02122C;
            color: #EAF4FF;
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #010D21 0%, #031A40 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            border-right: 2px solid var(--ia-blue);
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
        }
        .sidebar-brand {
            padding: 22px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(56,152,255,0.22);
        }
        .sidebar-brand img {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            border: 2px solid var(--ia-blue);
            object-fit: cover;
        }
        .sidebar-nav {
            padding: 18px 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
            overflow-y: auto;
        }
        .nav-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            border-radius: 12px;
            color: #C7DDF9;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            border: 1px solid transparent;
            background: transparent;
            cursor: pointer;
            width: 100%;
            text-align: left;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        .nav-btn .left {
            display: flex;
            align-items: center;
            gap: 11px;
        }
        .nav-btn:hover {
            background: rgba(0,116,240,0.22);
            color: #fff;
        }
        .nav-btn.active {
            background: linear-gradient(135deg, #0074F0 0%, #0051B3 100%);
            color: #fff;
            border-color: var(--ia-sky);
            box-shadow: 0 8px 20px rgba(0,116,240,0.35);
        }
        .badge-count {
            background: rgba(255,255,255,0.18);
            color: #fff;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 800;
        }
        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(56,152,255,0.22);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        /* Main Content */
        .main-wrapper {
            margin-left: 280px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .topbar {
            background: rgba(3, 24, 58, 0.94);
            backdrop-filter: blur(12px);
            padding: 16px 32px;
            border-bottom: 1px solid var(--ia-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 90;
        }
        .content-area {
            padding: 28px 32px 60px;
        }
        .tab-pane {
            display: none;
        }
        .tab-pane.active {
            display: block;
        }
        /* KPI Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 26px;
        }
        .kpi-card {
            background: rgba(6, 36, 86, 0.85);
            padding: 22px;
            border-radius: 18px;
            border: 1.5px solid var(--ia-border);
            box-shadow: 0 10px 26px rgba(0,0,0,0.35);
            position: relative;
            overflow: hidden;
            color: #fff;
        }
        .kpi-card.highlight {
            background: linear-gradient(135deg, #0051B3 0%, #042152 100%);
            color: #fff;
            border-color: var(--ia-sky);
        }
        .kpi-label {
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #9EC3F0;
            margin-bottom: 8px;
            display: block;
        }
        .kpi-card.highlight .kpi-label {
            color: #85C1FF;
        }
        .kpi-value {
            font-size: 1.75rem;
            font-weight: 900;
            line-height: 1.1;
            color: #fff;
        }
        .kpi-sub {
            font-size: 0.78rem;
            margin-top: 6px;
            color: #B8D4F8;
        }
        .kpi-card.highlight .kpi-sub {
            color: #DCEBFF;
        }
        /* Panels & Tables */
        .panel-card {
            background: rgba(5, 31, 74, 0.86);
            border-radius: 20px;
            border: 1.5px solid var(--ia-border);
            box-shadow: 0 12px 30px rgba(0,0,0,0.4);
            margin-bottom: 26px;
            overflow: hidden;
            color: #EAF4FF;
        }
        .panel-header {
            padding: 18px 24px;
            background: rgba(2, 18, 44, 0.78);
            border-bottom: 1px solid var(--ia-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .panel-header h3 {
            font-size: 1.12rem;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .panel-body {
            padding: 24px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        table.admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
            color: #EAF4FF;
        }
        table.admin-table th {
            background: rgba(0, 116, 240, 0.22);
            color: #85C1FF;
            text-align: left;
            padding: 13px 16px;
            font-weight: 800;
            border-bottom: 1px solid var(--ia-border);
            white-space: nowrap;
        }
        table.admin-table td {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(56, 152, 255, 0.15);
            vertical-align: middle;
        }
        table.admin-table tr:hover td {
            background: rgba(0, 116, 240, 0.12);
        }
        /* Badges */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 800;
        }
        .pill-success { background: rgba(16, 185, 129, 0.22); color: #34D399; border: 1px solid rgba(16,185,129,0.4); }
        .pill-warning { background: rgba(245, 158, 11, 0.22); color: #FBBF24; border: 1px solid rgba(245,158,11,0.4); }
        .pill-danger { background: rgba(239, 68, 68, 0.22); color: #F87171; border: 1px solid rgba(239,68,68,0.4); }
        .pill-blue { background: rgba(0, 116, 240, 0.25); color: #7CC2FF; border: 1px solid rgba(56,152,255,0.45); }
        /* Forms & Buttons */
        .form-grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
        }
        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 800;
            color: #85C1FF;
            margin-bottom: 5px;
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            background: rgba(2, 18, 44, 0.8);
            color: #fff;
            border: 1.5px solid var(--ia-border);
            border-radius: 10px;
            font-size: 0.88rem;
            font-family: inherit;
        }
        .form-control option {
            background: #031A40;
            color: #fff;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--ia-sky);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 16px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.84rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-primary { background: var(--ia-blue); color: #fff; }
        .btn-primary:hover { background: #005EC4; }
        .btn-navy { background: rgba(0, 116, 240, 0.25); color: #fff; border: 1px solid var(--ia-sky); }
        .btn-success { background: var(--ia-success); color: #fff; }
        .btn-danger { background: rgba(239, 68, 68, 0.25); color: #FCA5A5; border: 1px solid rgba(239,68,68,0.4); }
        .btn-danger:hover { background: rgba(239, 68, 68, 0.4); }
        .btn-sm { padding: 6px 11px; font-size: 0.78rem; }
        /* Progress bar */
        .progress-track {
            width: 110px;
            height: 8px;
            background: rgba(255,255,255,0.12);
            border-radius: 999px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0074F0, #3898FF);
            border-radius: 999px;
        }
        /* Modal */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(1, 10, 26, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
        }
        .modal-backdrop.open {
            display: flex;
        }
        .modal-box {
            background: #041F4A;
            color: #fff;
            width: 100%;
            max-width: 680px;
            border-radius: 22px;
            border: 2px solid var(--ia-sky);
            max-height: 90vh;
            overflow-y: auto;
        }
        @media (max-width: 960px) {
            .sidebar { position: static; width: 100%; border-right: none; border-bottom: 2px solid var(--ia-blue); }
            .sidebar-nav { flex-direction: row; flex-wrap: wrap; padding: 10px; }
            .sidebar-nav .nav-btn { width: auto; flex: 1 1 140px; padding: 9px 12px; font-size: 0.82rem; }
            body { flex-direction: column; }
            .main-wrapper { margin-left: 0; width: 100%; }
            .topbar { padding: 14px 16px; flex-wrap: wrap; gap: 10px; }
            .content-area { padding: 18px 14px 40px; }
            .charts-grid-admin { grid-template-columns: 1fr !important; }
            .inline-admin-form { grid-template-columns: 1fr !important; }
        }
        @media print {
            .sidebar, .topbar, .charts-grid-admin, form, .btn, .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; display: block !important; }
            .main-wrapper { margin-left: 0 !important; width: 100% !important; }
            .content-area { padding: 0 !important; }
            .panel-card, .kpi-card { background: #fff !important; color: #000 !important; border: 1px solid #94A3B8 !important; box-shadow: none !important; }
            .panel-header { background: #F1F5F9 !important; color: #000 !important; }
            .panel-header h3, .kpi-value, .kpi-label, table.admin-table, table.admin-table th, table.admin-table td { color: #000 !important; }
        }
    </style>
</head>
<body>

    <!-- ==================== SIDEBAR ==================== -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo Impulso Académico">
            <div>
                <div style="font-weight:900;font-size:1.1rem;line-height:1.1;">Impulso <span style="color:#3898FF;">Académico</span></div>
                <small style="color:#85C1FF;font-size:0.72rem;">Sistema de Control Interno</small>
            </div>
        </div>

        <nav class="sidebar-nav">
            <button class="nav-btn <?= $activeTab === 'dashboard' ? 'active' : '' ?>" onclick="switchTab('dashboard')">
                <span class="left"><i class="fa-solid fa-chart-pie"></i> Estados Financieros</span>
            </button>
            <button class="nav-btn <?= $activeTab === 'clientes' ? 'active' : '' ?>" onclick="switchTab('clientes')">
                <span class="left"><i class="fa-solid fa-user-graduate"></i> Control de Clientes</span>
                <span class="badge-count"><?= count($clientes) ?></span>
            </button>
            <button class="nav-btn <?= $activeTab === 'finanzas' ? 'active' : '' ?>" onclick="switchTab('finanzas')">
                <span class="left"><i class="fa-solid fa-money-bill-transfer"></i> Ingresos y Egresos</span>
                <span class="badge-count"><?= count($movimientos) ?></span>
            </button>
            <button class="nav-btn <?= $activeTab === 'campanas' ? 'active' : '' ?>" onclick="switchTab('campanas')">
                <span class="left"><i class="fa-solid fa-tags"></i> Campañas Descuento</span>
                <span class="badge-count"><?= count($campanas) ?></span>
            </button>
            <button class="nav-btn <?= $activeTab === 'videos' ? 'active' : '' ?>" onclick="switchTab('videos')">
                <span class="left"><i class="fa-solid fa-video"></i> Videos Promocionales</span>
                <span class="badge-count"><?= count($videos) ?></span>
            </button>
            <button class="nav-btn <?= $activeTab === 'imagenes' ? 'active' : '' ?>" onclick="switchTab('imagenes')">
                <span class="left"><i class="fa-solid fa-images"></i> Imágenes y Servicios BD</span>
                <span class="badge-count"><?= count($imagenesWeb) ?></span>
            </button>
            <button class="nav-btn <?= $activeTab === 'solicitudes' ? 'active' : '' ?>" onclick="switchTab('solicitudes')">
                <span class="left"><i class="fa-solid fa-inbox"></i> Solicitudes Web</span>
                <?php if ($solicitudesNuevas > 0): ?>
                <span class="badge-count" style="background:#EF4444;"><?= $solicitudesNuevas ?></span>
                <?php endif; ?>
            </button>
        </nav>

        <div class="sidebar-footer">
            <a href="../index.php" target="_blank" class="btn btn-primary" style="width:100%;">
                <i class="fa-solid fa-globe"></i> Ver Página Pública en Vivo
            </a>
            <a href="login.php?logout=1" class="btn" style="width:100%;background:rgba(255,255,255,0.08);color:#FCA5A5;">
                <i class="fa-solid fa-power-off"></i> Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- ==================== MAIN CONTENT ==================== -->
    <div class="main-wrapper">
        <header class="topbar">
            <div>
                <h2 style="font-size:1.25rem;font-weight:900;color:#fff;">
                    Panel de Control • Impulso Académico (Lima – 2026)
                </h2>
                <small style="color:#9EC3F0;">Control integral de clientes, estados financieros, videos promocionales, imágenes y campañas de descuento</small>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <a href="imprimir.php?tipo=estado_financiero&autoprint=1" class="btn" style="background:#F59E0B;color:#031126;font-weight:900;">
                    <i class="fa-solid fa-print"></i> Imprimir Reporte
                </a>
                <a href="exportar.php?tipo=estado_financiero" class="btn btn-success">
                    <i class="fa-solid fa-download"></i> Descargar Excel (.xls)
                </a>
                <a href="../index.php" class="btn btn-navy">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Abrir Web
                </a>
            </div>
        </header>

        <main class="content-area">
            <?php if ($flashMsg): ?>
            <div style="background:#ECFDF5;border:1.5px solid #10B981;color:#065F46;padding:14px 18px;border-radius:14px;margin-bottom:20px;font-weight:700;">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($flashMsg) ?>
            </div>
            <?php endif; ?>
            <?php if ($flashErr): ?>
            <div style="background:#FEF2F2;border:1.5px solid #EF4444;color:#991B1B;padding:14px 18px;border-radius:14px;margin-bottom:20px;font-weight:700;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($flashErr) ?>
            </div>
            <?php endif; ?>

            <!-- =================================================================
                 TAB 1: DASHBOARD Y ESTADOS FINANCIEROS
                 ================================================================= -->
            <section id="tab-dashboard" class="tab-pane <?= $activeTab === 'dashboard' ? 'active' : '' ?>">
                <div class="kpi-grid">
                    <div class="kpi-card highlight">
                        <span class="kpi-label"><i class="fa-solid fa-scale-balanced"></i> Utilidad Neta / Flujo de Caja</span>
                        <div class="kpi-value" style="color:#3898FF;">S/ <?= number_format($utilidadNetaCaja, 2) ?></div>
                        <div class="kpi-sub">Margen operativo: <strong><?= $margenOperativo ?>%</strong> (Ingresos - Egresos)</div>
                    </div>
                    <div class="kpi-card">
                        <span class="kpi-label" style="color:#10B981;"><i class="fa-solid fa-arrow-trend-up"></i> Ingresos Cobrados</span>
                        <div class="kpi-value" style="color:#059669;">S/ <?= number_format($totalIngresosCobrados, 2) ?></div>
                        <div class="kpi-sub">Abonos y cuotas confirmadas en caja/bancos</div>
                    </div>
                    <div class="kpi-card">
                        <span class="kpi-label" style="color:#F59E0B;"><i class="fa-solid fa-hand-holding-dollar"></i> Cuentas por Cobrar</span>
                        <div class="kpi-value" style="color:#D97706;">S/ <?= number_format($totalCuentasPorCobrar, 2) ?></div>
                        <div class="kpi-sub">Saldo pendiente de clientes activos</div>
                    </div>
                    <div class="kpi-card">
                        <span class="kpi-label" style="color:#EF4444;"><i class="fa-solid fa-arrow-trend-down"></i> Egresos Operativos</span>
                        <div class="kpi-value" style="color:#DC2626;">S/ <?= number_format($totalEgresosOperativos, 2) ?></div>
                        <div class="kpi-sub">Honorarios asesores, Turnitin, SPSS y Ads</div>
                    </div>
                    <div class="kpi-card">
                        <span class="kpi-label"><i class="fa-solid fa-file-contract"></i> Total Cartera Contratos</span>
                        <div class="kpi-value" style="color:var(--ia-midnight);">S/ <?= number_format($totalCarteraContratos, 2) ?></div>
                        <div class="kpi-sub"><?= count($clientes) ?> tesis en gestión activa</div>
                    </div>
                </div>

                <!-- Gráficos Financieros y Estado de Resultados -->
                <div class="charts-grid-admin" style="display:grid;grid-template-columns:1.35fr 0.65fr;gap:22px;margin-bottom:26px;">
                    <div class="panel-card" style="margin-bottom:0;">
                        <div class="panel-header">
                            <h3><i class="fa-solid fa-chart-column" style="color:#0074F0;"></i> Balance General y Estado de Resultados (S/)</h3>
                            <button class="btn btn-primary btn-sm" onclick="switchTab('finanzas')">
                                <i class="fa-solid fa-plus"></i> Registrar Ingreso / Egreso
                            </button>
                        </div>
                        <div class="panel-body">
                            <canvas id="chartFinanzas" height="125"></canvas>
                        </div>
                    </div>

                    <div class="panel-card" style="margin-bottom:0;">
                        <div class="panel-header">
                            <h3><i class="fa-solid fa-chart-pie" style="color:#0074F0;"></i> Estado de Pago de Clientes</h3>
                        </div>
                        <div class="panel-body">
                            <canvas id="chartClientesEstado" height="180"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tabla Resumen de Estado Financiero / Estado de Resultados -->
                <div class="panel-card">
                    <div class="panel-header">
                        <h3><i class="fa-solid fa-file-invoice-dollar" style="color:#0074F0;"></i> Resumen Ejecutivo del Estado Financiero • Impulso Académico</h3>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <a href="imprimir.php?tipo=estado_financiero&autoprint=1" class="btn btn-sm" style="background:#F59E0B;color:#031126;font-weight:900;">
                                <i class="fa-solid fa-print"></i> Imprimir Estado Financiero
                            </a>
                            <a href="exportar.php?tipo=estado_financiero" class="btn btn-success btn-sm">
                                <i class="fa-solid fa-download"></i> Descargar Estado Financiero (.xls)
                            </a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Rubro del Estado Financiero</th>
                                        <th>Detalle / Composición</th>
                                        <th style="text-align:right;">Monto (S/)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>(+) Total Contratos Acordados con Clientes</strong></td>
                                        <td>Valor total de las asesorías contratadas (neto de descuentos)</td>
                                        <td style="text-align:right;font-weight:800;">S/ <?= number_format($totalCarteraContratos, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong style="color:#059669;">(+) Ingresos Efectivamente Cobrados</strong></td>
                                        <td>Pagos iniciales, abonos por capítulos y liquidaciones recibidas</td>
                                        <td style="text-align:right;font-weight:800;color:#059669;">S/ <?= number_format($totalIngresosCobrados, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong style="color:#DC2626;">(-) Total Egresos y Costos Operativos</strong></td>
                                        <td>
                                            <?php
                                            $partesEg = [];
                                            foreach ($egresosPorCategoria as $cat => $mto) {
                                                $partesEg[] = htmlspecialchars($cat) . ': S/ ' . number_format($mto, 2);
                                            }
                                            echo $partesEg ? implode(' • ', $partesEg) : 'Sin egresos registrados';
                                            ?>
                                        </td>
                                        <td style="text-align:right;font-weight:800;color:#DC2626;">- S/ <?= number_format($totalEgresosOperativos, 2) ?></td>
                                    </tr>
                                    <tr style="background:rgba(0,116,240,0.25);">
                                        <td><strong style="color:#fff;font-size:1rem;">(=) UTILIDAD NETA DISPONIBLE EN CAJA / BANCOS</strong></td>
                                        <td><strong>Flujo de caja libre actual (Ingresos Cobrados - Egresos)</strong></td>
                                        <td style="text-align:right;font-weight:900;font-size:1.1rem;color:#3898FF;">S/ <?= number_format($utilidadNetaCaja, 2) ?></td>
                                    </tr>
                                    <tr style="background:rgba(245,158,11,0.16);">
                                        <td><strong style="color:#FBBF24;">(+) Cuentas por Cobrar (Cuotas Pendientes de Clientes)</strong></td>
                                        <td>Liquidez proyectada por cobrar conforme se entreguen los capítulos restantes</td>
                                        <td style="text-align:right;font-weight:900;color:#FBBF24;">S/ <?= number_format($totalCuentasPorCobrar, 2) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =================================================================
                 TAB 2: CONTROL DE CLIENTES Y PROYECTOS DE TESIS
                 ================================================================= -->
            <section id="tab-clientes" class="tab-pane <?= $activeTab === 'clientes' ? 'active' : '' ?>">
                <!-- Formulario para Registrar Nuevo Cliente -->
                <div class="panel-card">
                    <div class="panel-header">
                        <h3><i class="fa-solid fa-user-plus" style="color:#0074F0;"></i> Registrar Nuevo Cliente y Proyecto de Tesis</h3>
                    </div>
                    <div class="panel-body">
                        <form action="acciones.php" method="POST">
                            <input type="hidden" name="accion" value="crear_cliente">
                            <input type="hidden" name="tab_retorno" value="clientes">
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>Nombres *</label>
                                    <input type="text" name="nombres" class="form-control" placeholder="Ej. Luis Alberto" required>
                                </div>
                                <div class="form-group">
                                    <label>Apellidos *</label>
                                    <input type="text" name="apellidos" class="form-control" placeholder="Ej. Huamán Soto" required>
                                </div>
                                <div class="form-group">
                                    <label>Celular / WhatsApp *</label>
                                    <input type="text" name="telefono" class="form-control" placeholder="Ej. 987654321" required>
                                </div>
                                <div class="form-group">
                                    <label>DNI / Documento</label>
                                    <input type="text" name="dni" class="form-control" placeholder="8 dígitos">
                                </div>
                                <div class="form-group">
                                    <label>Universidad *</label>
                                    <input type="text" name="universidad" class="form-control" placeholder="Ej. UNMSM, UCV, PUCP..." required>
                                </div>
                                <div class="form-group">
                                    <label>Área / Carrera *</label>
                                    <select name="carrera" class="form-control">
                                        <option value="Administración">Administración</option>
                                        <option value="Contabilidad">Contabilidad</option>
                                        <option value="Economía">Economía</option>
                                        <option value="Educación">Educación</option>
                                        <option value="Ingeniería">Ingeniería</option>
                                        <option value="Derecho">Derecho</option>
                                        <option value="Psicología">Psicología</option>
                                        <option value="Ciencias de la Salud">Ciencias de la Salud</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Nivel Académico</label>
                                    <select name="nivel_academico" class="form-control">
                                        <option value="Pregrado">Pregrado</option>
                                        <option value="Maestría">Maestría</option>
                                        <option value="Doctorado">Doctorado</option>
                                        <option value="Segunda Especialidad">Segunda Especialidad</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Servicio Contratado</label>
                                    <select name="servicio_contratado" class="form-control">
                                        <option value="Realización Completa de la Tesis">Realización Completa de la Tesis (S/ 2000 - 3000)</option>
                                        <option value="Asesoría por Capítulo en Horas">Asesoría por Capítulo en Horas (S/ 30 - 50)</option>
                                        <option value="Análisis Estadístico">Análisis Estadístico (S/ 400 - 800)</option>
                                        <option value="Levantamiento de Observaciones">Levantamiento de Observaciones (S/ 300 - 600)</option>
                                        <option value="Preparación para Sustentación">Preparación para Sustentación (S/ 200 - 300)</option>
                                        <option value="Reducción de Similitud - Turnitin">Reducción de Similitud - Turnitin (S/ 100 - 150)</option>
                                        <option value="Realización de Diapositivas Profesionales">Diapositivas Profesionales (S/ 250)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Asesor Asignado</label>
                                    <input type="text" name="asesor_asignado" class="form-control" placeholder="Ej. Dr. Roberto Salazar">
                                </div>
                                <div class="form-group">
                                    <label>Etapa Actual de Tesis</label>
                                    <select name="estado_tesis" class="form-control">
                                        <option value="Diagnóstico Inicial">1. Diagnóstico Inicial</option>
                                        <option value="Plan Personalizado">2. Plan Personalizado</option>
                                        <option value="Marco Teórico">3. Marco Teórico</option>
                                        <option value="Análisis Estadístico">4. Análisis Estadístico</option>
                                        <option value="Redacción APA 7 / Turnitin">5. Redacción APA 7 / Turnitin</option>
                                        <option value="Levantando Observaciones">6. Levantando Observaciones</option>
                                        <option value="Listo para Sustentar">7. Listo para Sustentar</option>
                                        <option value="Sustentado / Aprobado">8. Sustentado / Aprobado</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>% de Avance Académico</label>
                                    <input type="number" name="porcentaje_avance" class="form-control" value="15" min="0" max="100">
                                </div>
                                <div class="form-group">
                                    <label>Fecha Estimada de Entrega</label>
                                    <input type="date" name="fecha_entrega_estimada" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Costo Total Acordado (S/) *</label>
                                    <input type="number" step="0.01" name="costo_total" class="form-control" value="2500.00" required>
                                </div>
                                <div class="form-group">
                                    <label>Descuento Aplicado (S/)</label>
                                    <input type="number" step="0.01" name="descuento_aplicado" class="form-control" value="0.00">
                                </div>
                                <div class="form-group">
                                    <label>Abono Inicial Recibido Hoy (S/)</label>
                                    <input type="number" step="0.01" name="abono_inicial" class="form-control" value="800.00">
                                </div>
                                <div class="form-group">
                                    <label>Método del Abono Inicial</label>
                                    <select name="metodo_pago_inicial" class="form-control">
                                        <option value="Transferencia BCP">Transferencia BCP</option>
                                        <option value="Yape">Yape</option>
                                        <option value="Plin">Plin</option>
                                        <option value="Transferencia Interbank">Transferencia Interbank</option>
                                        <option value="Transferencia BBVA">Transferencia BBVA</option>
                                        <option value="Efectivo">Efectivo</option>
                                    </select>
                                </div>
                                <div class="form-group" style="grid-column: span 2;">
                                    <label>Título del Tema de Investigación / Tesis</label>
                                    <input type="text" name="titulo_tesis" class="form-control" placeholder="Ingresa el título tentativo o definitivo de la tesis">
                                </div>
                            </div>
                            <div style="margin-top:16px;text-align:right;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-floppy-disk"></i> Guardar Cliente y Registrar Estado Financiero
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de Clientes Activos -->
                <div class="panel-card">
                    <div class="panel-header">
                        <h3><i class="fa-solid fa-users-viewfinder" style="color:#0074F0;"></i> Cartera de Clientes, Avance de Tesis y Saldos Pendientes</h3>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <a href="imprimir.php?tipo=clientes&autoprint=1" class="btn btn-sm" style="background:#F59E0B;color:#031126;font-weight:900;">
                                <i class="fa-solid fa-print"></i> Imprimir Clientes
                            </a>
                            <a href="exportar.php?tipo=clientes" class="btn btn-success btn-sm">
                                <i class="fa-solid fa-download"></i> Descargar Clientes (.xls)
                            </a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Código / Cliente</th>
                                        <th>Universidad / Carrera</th>
                                        <th>Servicio y Etapa</th>
                                        <th>Avance</th>
                                        <th>Total Final</th>
                                        <th>Abonado</th>
                                        <th>Saldo Pendiente</th>
                                        <th>Estado Pago</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientes as $cli):
                                        $efClass = 'pill-blue';
                                        if ($cli['estado_financiero'] === 'PAGADO_TOTAL') $efClass = 'pill-success';
                                        elseif ($cli['estado_financiero'] === 'PENDIENTE') $efClass = 'pill-warning';
                                        elseif ($cli['estado_financiero'] === 'VENCIDO') $efClass = 'pill-danger';

                                        $wspCli = preg_replace('/\D/', '', $cli['telefono']);
                                        if (strlen($wspCli) === 9) $wspCli = '51' . $wspCli;
                                        $msgRecordatorio = "Hola {$cli['nombres']}, te saludamos de *Impulso Académico* respecto a tu proyecto ({$cli['codigo_cliente']} - {$cli['servicio_contratado']}). Tu avance actual está al {$cli['porcentaje_avance']}% ({$cli['estado_tesis']}). Saldo pendiente: S/ " . number_format((float)$cli['saldo_pendiente'], 2);
                                        $urlWspCli = "https://wa.me/{$wspCli}?text=" . rawurlencode($msgRecordatorio);
                                    ?>
                                    <tr>
                                        <td>
                                            <strong style="color:var(--ia-blue);font-size:0.78rem;display:block;"><?= htmlspecialchars($cli['codigo_cliente']) ?></strong>
                                            <strong><?= htmlspecialchars($cli['nombres'] . ' ' . $cli['apellidos']) ?></strong>
                                            <small style="display:block;color:#64748B;"><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($cli['telefono']) ?></small>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($cli['universidad']) ?></strong>
                                            <small style="display:block;color:#64748B;"><?= htmlspecialchars($cli['carrera']) ?> • <?= htmlspecialchars($cli['nivel_academico']) ?></small>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($cli['servicio_contratado']) ?></strong>
                                            <small style="display:block;color:#0074F0;font-weight:700;"><?= htmlspecialchars($cli['estado_tesis']) ?></small>
                                            <small style="display:block;color:#64748B;">Asesor: <?= htmlspecialchars($cli['asesor_asignado']) ?></small>
                                        </td>
                                        <td>
                                            <strong style="font-size:0.82rem;"><?= (int)$cli['porcentaje_avance'] ?>%</strong>
                                            <div class="progress-track">
                                                <div class="progress-fill" style="width:<?= (int)$cli['porcentaje_avance'] ?>%;"></div>
                                            </div>
                                        </td>
                                        <td style="font-weight:800;">S/ <?= number_format((float)$cli['monto_final'], 2) ?></td>
                                        <td style="font-weight:800;color:#059669;">S/ <?= number_format((float)$cli['total_abonado'], 2) ?></td>
                                        <td style="font-weight:900;color:<?= (float)$cli['saldo_pendiente'] > 0 ? '#D97706' : '#059669' ?>;">
                                            S/ <?= number_format((float)$cli['saldo_pendiente'], 2) ?>
                                        </td>
                                        <td>
                                            <span class="status-pill <?= $efClass ?>"><?= htmlspecialchars($cli['estado_financiero']) ?></span>
                                        </td>
                                        <td>
                                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                                <?php if ((float)$cli['saldo_pendiente'] > 0): ?>
                                                <button type="button" class="btn btn-success btn-sm" title="Registrar nuevo pago/cuota de este cliente"
                                                        onclick="abrirModalAbono(<?= (int)$cli['id'] ?>, '<?= htmlspecialchars(addslashes($cli['nombres'] . ' ' . $cli['apellidos'])) ?>', <?= (float)$cli['saldo_pendiente'] ?>)">
                                                    <i class="fa-solid fa-dollar-sign"></i> Abonar
                                                </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-primary btn-sm" title="Editar avance y datos del cliente"
                                                        onclick='abrirModalEditarCliente(<?= json_encode($cli, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <a href="<?= htmlspecialchars($urlWspCli) ?>" target="_blank" class="btn btn-sm" style="background:#DCFCE7;color:#166534;" title="Enviar estado de cuenta por WhatsApp">
                                                    <i class="fa-brands fa-whatsapp"></i>
                                                </a>
                                                <form action="acciones.php" method="POST" onsubmit="return confirm('¿Eliminar este cliente?');" style="display:inline;">
                                                    <input type="hidden" name="accion" value="eliminar_cliente">
                                                    <input type="hidden" name="cliente_id" value="<?= (int)$cli['id'] ?>">
                                                    <input type="hidden" name="tab_retorno" value="clientes">
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =================================================================
                 TAB 3: LIBRO DE INGRESOS Y EGRESOS (ESTADOS FINANCIEROS)
                 ================================================================= -->
            <section id="tab-finanzas" class="tab-pane <?= $activeTab === 'finanzas' ? 'active' : '' ?>">
                <div class="panel-card">
                    <div class="panel-header">
                        <h3><i class="fa-solid fa-cash-register" style="color:#0074F0;"></i> Registrar Nuevo Movimiento Financiero (Ingreso de Cliente o Egreso Operativo)</h3>
                    </div>
                    <div class="panel-body">
                        <form action="acciones.php" method="POST">
                            <input type="hidden" name="accion" value="crear_movimiento">
                            <input type="hidden" name="tab_retorno" value="finanzas">
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>Tipo de Movimiento *</label>
                                    <select name="tipo" class="form-control" required>
                                        <option value="INGRESO">INGRESO (+) Cobro / Cuota de Cliente</option>
                                        <option value="EGRESO">EGRESO (-) Pago a Asesor / Gasto Operativo</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Categoría Financiera *</label>
                                    <select name="categoria" class="form-control">
                                        <option value="Abono de Cliente">Ingreso: Abono / Cuota de Cliente</option>
                                        <option value="Pago Inicial">Ingreso: Pago Inicial de Tesis</option>
                                        <option value="Liquidación Final">Ingreso: Liquidación Final de Tesis</option>
                                        <option value="Honorarios de Asesor">Egreso: Honorarios de Asesor / Investigador</option>
                                        <option value="Licencias / Turnitin / SPSS">Egreso: Licencias Turnitin / SPSS / Stata</option>
                                        <option value="Publicidad y Marketing">Egreso: Publicidad (Facebook / TikTok / Instagram)</option>
                                        <option value="Gastos Administrativos">Egreso: Gastos Administrativos</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Vincular a Cliente (Opcional si es Ingreso)</label>
                                    <select name="cliente_id" class="form-control">
                                        <option value="">-- Movimiento General / No vincular --</option>
                                        <?php foreach ($clientes as $c): ?>
                                        <option value="<?= (int)$c['id'] ?>">
                                            <?= htmlspecialchars($c['codigo_cliente'] . ' - ' . $c['nombres'] . ' ' . $c['apellidos'] . ' (Saldo: S/ ' . $c['saldo_pendiente'] . ')') ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Monto en Soles (S/) *</label>
                                    <input type="number" step="0.01" name="monto" class="form-control" placeholder="Ej. 500.00" required>
                                </div>
                                <div class="form-group">
                                    <label>Medio de Pago</label>
                                    <select name="metodo_pago" class="form-control">
                                        <option value="Transferencia BCP">Transferencia BCP</option>
                                        <option value="Yape">Yape</option>
                                        <option value="Plin">Plin</option>
                                        <option value="Transferencia Interbank">Transferencia Interbank</option>
                                        <option value="Transferencia BBVA">Transferencia BBVA</option>
                                        <option value="Efectivo">Efectivo</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>N° Operación / Referencia</label>
                                    <input type="text" name="referencia_operacion" class="form-control" placeholder="Ej. OP-482910">
                                </div>
                                <div class="form-group" style="grid-column: span 2;">
                                    <label>Concepto / Descripción del Movimiento *</label>
                                    <input type="text" name="concepto" class="form-control" placeholder="Ej. 2da Cuota - Avance Marco Teórico / Pago a Asesor Estadístico" required>
                                </div>
                                <div class="form-group">
                                    <label>Fecha del Movimiento</label>
                                    <input type="date" name="fecha_movimiento" class="form-control" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div style="margin-top:16px;text-align:right;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-plus-circle"></i> Registrar en Libro Financiero
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Historial de Movimientos -->
                <div class="panel-card">
                    <div class="panel-header">
                        <h3><i class="fa-solid fa-list-ul" style="color:#0074F0;"></i> Libro Mayor de Movimientos Financieros (Ingresos y Egresos)</h3>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <a href="imprimir.php?tipo=ingresos_egresos&autoprint=1" class="btn btn-sm" style="background:#F59E0B;color:#031126;font-weight:900;">
                                <i class="fa-solid fa-print"></i> Imprimir Ingresos y Egresos
                            </a>
                            <a href="exportar.php?tipo=ingresos_egresos" class="btn btn-success btn-sm">
                                <i class="fa-solid fa-download"></i> Descargar Ingresos y Egresos (.xls)
                            </a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Categoría</th>
                                        <th>Concepto / Cliente</th>
                                        <th>Medio / N° Op.</th>
                                        <th style="text-align:right;">Monto (S/)</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($movimientos as $m): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($m['fecha_movimiento'])) ?></td>
                                        <td>
                                            <?php if ($m['tipo'] === 'INGRESO'): ?>
                                                <span class="status-pill pill-success"><i class="fa-solid fa-arrow-up"></i> INGRESO</span>
                                            <?php else: ?>
                                                <span class="status-pill pill-danger"><i class="fa-solid fa-arrow-down"></i> EGRESO</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($m['categoria']) ?></strong></td>
                                        <td>
                                            <strong><?= htmlspecialchars($m['concepto']) ?></strong>
                                            <?php if (!empty($m['cli_nombres'])): ?>
                                            <small style="display:block;color:#0074F0;font-weight:700;">
                                                Cliente: <?= htmlspecialchars($m['codigo_cliente'] . ' - ' . $m['cli_nombres'] . ' ' . $m['cli_apellidos']) ?>
                                            </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($m['metodo_pago']) ?>
                                            <?php if (!empty($m['referencia_operacion'])): ?>
                                            <small style="display:block;color:#64748B;"><?= htmlspecialchars($m['referencia_operacion']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align:right;font-weight:900;color:<?= $m['tipo'] === 'INGRESO' ? '#059669' : '#DC2626' ?>;">
                                            <?= $m['tipo'] === 'INGRESO' ? '+' : '-' ?> S/ <?= number_format((float)$m['monto'], 2) ?>
                                        </td>
                                        <td>
                                            <form action="acciones.php" method="POST" onsubmit="return confirm('¿Eliminar este movimiento financiero?');">
                                                <input type="hidden" name="accion" value="eliminar_movimiento">
                                                <input type="hidden" name="movimiento_id" value="<?= (int)$m['id'] ?>">
                                                <input type="hidden" name="tab_retorno" value="finanzas">
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =================================================================
                 TAB 4: GESTOR DE CAMPAÑAS DE DESCUENTO (SE VISUALIZA EN LA WEB)
                 ================================================================= -->
            <section id="tab-campanas" class="tab-pane <?= $activeTab === 'campanas' ? 'active' : '' ?>">
                <div class="panel-card">
                    <div class="panel-header">
                        <h3><i class="fa-solid fa-bullhorn" style="color:#0074F0;"></i> Crear Nueva Campaña de Descuento (Se publica inmediatamente en la Web)</h3>
                    </div>
                    <div class="panel-body">
                        <form action="acciones.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="accion" value="crear_campana">
                            <input type="hidden" name="tab_retorno" value="campanas">
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>Título de la Campaña *</label>
                                    <input type="text" name="titulo" class="form-control" placeholder="Ej. Campaña Cierre de Ciclo 2026" required>
                                </div>
                                <div class="form-group">
                                    <label>Subtítulo / Beneficio Principal *</label>
                                    <input type="text" name="subtitulo" class="form-control" placeholder="Ej. 30% OFF en Tesis Completa + Diagnóstico Gratis" required>
                                </div>
                                <div class="form-group">
                                    <label>Porcentaje de Descuento (%) *</label>
                                    <input type="number" name="porcentaje_descuento" class="form-control" value="25" min="5" max="90" required>
                                </div>
                                <div class="form-group">
                                    <label>Código Promocional</label>
                                    <input type="text" name="codigo_promo" class="form-control" placeholder="Ej. TESIS30">
                                </div>
                                <div class="form-group">
                                    <label>Etiqueta Superior</label>
                                    <input type="text" name="etiqueta_oferta" class="form-control" value="🔥 OFERTA POR TIEMPO LIMITADO">
                                </div>
                                <div class="form-group">
                                    <label>Fecha Límite de Vigencia</label>
                                    <input type="date" name="fecha_fin" class="form-control" value="<?= date('Y-m-d', strtotime('+20 days')) ?>">
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-solid fa-upload" style="color:#0074F0;"></i> Subir Imagen de la Campaña desde tu PC</label>
                                    <input type="file" name="imagen_archivo" class="form-control" accept="image/*">
                                </div>
                                <div class="form-group" style="grid-column: span 2;">
                                    <label>Descripción / Condiciones</label>
                                    <input type="text" name="descripcion" class="form-control" placeholder="Ej. Válido para pregrado y posgrado coordinando en cuotas por interno." required>
                                </div>
                            </div>
                            <div style="margin-top:14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                                <label style="display:flex;align-items:center;gap:8px;font-weight:700;cursor:pointer;">
                                    <input type="checkbox" name="mostrar_banner_superior" value="1" checked>
                                    Mostrar también en la Barra Superior Animada de la página principal
                                </label>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-rocket"></i> Publicar Campaña de Descuento en la Web
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de Campañas -->
                <div class="panel-card">
                    <div class="panel-header">
                        <h3><i class="fa-solid fa-tags" style="color:#0074F0;"></i> Campañas de Descuento Registradas en la Base de Datos</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Imagen (BD)</th>
                                        <th>Campaña y Descripción</th>
                                        <th>Descuento / Código</th>
                                        <th>Vigencia</th>
                                        <th>Estado en Web</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($campanas as $camp): ?>
                                    <tr>
                                        <td>
                                            <img src="../<?= htmlspecialchars($camp['imagen_url']) ?>" alt="Campaña" style="width:95px;height:60px;object-fit:cover;border-radius:8px;border:1px solid var(--ia-border);">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($camp['titulo']) ?></strong>
                                            <small style="display:block;color:#0074F0;font-weight:700;"><?= htmlspecialchars($camp['subtitulo']) ?></small>
                                            <small style="color:#64748B;"><?= htmlspecialchars($camp['descripcion']) ?></small>
                                        </td>
                                        <td>
                                            <span class="status-pill pill-danger" style="font-size:0.85rem;">-<?= (int)$camp['porcentaje_descuento'] ?>% OFF</span>
                                            <small style="display:block;margin-top:4px;font-family:monospace;font-weight:800;"><?= htmlspecialchars($camp['codigo_promo']) ?></small>
                                        </td>
                                        <td>Hasta <?= !empty($camp['fecha_fin']) ? date('d/m/Y', strtotime($camp['fecha_fin'])) : 'Indefinido' ?></td>
                                        <td>
                                            <?php if ($camp['estado'] === 'ACTIVA'): ?>
                                                <span class="status-pill pill-success">VISIBLE EN WEB</span>
                                            <?php else: ?>
                                                <span class="status-pill pill-warning">OCULTA</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="display:flex;gap:6px;">
                                                <form action="acciones.php" method="POST">
                                                    <input type="hidden" name="accion" value="toggle_campana">
                                                    <input type="hidden" name="campana_id" value="<?= (int)$camp['id'] ?>">
                                                    <input type="hidden" name="tab_retorno" value="campanas">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <?= $camp['estado'] === 'ACTIVA' ? 'Ocultar' : 'Activar' ?>
                                                    </button>
                                                </form>
                                                <form action="acciones.php" method="POST" onsubmit="return confirm('¿Eliminar esta campaña?');">
                                                    <input type="hidden" name="accion" value="eliminar_campana">
                                                    <input type="hidden" name="campana_id" value="<?= (int)$camp['id'] ?>">
                                                    <input type="hidden" name="tab_retorno" value="campanas">
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =================================================================
                 TAB 5: GESTOR DE VIDEOS PROMOCIONALES (SUBIDA DESDE PC)
                 ================================================================= -->
            <section id="tab-videos" class="tab-pane <?= $activeTab === 'videos' ? 'active' : '' ?>">
                <div class="panel-card">
                    <div class="panel-header">
                        <h3><i class="fa-solid fa-cloud-arrow-up" style="color:#0074F0;"></i> Subir Nuevo Video Promocional desde tu PC</h3>
                    </div>
                    <div class="panel-body">
                        <form action="acciones.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="accion" value="crear_video">
                            <input type="hidden" name="tab_retorno" value="videos">
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>Título del Video Promocional *</label>
                                    <input type="text" name="titulo" class="form-control" placeholder="Ej. Testimonio Sustentación Aprobada 2026" required>
                                </div>
                                <div class="form-group">
                                    <label>Categoría</label>
                                    <input type="text" name="categoria" class="form-control" value="Promoción Impulso Académico">
                                </div>
                                <div class="form-group">
                                    <label>Duración Aprox.</label>
                                    <input type="text" name="duracion" class="form-control" value="01:20">
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-solid fa-file-video" style="color:#0074F0;"></i> Seleccionar Archivo de Video (.mp4, .webm, .mov) desde tu PC *</label>
                                    <input type="file" name="video_archivo" class="form-control" accept="video/mp4,video/webm,video/ogg,video/quicktime">
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-solid fa-image" style="color:#0074F0;"></i> Imagen de Portada / Miniatura desde tu PC (Opcional)</label>
                                    <input type="file" name="poster_archivo" class="form-control" accept="image/*">
                                </div>
                                <div class="form-group">
                                    <label>O Enlace Externo Opcional (YouTube Embed / MP4)</label>
                                    <input type="text" name="video_url_externa" class="form-control" placeholder="Opcional si no subes archivo">
                                </div>
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label>Descripción breve del video</label>
                                    <input type="text" name="descripcion" class="form-control" placeholder="Explica qué verán los clientes en este video promocional..." required>
                                </div>
                            </div>
                            <div style="margin-top:14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                                <label style="display:flex;align-items:center;gap:8px;font-weight:700;cursor:pointer;">
                                    <input type="checkbox" name="destacado" value="1" checked>
                                    Establecer como Video Principal Destacado en la Página Web
                                </label>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-upload"></i> Subir Video y Mostrar en la Web
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de Videos Promocionales -->
                <div class="panel-card">
                    <div class="panel-header">
                        <h3><i class="fa-solid fa-film" style="color:#0074F0;"></i> Videos Promocionales en la Base de Datos</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Vista Previa</th>
                                        <th>Título y Descripción</th>
                                        <th>Ruta en BD</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($videos as $v): ?>
                                    <tr>
                                        <td style="width:180px;">
                                            <video src="../<?= htmlspecialchars($v['video_url']) ?>" poster="../<?= htmlspecialchars($v['poster_url']) ?>" controls style="width:165px;height:95px;object-fit:cover;border-radius:10px;background:#000;"></video>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($v['titulo']) ?></strong>
                                            <?php if ((int)$v['destacado'] === 1): ?>
                                                <span class="status-pill pill-blue">★ PRINCIPAL</span>
                                            <?php endif; ?>
                                            <small style="display:block;color:#64748B;margin-top:4px;"><?= htmlspecialchars($v['descripcion']) ?></small>
                                        </td>
                                        <td><code style="font-size:0.78rem;color:#0074F0;"><?= htmlspecialchars($v['video_url']) ?></code></td>
                                        <td>
                                            <span class="status-pill <?= $v['estado'] === 'ACTIVO' ? 'pill-success' : 'pill-warning' ?>">
                                                <?= htmlspecialchars($v['estado']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                                <?php if ((int)$v['destacado'] === 0): ?>
                                                <form action="acciones.php" method="POST">
                                                    <input type="hidden" name="accion" value="destacar_video">
                                                    <input type="hidden" name="video_id" value="<?= (int)$v['id'] ?>">
                                                    <input type="hidden" name="tab_retorno" value="videos">
                                                    <button type="submit" class="btn btn-navy btn-sm" title="Poner primero">★ Destacar</button>
                                                </form>
                                                <?php endif; ?>
                                                <form action="acciones.php" method="POST">
                                                    <input type="hidden" name="accion" value="toggle_video">
                                                    <input type="hidden" name="video_id" value="<?= (int)$v['id'] ?>">
                                                    <input type="hidden" name="tab_retorno" value="videos">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <?= $v['estado'] === 'ACTIVO' ? 'Ocultar' : 'Activar' ?>
                                                    </button>
                                                </form>
                                                <form action="acciones.php" method="POST" onsubmit="return confirm('¿Eliminar este video?');">
                                                    <input type="hidden" name="accion" value="eliminar_video">
                                                    <input type="hidden" name="video_id" value="<?= (int)$v['id'] ?>">
                                                    <input type="hidden" name="tab_retorno" value="videos">
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =================================================================
                 TAB 6: GESTOR DE IMÁGENES DE LA WEB Y SERVICIOS (DESDE BD)
                 ================================================================= -->
            <section id="tab-imagenes" class="tab-pane <?= $activeTab === 'imagenes' ? 'active' : '' ?>">
                <div class="panel-card">
                    <div class="panel-header">
                        <h3><i class="fa-solid fa-images" style="color:#0074F0;"></i> Imágenes de las Secciones de la Web (Todas se jalan desde MySQL)</h3>
                    </div>
                    <div class="panel-body">
                        <p style="font-size:0.88rem;color:#475569;margin-bottom:18px;">
                            Desde aquí puedes reemplazar cualquier imagen de la página (Portada Hero, Logo, ¿Quiénes Somos?, Misión y Visión, Áreas de Especialización, Metodología, Contacto o Casos de Éxito) subiendo un archivo desde tu PC.
                        </p>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Imagen Actual (BD)</th>
                                        <th>Sección</th>
                                        <th>Título y Subtítulo</th>
                                        <th>Subir Nueva Imagen desde tu PC y Guardar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($imagenesWeb as $img): ?>
                                    <tr>
                                        <td>
                                            <img src="../<?= htmlspecialchars($img['imagen_url']) ?>" alt="Img" style="width:100px;height:68px;object-fit:cover;border-radius:10px;border:2px solid var(--ia-blue);">
                                            <small style="display:block;font-size:0.7rem;color:#64748B;margin-top:3px;"><?= htmlspecialchars($img['imagen_url']) ?></small>
                                        </td>
                                        <td>
                                            <span class="status-pill pill-blue"><?= htmlspecialchars($img['clave_seccion']) ?></span>
                                        </td>
                                        <td colspan="2">
                                            <form action="acciones.php" method="POST" enctype="multipart/form-data" style="display:grid;grid-template-columns:1fr 1fr 1.2fr auto;gap:10px;align-items:center;">
                                                <input type="hidden" name="accion" value="actualizar_imagen_web">
                                                <input type="hidden" name="imagen_id" value="<?= (int)$img['id'] ?>">
                                                <input type="hidden" name="etiqueta" value="<?= htmlspecialchars($img['etiqueta']) ?>">
                                                <input type="hidden" name="descripcion" value="<?= htmlspecialchars($img['descripcion']) ?>">
                                                <input type="hidden" name="tab_retorno" value="imagenes">

                                                <input type="text" name="titulo" class="form-control" value="<?= htmlspecialchars($img['titulo']) ?>" placeholder="Título">
                                                <input type="text" name="subtitulo" class="form-control" value="<?= htmlspecialchars($img['subtitulo']) ?>" placeholder="Subtítulo">
                                                <input type="file" name="imagen_archivo" class="form-control" accept="image/*">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fa-solid fa-floppy-disk"></i> Actualizar BD
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Imágenes y Tarifas de los 8 Servicios -->
                <div class="panel-card">
                    <div class="panel-header">
                        <h3><i class="fa-solid fa-graduation-cap" style="color:#0074F0;"></i> Los 8 Servicios Oficiales y sus Imágenes en la Base de Datos</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Imagen (BD)</th>
                                        <th>Servicio / Resumen / Tarifa Referencial / Cambiar Imagen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($serviciosWeb as $s): ?>
                                    <tr>
                                        <td style="width:120px;">
                                            <img src="../<?= htmlspecialchars($s['imagen_url']) ?>" alt="Servicio" style="width:100px;height:66px;object-fit:cover;border-radius:10px;border:1px solid var(--ia-border);">
                                        </td>
                                        <td>
                                            <form action="acciones.php" method="POST" enctype="multipart/form-data" style="display:grid;grid-template-columns:1fr 1.2fr 0.7fr 1fr auto;gap:10px;align-items:center;">
                                                <input type="hidden" name="accion" value="actualizar_servicio">
                                                <input type="hidden" name="servicio_id" value="<?= (int)$s['id'] ?>">
                                                <input type="hidden" name="descripcion" value="<?= htmlspecialchars($s['descripcion']) ?>">
                                                <input type="hidden" name="tab_retorno" value="imagenes">

                                                <input type="text" name="titulo" class="form-control" value="<?= htmlspecialchars($s['titulo']) ?>">
                                                <input type="text" name="resumen" class="form-control" value="<?= htmlspecialchars($s['resumen']) ?>">
                                                <input type="text" name="tarifa_referencial" class="form-control" value="<?= htmlspecialchars($s['tarifa_referencial']) ?>">
                                                <input type="file" name="imagen_archivo" class="form-control" accept="image/*">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =================================================================
                 TAB 7: SOLICITUDES / CONSULTAS DESDE LA WEB
                 ================================================================= -->
            <section id="tab-solicitudes" class="tab-pane <?= $activeTab === 'solicitudes' ? 'active' : '' ?>">
                <div class="panel-card">
                    <div class="panel-header">
                        <h3><i class="fa-solid fa-inbox" style="color:#0074F0;"></i> Solicitudes de Presupuesto Recibidas desde la Web (Coordinación Interna)</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Interesado / Teléfono</th>
                                        <th>Universidad / Área</th>
                                        <th>Servicio Solicitado</th>
                                        <th>Código Promo</th>
                                        <th>Mensaje</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($solicitudes as $sol):
                                        $wspSol = preg_replace('/\D/', '', $sol['telefono']);
                                        if (strlen($wspSol) === 9) $wspSol = '51' . $wspSol;
                                        $urlWspSol = "https://wa.me/{$wspSol}?text=" . rawurlencode("Hola {$sol['nombre_completo']}, te saludamos de *Impulso Académico* en respuesta a tu solicitud para *{$sol['servicio_interes']}*.");
                                    ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($sol['creado_en'])) ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($sol['nombre_completo']) ?></strong>
                                            <small style="display:block;color:#0074F0;font-weight:700;"><?= htmlspecialchars($sol['telefono']) ?></small>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($sol['universidad'] ?: '-') ?>
                                            <small style="display:block;color:#64748B;"><?= htmlspecialchars($sol['carrera'] . ' • ' . $sol['nivel_academico']) ?></small>
                                        </td>
                                        <td><strong><?= htmlspecialchars($sol['servicio_interes']) ?></strong></td>
                                        <td>
                                            <?php if (!empty($sol['codigo_promo'])): ?>
                                                <span class="status-pill pill-danger"><?= htmlspecialchars($sol['codigo_promo']) ?></span>
                                            <?php else: ?>
                                                <span style="color:#94A3B8;">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="max-width:260px;"><?= htmlspecialchars($sol['mensaje']) ?></td>
                                        <td>
                                            <span class="status-pill <?= $sol['estado'] === 'CONVERTIDO' ? 'pill-success' : 'pill-blue' ?>">
                                                <?= htmlspecialchars($sol['estado']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display:flex;gap:6px;">
                                                <a href="<?= htmlspecialchars($urlWspSol) ?>" target="_blank" class="btn btn-success btn-sm">
                                                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                                                </a>
                                                <?php if ($sol['estado'] !== 'CONVERTIDO'): ?>
                                                <form action="acciones.php" method="POST">
                                                    <input type="hidden" name="accion" value="convertir_solicitud">
                                                    <input type="hidden" name="solicitud_id" value="<?= (int)$sol['id'] ?>">
                                                    <input type="hidden" name="tab_retorno" value="clientes">
                                                    <button type="submit" class="btn btn-primary btn-sm" title="Crear cliente automáticamente a partir de esta solicitud">
                                                        <i class="fa-solid fa-user-check"></i> Convertir en Cliente
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- ==================== MODAL RÁPIDO PARA REGISTRAR ABONO DE CLIENTE ==================== -->
    <div class="modal-backdrop" id="modalAbono">
        <div class="modal-box">
            <div class="panel-header">
                <h3><i class="fa-solid fa-hand-holding-dollar" style="color:#10B981;"></i> Registrar Abono / Cuota por Interno</h3>
                <button type="button" class="btn btn-danger btn-sm" onclick="cerrarModal('modalAbono')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="panel-body">
                <form action="acciones.php" method="POST">
                    <input type="hidden" name="accion" value="crear_movimiento">
                    <input type="hidden" name="tipo" value="INGRESO">
                    <input type="hidden" name="categoria" value="Abono de Cliente">
                    <input type="hidden" name="cliente_id" id="abonoClienteId">
                    <input type="hidden" name="tab_retorno" value="clientes">

                    <div class="form-grid-3">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Cliente</label>
                            <input type="text" id="abonoClienteNombre" class="form-control" readonly style="background:#F1F5F9;font-weight:800;">
                        </div>
                        <div class="form-group">
                            <label>Monto a Abonar (S/) *</label>
                            <input type="number" step="0.01" name="monto" id="abonoMontoInput" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Método de Pago</label>
                            <select name="metodo_pago" class="form-control">
                                <option value="Yape">Yape</option>
                                <option value="Plin">Plin</option>
                                <option value="Transferencia BCP">Transferencia BCP</option>
                                <option value="Transferencia Interbank">Transferencia Interbank</option>
                                <option value="Transferencia BBVA">Transferencia BBVA</option>
                                <option value="Efectivo">Efectivo</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>N° Operación</label>
                            <input type="text" name="referencia_operacion" class="form-control" placeholder="Ej. OP-12345">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Concepto del Pago *</label>
                            <input type="text" name="concepto" id="abonoConceptoInput" class="form-control" required>
                        </div>
                    </div>
                    <div style="margin-top:18px;text-align:right;">
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-check"></i> Confirmar Abono y Actualizar Saldo del Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL PARA EDITAR CLIENTE ==================== -->
    <div class="modal-backdrop" id="modalEditarCliente">
        <div class="modal-box">
            <div class="panel-header">
                <h3><i class="fa-solid fa-user-pen" style="color:#0074F0;"></i> Actualizar Avance Académico y Finanzas del Cliente</h3>
                <button type="button" class="btn btn-danger btn-sm" onclick="cerrarModal('modalEditarCliente')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="panel-body">
                <form action="acciones.php" method="POST">
                    <input type="hidden" name="accion" value="editar_cliente">
                    <input type="hidden" name="cliente_id" id="editCliId">
                    <input type="hidden" name="tab_retorno" value="clientes">

                    <div class="form-grid-3">
                        <div class="form-group">
                            <label>Nombres</label>
                            <input type="text" name="nombres" id="editCliNombres" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Apellidos</label>
                            <input type="text" name="apellidos" id="editCliApellidos" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Teléfono / WhatsApp</label>
                            <input type="text" name="telefono" id="editCliTelefono" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Universidad</label>
                            <input type="text" name="universidad" id="editCliUni" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Carrera / Área</label>
                            <input type="text" name="carrera" id="editCliCarrera" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Nivel</label>
                            <select name="nivel_academico" id="editCliNivel" class="form-control">
                                <option value="Pregrado">Pregrado</option>
                                <option value="Maestría">Maestría</option>
                                <option value="Doctorado">Doctorado</option>
                                <option value="Segunda Especialidad">Segunda Especialidad</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Servicio</label>
                            <input type="text" name="servicio_contratado" id="editCliServicio" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Asesor Asignado</label>
                            <input type="text" name="asesor_asignado" id="editCliAsesor" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Etapa de Tesis</label>
                            <select name="estado_tesis" id="editCliEstadoTesis" class="form-control">
                                <option value="Diagnóstico Inicial">1. Diagnóstico Inicial</option>
                                <option value="Plan Personalizado">2. Plan Personalizado</option>
                                <option value="Marco Teórico">3. Marco Teórico</option>
                                <option value="Análisis Estadístico">4. Análisis Estadístico</option>
                                <option value="Redacción APA 7 / Turnitin">5. Redacción APA 7 / Turnitin</option>
                                <option value="Levantando Observaciones">6. Levantando Observaciones</option>
                                <option value="Listo para Sustentar">7. Listo para Sustentar</option>
                                <option value="Sustentado / Aprobado">8. Sustentado / Aprobado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>% Avance (0-100)</label>
                            <input type="number" name="porcentaje_avance" id="editCliAvance" class="form-control" min="0" max="100">
                        </div>
                        <div class="form-group">
                            <label>Costo Total (S/)</label>
                            <input type="number" step="0.01" name="costo_total" id="editCliCosto" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Descuento (S/)</label>
                            <input type="number" step="0.01" name="descuento_aplicado" id="editCliDescuento" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Estado Financiero</label>
                            <select name="estado_financiero" id="editCliEstadoFin" class="form-control">
                                <option value="AL_DIA">AL_DIA</option>
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="PAGADO_TOTAL">PAGADO_TOTAL</option>
                                <option value="VENCIDO">VENCIDO</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fecha Entrega</label>
                            <input type="date" name="fecha_entrega_estimada" id="editCliFechaEntrega" class="form-control">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Título de Tesis</label>
                            <input type="text" name="titulo_tesis" id="editCliTitulo" class="form-control">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Notas Internas</label>
                            <input type="text" name="notas_internas" id="editCliNotas" class="form-control">
                        </div>
                    </div>
                    <div style="margin-top:18px;text-align:right;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.sidebar-nav .nav-btn').forEach(b => b.classList.remove('active'));
            const pane = document.getElementById('tab-' + tabName);
            if (pane) pane.classList.add('active');

            const btns = document.querySelectorAll('.sidebar-nav .nav-btn');
            const map = ['dashboard', 'clientes', 'finanzas', 'campanas', 'videos', 'imagenes', 'solicitudes'];
            const idx = map.indexOf(tabName);
            if (idx >= 0 && btns[idx]) btns[idx].classList.add('active');
        }

        function abrirModalAbono(id, nombre, saldo) {
            document.getElementById('abonoClienteId').value = id;
            document.getElementById('abonoClienteNombre').value = nombre + ' (Saldo pendiente: S/ ' + saldo.toFixed(2) + ')';
            document.getElementById('abonoMontoInput').value = saldo.toFixed(2);
            document.getElementById('abonoConceptoInput').value = 'Abono de cuota - ' + nombre;
            document.getElementById('modalAbono').classList.add('open');
        }

        function abrirModalEditarCliente(cli) {
            document.getElementById('editCliId').value = cli.id;
            document.getElementById('editCliNombres').value = cli.nombres;
            document.getElementById('editCliApellidos').value = cli.apellidos;
            document.getElementById('editCliTelefono').value = cli.telefono;
            document.getElementById('editCliUni').value = cli.universidad;
            document.getElementById('editCliCarrera').value = cli.carrera;
            document.getElementById('editCliNivel').value = cli.nivel_academico;
            document.getElementById('editCliServicio').value = cli.servicio_contratado;
            document.getElementById('editCliAsesor').value = cli.asesor_asignado;
            document.getElementById('editCliEstadoTesis').value = cli.estado_tesis;
            document.getElementById('editCliAvance').value = cli.porcentaje_avance;
            document.getElementById('editCliCosto').value = cli.costo_total;
            document.getElementById('editCliDescuento').value = cli.descuento_aplicado;
            document.getElementById('editCliEstadoFin').value = cli.estado_financiero;
            document.getElementById('editCliFechaEntrega').value = cli.fecha_entrega_estimada;
            document.getElementById('editCliTitulo').value = cli.titulo_tesis;
            document.getElementById('editCliNotas').value = cli.notas_internas;
            document.getElementById('modalEditarCliente').classList.add('open');
        }

        function cerrarModal(id) {
            document.getElementById(id).classList.remove('open');
        }

        // Gráficos de Estados Financieros (Chart.js)
        document.addEventListener('DOMContentLoaded', () => {
            const ctxFin = document.getElementById('chartFinanzas');
            if (ctxFin && typeof Chart !== 'undefined') {
                new Chart(ctxFin, {
                    type: 'bar',
                    data: {
                        labels: ['Total Contratos', 'Ingresos Cobrados', 'Cuentas por Cobrar', 'Egresos Operativos', 'Utilidad Neta'],
                        datasets: [{
                            label: 'Monto en Soles (S/)',
                            data: [
                                <?= round($totalCarteraContratos, 2) ?>,
                                <?= round($totalIngresosCobrados, 2) ?>,
                                <?= round($totalCuentasPorCobrar, 2) ?>,
                                <?= round($totalEgresosOperativos, 2) ?>,
                                <?= round($utilidadNetaCaja, 2) ?>
                            ],
                            backgroundColor: ['#042152', '#10B981', '#F59E0B', '#EF4444', '#0074F0'],
                            borderRadius: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } }
                    }
                });
            }

            const ctxEst = document.getElementById('chartClientesEstado');
            if (ctxEst && typeof Chart !== 'undefined') {
                new Chart(ctxEst, {
                    type: 'doughnut',
                    data: {
                        labels: ['Al Día', 'Pagado Total', 'Pendiente', 'Vencido'],
                        datasets: [{
                            data: [
                                <?= (int)$conteoEstadosFin['AL_DIA'] ?>,
                                <?= (int)$conteoEstadosFin['PAGADO_TOTAL'] ?>,
                                <?= (int)$conteoEstadosFin['PENDIENTE'] ?>,
                                <?= (int)$conteoEstadosFin['VENCIDO'] ?>
                            ],
                            backgroundColor: ['#0074F0', '#10B981', '#F59E0B', '#EF4444']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }

            if (window.location.pathname.endsWith('.html') || window.location.hostname.includes('github.io')) {
                document.querySelectorAll('form[action="acciones.php"]').forEach((form) => {
                    form.addEventListener('submit', (e) => {
                        e.preventDefault();
                        document.querySelectorAll('.modal-overlay.open').forEach((m) => m.classList.remove('open'));
                        let alertBox = document.getElementById('ghPagesToast');
                        if (!alertBox) {
                            alertBox = document.createElement('div');
                            alertBox.id = 'ghPagesToast';
                            alertBox.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;background:#064E3B;border:2px solid #10B981;color:#ECFDF5;padding:14px 22px;border-radius:14px;font-weight:800;box-shadow:0 16px 36px rgba(0,0,0,0.45);';
                            document.body.appendChild(alertBox);
                        }
                        alertBox.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#34D399;margin-right:8px;"></i> ¡Operación registrada en el Panel! (Para guardar en MySQL usa Laragon o GitHub Codespaces)';
                        alertBox.style.display = 'block';
                        setTimeout(() => { alertBox.style.display = 'none'; }, 4500);
                    });
                });
            }
        });
    </script>
</body>
</html>
