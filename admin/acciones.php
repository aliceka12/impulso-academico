<?php
/**
 * IMPULSO ACADÉMICO - Procesador de Acciones y Subida de Archivos desde PC
 * Controla Clientes, Estados Financieros, Campañas de Descuento, Videos Promocionales e Imágenes BD.
 */
require_once dirname(__DIR__) . '/config/db.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$tabRetorno = $_POST['tab_retorno'] ?? $_GET['tab'] ?? 'dashboard';

/**
 * Sube un archivo desde la PC del administrador a la carpeta indicada en uploads/
 */
function subirArchivoDesdePC(string $inputName, string $subFolder, array $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif', 'mp4', 'webm', 'ogg', 'mov']): ?string {
    if (empty($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $tmpName = $_FILES[$inputName]['tmp_name'];
    $origName = basename($_FILES[$inputName]['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExts, true)) {
        return null;
    }

    $targetDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $subFolder;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $cleanBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
    $newFileName = $cleanBase . '_' . time() . '_' . mt_rand(100, 999) . '.' . $ext;
    $destPath = $targetDir . DIRECTORY_SEPARATOR . $newFileName;

    if (move_uploaded_file($tmpName, $destPath)) {
        return 'uploads/' . $subFolder . '/' . $newFileName;
    }

    return null;
}

try {
    switch ($accion) {
        // ====================================================================
        // 1. GESTIÓN DE CLIENTES Y PROYECTOS DE TESIS
        // ====================================================================
        case 'crear_cliente':
            $totalRows = (int)$pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn() + 1;
            $codigo = trim($_POST['codigo_cliente'] ?? '') ?: sprintf('IA-2026-%03d', $totalRows + mt_rand(10, 99));
            $nombres = trim($_POST['nombres'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            $dni = trim($_POST['dni'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $universidad = trim($_POST['universidad'] ?? '');
            $carrera = trim($_POST['carrera'] ?? '');
            $nivel = $_POST['nivel_academico'] ?? 'Pregrado';
            $servicio = trim($_POST['servicio_contratado'] ?? 'Realización Completa de la Tesis');
            $tituloTesis = trim($_POST['titulo_tesis'] ?? '');
            $asesor = trim($_POST['asesor_asignado'] ?? 'Por asignar');
            $estadoTesis = $_POST['estado_tesis'] ?? 'Diagnóstico Inicial';
            $avance = max(0, min(100, (int)($_POST['porcentaje_avance'] ?? 10)));
            $costoTotal = (float)($_POST['costo_total'] ?? 0);
            $descuento = (float)($_POST['descuento_aplicado'] ?? 0);
            $montoFinal = max(0, $costoTotal - $descuento);
            $abonoInicial = (float)($_POST['abono_inicial'] ?? 0);
            $saldo = max(0, $montoFinal - $abonoInicial);
            $cuotas = max(1, (int)($_POST['numero_cuotas'] ?? 3));
            $estadoFin = $saldo <= 0.01 && $montoFinal > 0 ? 'PAGADO_TOTAL' : ($_POST['estado_financiero'] ?? 'AL_DIA');
            $fechaInicio = $_POST['fecha_inicio'] ?: date('Y-m-d');
            $fechaEntrega = $_POST['fecha_entrega_estimada'] ?: date('Y-m-d', strtotime('+30 days'));
            $notas = trim($_POST['notas_internas'] ?? '');

            $stmt = $pdo->prepare("
                INSERT INTO clientes (
                    codigo_cliente, nombres, apellidos, dni, telefono, email, universidad, carrera, nivel_academico,
                    servicio_contratado, titulo_tesis, asesor_asignado, estado_tesis, porcentaje_avance,
                    costo_total, descuento_aplicado, monto_final, total_abonado, saldo_pendiente, numero_cuotas,
                    estado_financiero, fecha_inicio, fecha_entrega_estimada, notas_internas
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $codigo, $nombres, $apellidos, $dni, $telefono, $email, $universidad, $carrera, $nivel,
                $servicio, $tituloTesis, $asesor, $estadoTesis, $avance,
                $costoTotal, $descuento, $montoFinal, $abonoInicial, $saldo, $cuotas,
                $estadoFin, $fechaInicio, $fechaEntrega, $notas
            ]);
            $nuevoClienteId = (int)$pdo->lastInsertId();

            // Si registró un abono inicial al crear al cliente, lo agregamos automáticamente a Movimientos Financieros
            if ($abonoInicial > 0) {
                $metodoPago = $_POST['metodo_pago_inicial'] ?? 'Transferencia BCP';
                $stmtMov = $pdo->prepare("
                    INSERT INTO movimientos_financieros (
                        tipo, categoria, cliente_id, concepto, monto, metodo_pago, referencia_operacion, fecha_movimiento, estado, observaciones
                    ) VALUES ('INGRESO', 'Pago Inicial', ?, ?, ?, ?, ?, ?, 'CONFIRMADO', 'Abono inicial registrado al dar de alta al cliente')
                ");
                $stmtMov->execute([
                    $nuevoClienteId,
                    "Abono Inicial - {$servicio} ({$nombres} {$apellidos})",
                    $abonoInicial,
                    $metodoPago,
                    'INI-' . date('His'),
                    date('Y-m-d')
                ]);
                recalcularFinanzasCliente($pdo, $nuevoClienteId);
            }

            $_SESSION['flash_msg'] = "Cliente {$nombres} {$apellidos} ({$codigo}) registrado exitosamente.";
            break;

        case 'editar_cliente':
            $id = (int)($_POST['cliente_id'] ?? 0);
            $nombres = trim($_POST['nombres'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $universidad = trim($_POST['universidad'] ?? '');
            $carrera = trim($_POST['carrera'] ?? '');
            $nivel = $_POST['nivel_academico'] ?? 'Pregrado';
            $servicio = trim($_POST['servicio_contratado'] ?? '');
            $tituloTesis = trim($_POST['titulo_tesis'] ?? '');
            $asesor = trim($_POST['asesor_asignado'] ?? '');
            $estadoTesis = $_POST['estado_tesis'] ?? 'Diagnóstico Inicial';
            $avance = max(0, min(100, (int)($_POST['porcentaje_avance'] ?? 0)));
            $costoTotal = (float)($_POST['costo_total'] ?? 0);
            $descuento = (float)($_POST['descuento_aplicado'] ?? 0);
            $montoFinal = max(0, $costoTotal - $descuento);
            $estadoFin = $_POST['estado_financiero'] ?? 'AL_DIA';
            $fechaEntrega = $_POST['fecha_entrega_estimada'] ?: null;
            $notas = trim($_POST['notas_internas'] ?? '');

            $stmt = $pdo->prepare("
                UPDATE clientes SET
                    nombres = ?, apellidos = ?, telefono = ?, universidad = ?, carrera = ?, nivel_academico = ?,
                    servicio_contratado = ?, titulo_tesis = ?, asesor_asignado = ?, estado_tesis = ?,
                    porcentaje_avance = ?, costo_total = ?, descuento_aplicado = ?, monto_final = ?,
                    estado_financiero = ?, fecha_entrega_estimada = ?, notas_internas = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $nombres, $apellidos, $telefono, $universidad, $carrera, $nivel,
                $servicio, $tituloTesis, $asesor, $estadoTesis,
                $avance, $costoTotal, $descuento, $montoFinal,
                $estadoFin, $fechaEntrega, $notas, $id
            ]);
            recalcularFinanzasCliente($pdo, $id);
            $_SESSION['flash_msg'] = "Datos académicos y financieros del cliente actualizados.";
            break;

        case 'eliminar_cliente':
            $id = (int)($_POST['cliente_id'] ?? $_GET['id'] ?? 0);
            $pdo->prepare("DELETE FROM clientes WHERE id = ?")->execute([$id]);
            $_SESSION['flash_msg'] = "Cliente eliminado correctamente.";
            break;

        // ====================================================================
        // 2. GESTIÓN DE ESTADOS FINANCIEROS (INGRESOS Y EGRESOS)
        // ====================================================================
        case 'crear_movimiento':
            $tipo = $_POST['tipo'] === 'EGRESO' ? 'EGRESO' : 'INGRESO';
            $categoria = trim($_POST['categoria'] ?? ($tipo === 'INGRESO' ? 'Abono de Cliente' : 'Honorarios de Asesor'));
            $clienteId = !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
            $concepto = trim($_POST['concepto'] ?? '');
            $monto = (float)($_POST['monto'] ?? 0);
            $metodo = trim($_POST['metodo_pago'] ?? 'Transferencia BCP');
            $referencia = trim($_POST['referencia_operacion'] ?? '');
            $fecha = $_POST['fecha_movimiento'] ?: date('Y-m-d');
            $observaciones = trim($_POST['observaciones'] ?? '');

            if ($monto > 0 && $concepto !== '') {
                $stmt = $pdo->prepare("
                    INSERT INTO movimientos_financieros (
                        tipo, categoria, cliente_id, concepto, monto, metodo_pago, referencia_operacion, fecha_movimiento, estado, observaciones
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'CONFIRMADO', ?)
                ");
                $stmt->execute([$tipo, $categoria, $clienteId, $concepto, $monto, $metodo, $referencia, $fecha, $observaciones]);

                if ($clienteId) {
                    recalcularFinanzasCliente($pdo, $clienteId);
                }
                $_SESSION['flash_msg'] = "Movimiento financiero ({$tipo}: S/ " . number_format($monto, 2) . ") registrado en los Estados Financieros.";
            }
            break;

        case 'eliminar_movimiento':
            $id = (int)($_POST['movimiento_id'] ?? 0);
            $stmtMov = $pdo->prepare("SELECT cliente_id FROM movimientos_financieros WHERE id = ?");
            $stmtMov->execute([$id]);
            $mov = $stmtMov->fetch();

            $pdo->prepare("DELETE FROM movimientos_financieros WHERE id = ?")->execute([$id]);
            if (!empty($mov['cliente_id'])) {
                recalcularFinanzasCliente($pdo, (int)$mov['cliente_id']);
            }
            $_SESSION['flash_msg'] = "Movimiento financiero eliminado y saldos actualizados.";
            break;

        // ====================================================================
        // 3. GESTIÓN DE CAMPAÑAS DE DESCUENTO (SUBIDA DE IMAGEN DESDE PC)
        // ====================================================================
        case 'crear_campana':
            $titulo = trim($_POST['titulo'] ?? '');
            $subtitulo = trim($_POST['subtitulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $porcentaje = max(1, min(95, (int)($_POST['porcentaje_descuento'] ?? 20)));
            $codigo = strtoupper(trim($_POST['codigo_promo'] ?? 'IMPULSO2026'));
            $etiqueta = trim($_POST['etiqueta_oferta'] ?? '🔥 CAMPAÑA ACTIVA');
            $fechaInicio = $_POST['fecha_inicio'] ?: date('Y-m-d');
            $fechaFin = $_POST['fecha_fin'] ?: date('Y-m-d', strtotime('+30 days'));
            $bannerSup = isset($_POST['mostrar_banner_superior']) ? 1 : 0;

            $rutaSubida = subirArchivoDesdePC('imagen_archivo', 'campanas', ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif']);
            $imagenUrl = $rutaSubida ?: (trim($_POST['imagen_url_manual'] ?? '') ?: 'uploads/campanas/campana_octubre.svg');

            $stmt = $pdo->prepare("
                INSERT INTO campanas_descuento (
                    titulo, subtitulo, descripcion, porcentaje_descuento, codigo_promo, etiqueta_oferta,
                    imagen_url, fecha_inicio, fecha_fin, mostrar_banner_superior, mostrar_popup, estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'ACTIVA')
            ");
            $stmt->execute([
                $titulo, $subtitulo, $descripcion, $porcentaje, $codigo, $etiqueta,
                $imagenUrl, $fechaInicio, $fechaFin, $bannerSup
            ]);
            $_SESSION['flash_msg'] = "¡Campaña de descuento '{$titulo}' ({$porcentaje}% OFF) publicada en la página web!";
            break;

        case 'toggle_campana':
            $id = (int)($_POST['campana_id'] ?? 0);
            $pdo->prepare("UPDATE campanas_descuento SET estado = IF(estado='ACTIVA','INACTIVA','ACTIVA') WHERE id = ?")->execute([$id]);
            $_SESSION['flash_msg'] = "Estado de la campaña de descuento actualizado en la web.";
            break;

        case 'eliminar_campana':
            $id = (int)($_POST['campana_id'] ?? 0);
            $pdo->prepare("DELETE FROM campanas_descuento WHERE id = ?")->execute([$id]);
            $_SESSION['flash_msg'] = "Campaña eliminada.";
            break;

        // ====================================================================
        // 4. GESTIÓN DE VIDEOS PROMOCIONALES (SUBIDA DE VIDEO DESDE PC)
        // ====================================================================
        case 'crear_video':
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $categoria = trim($_POST['categoria'] ?? 'Metodología Oficial');
            $duracion = trim($_POST['duracion'] ?? '01:30');
            $destacado = isset($_POST['destacado']) ? 1 : 0;

            // Subir video desde la PC
            $videoSubido = subirArchivoDesdePC('video_archivo', 'videos', ['mp4', 'webm', 'ogg', 'mov']);
            $urlExterna = trim($_POST['video_url_externa'] ?? '');

            $tipoFuente = 'ARCHIVO';
            $videoUrl = 'uploads/videos/promo_impulso.mp4';
            if ($videoSubido) {
                $tipoFuente = 'ARCHIVO';
                $videoUrl = $videoSubido;
            } elseif ($urlExterna !== '') {
                $tipoFuente = (strpos($urlExterna, 'http') === 0) ? 'EXTERNO' : 'ARCHIVO';
                $videoUrl = $urlExterna;
            }

            // Subir portada / miniatura opcional desde la PC
            $posterSubido = subirArchivoDesdePC('poster_archivo', 'img', ['jpg', 'jpeg', 'png', 'webp', 'svg']);
            $posterUrl = $posterSubido ?: (trim($_POST['poster_url_actual'] ?? '') ?: 'uploads/img/pdf_img_2.jpg');

            if ($destacado === 1) {
                $pdo->exec("UPDATE videos_promocionales SET destacado = 0");
            }

            $stmt = $pdo->prepare("
                INSERT INTO videos_promocionales (
                    titulo, descripcion, categoria, tipo_fuente, video_url, poster_url, duracion, destacado, estado, orden
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVO', 1)
            ");
            $stmt->execute([$titulo, $descripcion, $categoria, $tipoFuente, $videoUrl, $posterUrl, $duracion, $destacado]);
            $_SESSION['flash_msg'] = "Video promocional '{$titulo}' subido y activado en la página pública.";
            break;

        case 'toggle_video':
            $id = (int)($_POST['video_id'] ?? 0);
            $pdo->prepare("UPDATE videos_promocionales SET estado = IF(estado='ACTIVO','INACTIVO','ACTIVO') WHERE id = ?")->execute([$id]);
            $_SESSION['flash_msg'] = "Visibilidad del video promocional actualizada.";
            break;

        case 'destacar_video':
            $id = (int)($_POST['video_id'] ?? 0);
            $pdo->exec("UPDATE videos_promocionales SET destacado = 0");
            $pdo->prepare("UPDATE videos_promocionales SET destacado = 1, estado = 'ACTIVO' WHERE id = ?")->execute([$id]);
            $_SESSION['flash_msg'] = "Video establecido como principal en el reproductor de la web.";
            break;

        case 'eliminar_video':
            $id = (int)($_POST['video_id'] ?? 0);
            $pdo->prepare("DELETE FROM videos_promocionales WHERE id = ?")->execute([$id]);
            $_SESSION['flash_msg'] = "Video promocional eliminado.";
            break;

        // ====================================================================
        // 5. GESTIÓN DE IMÁGENES DE LA WEB Y SERVICIOS DESDE BASE DE DATOS
        // ====================================================================
        case 'actualizar_imagen_web':
            $id = (int)($_POST['imagen_id'] ?? 0);
            $titulo = trim($_POST['titulo'] ?? '');
            $subtitulo = trim($_POST['subtitulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $etiqueta = trim($_POST['etiqueta'] ?? 'Institucional');

            $nuevaImg = subirArchivoDesdePC('imagen_archivo', 'img', ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif']);
            if ($nuevaImg) {
                $stmt = $pdo->prepare("UPDATE galeria_imagenes SET titulo = ?, subtitulo = ?, descripcion = ?, etiqueta = ?, imagen_url = ? WHERE id = ?");
                $stmt->execute([$titulo, $subtitulo, $descripcion, $etiqueta, $nuevaImg, $id]);

                // Si la imagen actualizada es el logo oficial, sincronizamos configuracion_web
                $stmtCheck = $pdo->prepare("SELECT clave_seccion FROM galeria_imagenes WHERE id = ?");
                $stmtCheck->execute([$id]);
                if ($stmtCheck->fetchColumn() === 'logo') {
                    $pdo->prepare("UPDATE configuracion_web SET valor = ? WHERE clave = 'logo_url'")->execute([$nuevaImg]);
                }
            } else {
                $stmt = $pdo->prepare("UPDATE galeria_imagenes SET titulo = ?, subtitulo = ?, descripcion = ?, etiqueta = ? WHERE id = ?");
                $stmt->execute([$titulo, $subtitulo, $descripcion, $etiqueta, $id]);
            }
            $_SESSION['flash_msg'] = "Imagen y textos de la sección actualizados en la Base de Datos.";
            break;

        case 'crear_imagen_web':
            $seccion = $_POST['clave_seccion'] ?? 'galeria_exito';
            $titulo = trim($_POST['titulo'] ?? '');
            $subtitulo = trim($_POST['subtitulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $etiqueta = trim($_POST['etiqueta'] ?? 'Caso de Éxito');

            $nuevaImg = subirArchivoDesdePC('imagen_archivo', 'img', ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif']);
            $imgUrl = $nuevaImg ?: 'uploads/img/pdf_img_6.jpg';

            $stmt = $pdo->prepare("
                INSERT INTO galeria_imagenes (clave_seccion, titulo, subtitulo, descripcion, imagen_url, etiqueta, orden, estado)
                VALUES (?, ?, ?, ?, ?, ?, 1, 'ACTIVO')
            ");
            $stmt->execute([$seccion, $titulo, $subtitulo, $descripcion, $imgUrl, $etiqueta]);
            $_SESSION['flash_msg'] = "Nueva imagen subida a la Base de Datos y publicada en la web.";
            break;

        case 'eliminar_imagen_web':
            $id = (int)($_POST['imagen_id'] ?? 0);
            $pdo->prepare("DELETE FROM galeria_imagenes WHERE id = ?")->execute([$id]);
            $_SESSION['flash_msg'] = "Imagen eliminada de la galería.";
            break;

        case 'actualizar_servicio':
            $id = (int)($_POST['servicio_id'] ?? 0);
            $titulo = trim($_POST['titulo'] ?? '');
            $resumen = trim($_POST['resumen'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $tarifa = trim($_POST['tarifa_referencial'] ?? '');
            $nuevaImg = subirArchivoDesdePC('imagen_archivo', 'img', ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif']);

            if ($nuevaImg) {
                $stmt = $pdo->prepare("UPDATE servicios SET titulo = ?, resumen = ?, descripcion = ?, tarifa_referencial = ?, imagen_url = ? WHERE id = ?");
                $stmt->execute([$titulo, $resumen, $descripcion, $tarifa, $nuevaImg, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE servicios SET titulo = ?, resumen = ?, descripcion = ?, tarifa_referencial = ? WHERE id = ?");
                $stmt->execute([$titulo, $resumen, $descripcion, $tarifa, $id]);
            }
            $_SESSION['flash_msg'] = "Servicio académico actualizado en la Base de Datos.";
            break;

        // ====================================================================
        // 6. BANDEJA DE SOLICITUDES WEB -> CONVERTIR EN CLIENTE ACTIVO
        // ====================================================================
        case 'convertir_solicitud':
            $solId = (int)($_POST['solicitud_id'] ?? 0);
            $stmtSol = $pdo->prepare("SELECT * FROM solicitudes_web WHERE id = ?");
            $stmtSol->execute([$solId]);
            $sol = $stmtSol->fetch();

            if ($sol) {
                $partes = explode(' ', trim($sol['nombre_completo']), 2);
                $nombres = $partes[0] ?? $sol['nombre_completo'];
                $apellidos = $partes[1] ?? 'Pendiente';
                $totalRows = (int)$pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn() + 1;
                $codigo = sprintf('IA-2026-%03d', $totalRows + mt_rand(10, 99));

                $stmtIns = $pdo->prepare("
                    INSERT INTO clientes (
                        codigo_cliente, nombres, apellidos, telefono, email, universidad, carrera, nivel_academico,
                        servicio_contratado, titulo_tesis, asesor_asignado, estado_tesis, porcentaje_avance,
                        costo_total, descuento_aplicado, monto_final, total_abonado, saldo_pendiente, numero_cuotas,
                        estado_financiero, fecha_inicio, fecha_entrega_estimada, notas_internas
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Por asignar', 'Diagnóstico Inicial', 10, 2000.00, 0.00, 2000.00, 0.00, 2000.00, 3, 'PENDIENTE', ?, ?, ?)
                ");
                $stmtIns->execute([
                    $codigo,
                    $nombres,
                    $apellidos,
                    $sol['telefono'],
                    $sol['email'],
                    $sol['universidad'] ?: 'Por definir',
                    $sol['carrera'] ?: 'Por definir',
                    in_array($sol['nivel_academico'], ['Pregrado', 'Maestría', 'Doctorado', 'Segunda Especialidad']) ? $sol['nivel_academico'] : 'Pregrado',
                    $sol['servicio_interes'],
                    'En definición - ' . ($sol['mensaje'] ?: 'Diagnóstico inicial'),
                    date('Y-m-d'),
                    date('Y-m-d', strtotime('+30 days')),
                    'Convertido desde solicitud web #' . $sol['id'] . ($sol['codigo_promo'] ? ' (Código promo: ' . $sol['codigo_promo'] . ')' : '')
                ]);

                $pdo->prepare("UPDATE solicitudes_web SET estado = 'CONVERTIDO' WHERE id = ?")->execute([$solId]);
                $_SESSION['flash_msg'] = "Solicitud de {$sol['nombre_completo']} convertida en Cliente Activo ({$codigo}).";
            }
            break;

        case 'cambiar_estado_solicitud':
            $solId = (int)($_POST['solicitud_id'] ?? 0);
            $nuevoEst = $_POST['nuevo_estado'] ?? 'EN_COORDINACION';
            $pdo->prepare("UPDATE solicitudes_web SET estado = ? WHERE id = ?")->execute([$nuevoEst, $solId]);
            $_SESSION['flash_msg'] = "Estado de la solicitud actualizado.";
            break;

        // ====================================================================
        // 7. CONTROL DE PROYECTOS (PENDIENTES, ENTREGAS, CANCELACIÓN Y AVANCES)
        // ====================================================================
        case 'crear_seguimiento_proyecto':
            $estadoProy = in_array($_POST['estado_proyecto'] ?? '', ['PENDIENTE','ENTREGADO','CANCELADO']) ? $_POST['estado_proyecto'] : 'PENDIENTE';
            $fecha = $_POST['fecha'] ?: date('Y-m-d');
            $universidad = trim($_POST['universidad'] ?? 'Pontificia Universidad Católica del Perú (PUCP)');
            $clienteNombre = trim($_POST['cliente_nombre'] ?? '');
            $celular = trim($_POST['celular'] ?? '');
            $areaCarrera = trim($_POST['area_carrera'] ?? 'Contabilidad');
            $areaAcademica = trim($_POST['area_academica'] ?? 'Bachiller');
            $listaAvance = trim($_POST['lista_avance'] ?? 'Realidad problemática');
            $descAct = trim($_POST['descripcion_actividad'] ?? '');
            $evidencias = trim($_POST['envio_evidencias'] ?? 'Sí, en fecha');
            $horaIni = trim($_POST['hora_inicio'] ?? '09:00');
            $horaFin = trim($_POST['hora_fin'] ?? '13:00');
            $supervision = trim($_POST['supervision_investigacion'] ?? 'En supervisión');
            $justificacion = trim($_POST['justificacion_demora'] ?? 'Sin demora');

            $stmt = $pdo->prepare("
                INSERT INTO seguimiento_proyectos (
                    estado_proyecto, fecha, universidad, cliente_nombre, celular, area_carrera, area_academica,
                    lista_avance, descripcion_actividad, envio_evidencias, hora_inicio, hora_fin,
                    supervision_investigacion, justificacion_demora
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $estadoProy, $fecha, $universidad, $clienteNombre, $celular, $areaCarrera, $areaAcademica,
                $listaAvance, $descAct, $evidencias, $horaIni, $horaFin, $supervision, $justificacion
            ]);
            $_SESSION['flash_msg'] = "Avance de proyecto ({$estadoProy}) para {$clienteNombre} registrado.";
            break;

        case 'cambiar_estado_proyecto':
            $id = (int)($_POST['proyecto_id'] ?? 0);
            $nuevoEst = in_array($_POST['nuevo_estado'] ?? '', ['PENDIENTE','ENTREGADO','CANCELADO']) ? $_POST['nuevo_estado'] : 'ENTREGADO';
            $pdo->prepare("UPDATE seguimiento_proyectos SET estado_proyecto = ? WHERE id = ?")->execute([$nuevoEst, $id]);
            $_SESSION['flash_msg'] = "Proyecto movido a estado: {$nuevoEst}.";
            break;

        case 'eliminar_seguimiento_proyecto':
            $id = (int)($_POST['proyecto_id'] ?? 0);
            $pdo->prepare("DELETE FROM seguimiento_proyectos WHERE id = ?")->execute([$id]);
            $_SESSION['flash_msg'] = "Registro de proyecto eliminado.";
            break;

        // ====================================================================
        // 8. CONTROL DE INGRESO/SALIDA DE ACTIVIDADES, GASTOS Y PROVEEDORES
        // ====================================================================
        case 'crear_registro_actividad_fin':
            $tipoReg = in_array($_POST['tipo_registro'] ?? '', ['INGRESO_ACTIVIDAD','SALIDA_ACTIVIDAD','GASTO_FINANCIERO','PROVEEDOR'])
                ? $_POST['tipo_registro'] : 'INGRESO_ACTIVIDAD';
            $totalExist = (int)$pdo->query("SELECT COUNT(*) FROM registro_actividades_fin WHERE tipo_registro = " . $pdo->quote($tipoReg))->fetchColumn() + 1;
            $prefijo = $tipoReg === 'GASTO_FINANCIERO' ? 'FC-000-' : ($tipoReg === 'PROVEEDOR' ? 'PR-000-' : ($tipoReg === 'SALIDA_ACTIVIDAD' ? 'SA-000-' : 'FV-000-'));
            $nroLista = trim($_POST['nro_lista'] ?? '') ?: sprintf('%s%03d', $prefijo, $totalExist);
            $fecha = $_POST['fecha'] ?: date('Y-m-d');
            $areaCarrera = trim($_POST['area_carrera'] ?? 'Contabilidad');
            $areaAcademica = trim($_POST['area_academica'] ?? 'Bachiller');
            $codAct = trim($_POST['codigo_actividad'] ?? 'C0101');
            $descripcion = trim($_POST['descripcion'] ?? 'Tesis');
            $ingresoUnit = (float)($_POST['ingreso_unitario'] ?? 0);
            $cantidad = max(1, (int)($_POST['cantidad'] ?? 1));
            $gastoHerr = (float)($_POST['gasto_herramientas'] ?? 0);
            $montoTotal = ($tipoReg === 'GASTO_FINANCIERO' || $tipoReg === 'PROVEEDOR')
                ? ($gastoHerr > 0 ? $gastoHerr : $ingresoUnit * $cantidad)
                : ($ingresoUnit * $cantidad);
            $contacto = trim($_POST['proveedor_contacto'] ?? '');
            $cuenta = trim($_POST['cuenta_pago'] ?? 'BCP');

            $stmt = $pdo->prepare("
                INSERT INTO registro_actividades_fin (
                    tipo_registro, nro_lista, fecha, area_carrera, area_academica, codigo_actividad,
                    descripcion, ingreso_unitario, cantidad, monto_total, gasto_herramientas, proveedor_contacto, cuenta_pago
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $tipoReg, $nroLista, $fecha, $areaCarrera, $areaAcademica, $codAct,
                $descripcion, $ingresoUnit, $cantidad, $montoTotal, $gastoHerr, $contacto, $cuenta
            ]);
            $_SESSION['flash_msg'] = "Registro {$nroLista} ({$descripcion}) guardado correctamente.";
            break;

        case 'eliminar_registro_actividad_fin':
            $id = (int)($_POST['registro_id'] ?? 0);
            $pdo->prepare("DELETE FROM registro_actividades_fin WHERE id = ?")->execute([$id]);
            $_SESSION['flash_msg'] = "Registro de actividad eliminado.";
            break;

        case 'guardar_actividad_catalogo':
            $codigo = strtoupper(trim($_POST['codigo'] ?? 'C0108'));
            $descripcion = trim($_POST['descripcion'] ?? '');
            $ingresoBase = (float)($_POST['ingreso_base'] ?? 0);
            $cuenta = trim($_POST['cuenta_corriente'] ?? 'BCP');
            if ($codigo !== '' && $descripcion !== '') {
                $stmt = $pdo->prepare("
                    INSERT INTO catalogo_actividades (codigo, descripcion, ingreso_base, cuenta_corriente)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion), ingreso_base = VALUES(ingreso_base), cuenta_corriente = VALUES(cuenta_corriente)
                ");
                $stmt->execute([$codigo, $descripcion, $ingresoBase, $cuenta]);
                $_SESSION['flash_msg'] = "Actividad {$codigo} ({$descripcion} - S/ " . number_format($ingresoBase, 2) . ") guardada en el catálogo.";
            }
            break;

        // ====================================================================
        // 9. DOCUMENTACIÓN: BOLETAS, FACTURAS Y COTIZACIONES + RUC ACTUALIZABLE
        // ====================================================================
        case 'actualizar_ruc_facturacion':
            $ruc = trim($_POST['ruc_empresa'] ?? 'EN TRÁMITE');
            $razon = trim($_POST['razon_social_facturacion'] ?? 'IMPULSO ACADÉMICO');
            $dir = trim($_POST['direccion_fiscal'] ?? 'Lima – Perú');
            $stmtUp = $pdo->prepare("
                INSERT INTO configuracion_web (clave, valor, descripcion) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE valor = VALUES(valor)
            ");
            $stmtUp->execute(['ruc_empresa', $ruc, 'RUC para Boletas y Facturas']);
            $stmtUp->execute(['razon_social_facturacion', $razon, 'Razón social para comprobantes']);
            $stmtUp->execute(['direccion_fiscal', $dir, 'Dirección fiscal']);
            $_SESSION['flash_msg'] = "Datos de RUC ({$ruc}) y Razón Social actualizados para todas las Boletas, Facturas y Cotizaciones.";
            break;

        case 'crear_comprobante_pago':
            $tipoDoc = in_array($_POST['tipo_doc'] ?? '', ['BOLETA','FACTURA','COTIZACION']) ? $_POST['tipo_doc'] : 'BOLETA';
            $totalDocs = (int)$pdo->query("SELECT COUNT(*) FROM comprobantes_pago WHERE tipo_doc = " . $pdo->quote($tipoDoc))->fetchColumn() + 1;
            $pref = $tipoDoc === 'FACTURA' ? 'FC-000-' : ($tipoDoc === 'COTIZACION' ? 'CT-000-' : 'BV-000-');
            $nroDoc = trim($_POST['nro_documento'] ?? '') ?: sprintf('%s%03d', $pref, $totalDocs);
            $fecha = $_POST['fecha_emision'] ?: date('Y-m-d');
            $cliNombre = trim($_POST['cliente_nombre'] ?? '');
            $docTipo = trim($_POST['cliente_doc_tipo'] ?? ($tipoDoc === 'FACTURA' ? 'RUC' : 'DNI'));
            $docNum = trim($_POST['cliente_doc_numero'] ?? '');
            $celular = trim($_POST['cliente_celular'] ?? '');
            $uni = trim($_POST['universidad'] ?? '');
            $areaCarrera = trim($_POST['area_carrera'] ?? 'Contabilidad');
            $areaAcad = trim($_POST['area_academica'] ?? 'Bachiller');
            $codAct = trim($_POST['codigo_actividad'] ?? 'C0101');
            $desc = trim($_POST['descripcion'] ?? 'Tesis');
            $cant = max(1, (int)($_POST['cantidad'] ?? 1));
            $precioUnit = (float)($_POST['precio_unitario'] ?? 0);
            $importeTotal = $cant * $precioUnit;
            $conIgv = isset($_POST['desglosar_igv']) && $_POST['desglosar_igv'] === '1';
            $subtotal = $conIgv ? round($importeTotal / 1.18, 2) : $importeTotal;
            $igv = $conIgv ? round($importeTotal - $subtotal, 2) : 0.00;
            $cuenta = trim($_POST['cuenta_pago'] ?? 'BCP');
            $estadoDoc = $_POST['estado_doc'] ?? 'PAGADO';
            $obs = trim($_POST['observaciones'] ?? '');

            $stmt = $pdo->prepare("
                INSERT INTO comprobantes_pago (
                    tipo_doc, nro_documento, fecha_emision, cliente_nombre, cliente_doc_tipo, cliente_doc_numero,
                    cliente_celular, universidad, area_carrera, area_academica, codigo_actividad, descripcion,
                    cantidad, precio_unitario, subtotal, igv, importe_total, cuenta_pago, estado_doc, observaciones
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $tipoDoc, $nroDoc, $fecha, $cliNombre, $docTipo, $docNum,
                $celular, $uni, $areaCarrera, $areaAcad, $codAct, $desc,
                $cant, $precioUnit, $subtotal, $igv, $importeTotal, $cuenta, $estadoDoc, $obs
            ]);
            $_SESSION['flash_msg'] = "{$tipoDoc} {$nroDoc} para {$cliNombre} (S/ " . number_format($importeTotal, 2) . ") generada exitosamente.";
            break;

        case 'eliminar_comprobante_pago':
            $id = (int)($_POST['comprobante_id'] ?? 0);
            $pdo->prepare("DELETE FROM comprobantes_pago WHERE id = ?")->execute([$id]);
            $_SESSION['flash_msg'] = "Comprobante eliminado.";
            break;

        // ====================================================================
        // 10. EMPLEADOS Y CONTROL DE ASISTENCIA / SUPERVISIÓN
        // ====================================================================
        case 'crear_empleado_control':
            $nombre = trim($_POST['nombre_trabajador'] ?? '');
            $area = ($_POST['area_trabajador'] ?? '') === 'COMERCIAL' ? 'COMERCIAL' : 'INVESTIGACION';
            $celular = trim($_POST['celular'] ?? '');
            $uni = trim($_POST['universidad_asignada'] ?? 'Pontificia Universidad Católica del Perú (PUCP)');
            $descAct = trim($_POST['descripcion_actividad'] ?? '');
            $evid = trim($_POST['envio_evidencias'] ?? 'Sí, en fecha');
            $horaIni = trim($_POST['hora_inicio'] ?? '08:00');
            $horaFin = trim($_POST['hora_fin'] ?? '16:00');
            $sup = trim($_POST['supervision_jefatura'] ?? 'Aprobado por Jefatura');
            $just = trim($_POST['justificacion_demora'] ?? 'Sin demora');
            $s1 = max(0, min(7, (int)($_POST['semana_1_dias'] ?? 5)));
            $s2 = max(0, min(7, (int)($_POST['semana_2_dias'] ?? 5)));
            $s3 = max(0, min(7, (int)($_POST['semana_3_dias'] ?? 5)));
            $s4 = max(0, min(7, (int)($_POST['semana_4_dias'] ?? 5)));
            $totalDias = $s1 + $s2 + $s3 + $s4;
            $horasTotales = max(1, (int)($_POST['horas_totales'] ?? ($totalDias * 8)));
            $importeMensual = (float)($_POST['importe_mensual'] ?? ($area === 'INVESTIGACION' ? 1800.00 : 1500.00));
            $importeHora = $horasTotales > 0 ? round($importeMensual / $horasTotales, 2) : 0.00;
            $fecha = $_POST['fecha_registro'] ?: date('Y-m-d');

            $stmt = $pdo->prepare("
                INSERT INTO empleados_control (
                    nombre_trabajador, area_trabajador, celular, universidad_asignada, descripcion_actividad,
                    envio_evidencias, hora_inicio, hora_fin, supervision_jefatura, justificacion_demora,
                    semana_1_dias, semana_2_dias, semana_3_dias, semana_4_dias, total_dias_asistidos,
                    horas_totales, importe_hora, importe_mensual, fecha_registro
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $nombre, $area, $celular, $uni, $descAct,
                $evid, $horaIni, $horaFin, $sup, $just,
                $s1, $s2, $s3, $s4, $totalDias,
                $horasTotales, $importeHora, $importeMensual, $fecha
            ]);
            $_SESSION['flash_msg'] = "Registro de empleado y asistencia ({$nombre} - {$area}) guardado.";
            break;

        case 'eliminar_empleado_control':
            $id = (int)($_POST['empleado_id'] ?? 0);
            $pdo->prepare("DELETE FROM empleados_control WHERE id = ?")->execute([$id]);
            $_SESSION['flash_msg'] = "Registro de empleado eliminado.";
            break;
    }
} catch (Throwable $e) {
    $_SESSION['flash_error'] = "Error al procesar la acción: " . $e->getMessage();
}

header('Location: index.php?tab=' . urlencode($tabRetorno));
exit;
