<?php
/**
 * API para registrar solicitudes y cotizaciones desde la página pública
 * (Sin pasarela de pagos: coordinación 100% interna con el cliente)
 */
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

try {
    $pdo = getPDO();
    $cfg = obtenerConfiguracion($pdo);

    $nombre = trim($_POST['nombre_completo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $universidad = trim($_POST['universidad'] ?? '');
    $carrera = trim($_POST['carrera'] ?? '');
    $nivel = trim($_POST['nivel_academico'] ?? 'Pregrado');
    $servicio = trim($_POST['servicio_interes'] ?? 'Asesoría Integral de Tesis');
    $codigoPromo = strtoupper(trim($_POST['codigo_promo'] ?? ''));
    $mensaje = trim($_POST['mensaje'] ?? '');

    if ($nombre === '' || $telefono === '') {
        echo json_encode([
            'ok' => false,
            'mensaje' => 'Por favor ingresa tu nombre completo y tu número de celular/WhatsApp.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO solicitudes_web (
            nombre_completo, telefono, email, universidad, carrera,
            nivel_academico, servicio_interes, codigo_promo, mensaje, estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'NUEVO')
    ");
    $stmt->execute([
        $nombre, $telefono, $email, $universidad, $carrera,
        $nivel, $servicio, $codigoPromo ?: null, $mensaje
    ]);

    $idSolicitud = $pdo->lastInsertId();
    $whatsappNum = preg_replace('/\D/', '', $cfg['whatsapp_numero'] ?? '51987654321');

    $textoWsp = "Hola *Impulso Académico*, acabo de registrar mi solicitud #{$idSolicitud} en la web.\n"
        . "• *Nombre:* {$nombre}\n"
        . "• *Nivel:* {$nivel} ({$universidad} - {$carrera})\n"
        . "• *Servicio:* {$servicio}\n"
        . ($codigoPromo ? "• *Código de Campaña:* {$codigoPromo}\n" : "")
        . "• *Detalle:* " . ($mensaje ?: 'Deseo coordinar presupuesto y facilidades de pago por interno.');

    $urlWhatsapp = "https://wa.me/{$whatsappNum}?text=" . rawurlencode($textoWsp);

    echo json_encode([
        'ok' => true,
        'id' => $idSolicitud,
        'mensaje' => '¡Solicitud registrada con éxito! Un asesor especialista se comunicará contigo por interno para coordinar tu diagnóstico, cronograma de entregas y facilidades de pago.',
        'whatsapp_url' => $urlWhatsapp
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Error al registrar la solicitud: ' . $e->getMessage()
    ]);
}
