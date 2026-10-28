-- ============================================================================
-- BASE DE DATOS: IMPULSO ACADÉMICO - SISTEMA DE GESTIÓN Y APOYO A LA INVESTIGACIÓN
-- Presentado por: IMPULSO ACADÉMICO (Lima - 2026)
-- Motor: MySQL / MariaDB (Compatible con Laragon / XAMPP)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `impulso_academico` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `impulso_academico`;

-- 1. Tabla de Administradores
CREATE TABLE IF NOT EXISTS `usuarios_admin` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `nombre` VARCHAR(120) NOT NULL,
    `cargo` VARCHAR(80) DEFAULT 'Dirección General',
    `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabla de Configuración General (Quiénes somos, Misión, Visión, Contacto, Redes)
CREATE TABLE IF NOT EXISTS `configuracion_web` (
    `clave` VARCHAR(60) PRIMARY KEY,
    `valor` TEXT NOT NULL,
    `descripcion` VARCHAR(160) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabla de Imágenes Dinámicas de la Web (Todas las imágenes se jalan de la BD)
CREATE TABLE IF NOT EXISTS `galeria_imagenes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `clave_seccion` VARCHAR(50) NOT NULL, -- 'logo', 'hero_principal', 'quienes_somos', 'mision_vision', 'servicios_banner', 'especialidades_banner', 'metodologia_banner', 'contacto_banner', 'galeria_exito'
    `titulo` VARCHAR(150) NOT NULL,
    `subtitulo` VARCHAR(200) DEFAULT NULL,
    `descripcion` TEXT DEFAULT NULL,
    `imagen_url` VARCHAR(255) NOT NULL,
    `etiqueta` VARCHAR(60) DEFAULT 'Institucional',
    `orden` INT DEFAULT 1,
    `estado` ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabla de Servicios que Ofrecemos (Página 4 del PDF + Imágenes desde BD)
CREATE TABLE IF NOT EXISTS `servicios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `numero_paso` VARCHAR(10) DEFAULT '1',
    `titulo` VARCHAR(140) NOT NULL,
    `resumen` VARCHAR(240) NOT NULL,
    `descripcion` TEXT NOT NULL,
    `categoria` VARCHAR(60) DEFAULT 'Investigación',
    `tarifa_referencial` VARCHAR(80) DEFAULT 'Consultar',
    `imagen_url` VARCHAR(255) NOT NULL,
    `icono` VARCHAR(60) DEFAULT 'fa-graduation-cap',
    `incluye_items` TEXT DEFAULT NULL,
    `destacado` TINYINT(1) DEFAULT 0,
    `orden` INT DEFAULT 1,
    `estado` ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabla de Tarifas de Nuestros Servicios (Página 9 del PDF - Sin pasarela de pago, referencial para coordinación interna)
CREATE TABLE IF NOT EXISTS `tarifas_servicios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `servicio_nombre` VARCHAR(180) NOT NULL,
    `rango_inversion` VARCHAR(80) NOT NULL,
    `detalle` VARCHAR(220) DEFAULT NULL,
    `orden` INT DEFAULT 1,
    `estado` ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabla de Campañas de Descuento y Promociones (Administrables desde el Panel)
CREATE TABLE IF NOT EXISTS `campanas_descuento` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(160) NOT NULL,
    `subtitulo` VARCHAR(220) DEFAULT NULL,
    `descripcion` TEXT NOT NULL,
    `porcentaje_descuento` INT NOT NULL DEFAULT 20,
    `codigo_promo` VARCHAR(40) DEFAULT NULL,
    `etiqueta_oferta` VARCHAR(80) DEFAULT 'OFERTA POR TIEMPO LIMITADO',
    `imagen_url` VARCHAR(255) NOT NULL,
    `fecha_inicio` DATE DEFAULT NULL,
    `fecha_fin` DATE DEFAULT NULL,
    `mostrar_banner_superior` TINYINT(1) DEFAULT 1,
    `mostrar_popup` TINYINT(1) DEFAULT 0,
    `estado` ENUM('ACTIVA', 'INACTIVA') DEFAULT 'ACTIVA',
    `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Tabla de Videos Promocionales (Administrables desde el Panel)
CREATE TABLE IF NOT EXISTS `videos_promocionales` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(160) NOT NULL,
    `descripcion` TEXT DEFAULT NULL,
    `categoria` VARCHAR(80) DEFAULT 'Institucional',
    `tipo_fuente` ENUM('ARCHIVO', 'EXTERNO') DEFAULT 'ARCHIVO',
    `video_url` VARCHAR(255) NOT NULL,
    `poster_url` VARCHAR(255) DEFAULT 'uploads/img/pdf_img_2.jpg',
    `duracion` VARCHAR(30) DEFAULT '01:30',
    `destacado` TINYINT(1) DEFAULT 0,
    `estado` ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    `orden` INT DEFAULT 1,
    `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Tabla de Clientes y Control de Proyectos de Tesis
CREATE TABLE IF NOT EXISTS `clientes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo_cliente` VARCHAR(30) NOT NULL UNIQUE,
    `nombres` VARCHAR(100) NOT NULL,
    `apellidos` VARCHAR(100) NOT NULL,
    `dni` VARCHAR(20) DEFAULT NULL,
    `telefono` VARCHAR(30) NOT NULL,
    `email` VARCHAR(120) DEFAULT NULL,
    `universidad` VARCHAR(140) NOT NULL,
    `carrera` VARCHAR(140) NOT NULL,
    `nivel_academico` ENUM('Pregrado', 'Maestría', 'Doctorado', 'Segunda Especialidad') DEFAULT 'Pregrado',
    `servicio_contratado` VARCHAR(150) NOT NULL,
    `titulo_tesis` TEXT DEFAULT NULL,
    `asesor_asignado` VARCHAR(120) DEFAULT 'Por asignar',
    `estado_tesis` ENUM('Diagnóstico Inicial', 'Plan Personalizado', 'Marco Teórico', 'Análisis Estadístico', 'Redacción APA 7 / Turnitin', 'Levantando Observaciones', 'Listo para Sustentar', 'Sustentado / Aprobado') DEFAULT 'Diagnóstico Inicial',
    `porcentaje_avance` INT DEFAULT 15,
    `costo_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `descuento_aplicado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `monto_final` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_abonado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `saldo_pendiente` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `numero_cuotas` INT DEFAULT 3,
    `estado_financiero` ENUM('AL_DIA', 'PENDIENTE', 'PAGADO_TOTAL', 'VENCIDO') DEFAULT 'AL_DIA',
    `fecha_inicio` DATE DEFAULT NULL,
    `fecha_entrega_estimada` DATE DEFAULT NULL,
    `notas_internas` TEXT DEFAULT NULL,
    `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Tabla de Movimientos Financieros (Ingresos de Clientes y Egresos Operativos)
CREATE TABLE IF NOT EXISTS `movimientos_financieros` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tipo` ENUM('INGRESO', 'EGRESO') NOT NULL,
    `categoria` VARCHAR(100) NOT NULL,
    `cliente_id` INT DEFAULT NULL,
    `concepto` VARCHAR(220) NOT NULL,
    `monto` DECIMAL(10,2) NOT NULL,
    `metodo_pago` VARCHAR(60) DEFAULT 'Transferencia BCP',
    `referencia_operacion` VARCHAR(80) DEFAULT NULL,
    `fecha_movimiento` DATE NOT NULL,
    `estado` ENUM('CONFIRMADO', 'PENDIENTE', 'ANULADO') DEFAULT 'CONFIRMADO',
    `comprobante_url` VARCHAR(255) DEFAULT NULL,
    `observaciones` TEXT DEFAULT NULL,
    `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_movimiento_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Tabla de Solicitudes / Consultas desde la Web (Sin pasarela de pagos, coordinación interna)
CREATE TABLE IF NOT EXISTS `solicitudes_web` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre_completo` VARCHAR(150) NOT NULL,
    `telefono` VARCHAR(40) NOT NULL,
    `email` VARCHAR(120) DEFAULT NULL,
    `universidad` VARCHAR(140) DEFAULT NULL,
    `carrera` VARCHAR(140) DEFAULT NULL,
    `nivel_academico` VARCHAR(60) DEFAULT 'Pregrado',
    `servicio_interes` VARCHAR(150) NOT NULL,
    `codigo_promo` VARCHAR(50) DEFAULT NULL,
    `mensaje` TEXT DEFAULT NULL,
    `estado` ENUM('NUEVO', 'EN_COORDINACION', 'CONVERTIDO', 'DESCARTADO') DEFAULT 'NUEVO',
    `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
