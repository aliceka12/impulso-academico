<?php
/**
 * IMPULSO ACADÉMICO - Mapa del Sitio Dinámico XML para Google Search Console
 */
require_once __DIR__ . '/config/db.php';
$pdo = getPDO();
$cfg = obtenerConfiguracion($pdo);

$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https://' : 'http://';
$hostActual = $_SERVER['HTTP_HOST'] ?? 'impulsoacademico.pe';
$dominioBase = !empty($cfg['dominio_oficial']) ? rtrim($cfg['dominio_oficial'], '/') : rtrim($protocolo . $hostActual, '/');

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?= htmlspecialchars($dominioBase) ?>/</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= htmlspecialchars($dominioBase) ?>/servicios.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?= htmlspecialchars($dominioBase) ?>/tarifas.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.85</priority>
    </url>
    <url>
        <loc><?= htmlspecialchars($dominioBase) ?>/contacto.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.85</priority>
    </url>
    <url>
        <loc><?= htmlspecialchars($dominioBase) ?>/nosotros.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= htmlspecialchars($dominioBase) ?>/metodologia.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
</urlset>
