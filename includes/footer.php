    <!-- ==================== FOOTER COMPARTIDO MULTIPÁGINA ==================== -->
    <footer class="site-footer">
        <div class="footer-grid">
            <div>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
                    <img src="<?= htmlspecialchars($imgLogo) ?>" alt="Logo Impulso Académico" style="width:58px;height:58px;border-radius:50%;border:2px solid #0074F0;object-fit:cover;">
                    <div>
                        <strong style="color:#fff;font-size:1.22rem;display:block;">Impulso <span style="color:#3898FF;">Académico</span></strong>
                        <small style="color:#85C1FF;font-size:0.75rem;"><?= htmlspecialchars($cfg['subtitulo_empresa'] ?? '') ?></small>
                    </div>
                </div>
                <p style="font-size:0.9rem;color:#9EC3F0;margin-bottom:12px;">
                    <strong><?= htmlspecialchars($cfg['lema_oficial'] ?? '¡Tu éxito es nuestro impulso!') ?></strong><br>
                    Acompañamiento profesional para pregrado y posgrado. Lima – 2026.
                </p>
            </div>

            <div class="footer-col">
                <h4>Páginas del Sitio</h4>
                <ul class="footer-links">
                    <li><a href="index.php"><i class="fa-solid fa-angle-right"></i> Inicio</a></li>
                    <li><a href="nosotros.php"><i class="fa-solid fa-angle-right"></i> ¿Quiénes Somos? y Misión</a></li>
                    <li><a href="servicios.php"><i class="fa-solid fa-angle-right"></i> Servicios y Especialidades</a></li>
                    <li><a href="metodologia.php"><i class="fa-solid fa-angle-right"></i> Metodología y Modalidad</a></li>
                    <li><a href="tarifas.php"><i class="fa-solid fa-angle-right"></i> Tarifas de Servicios</a></li>
                    <li><a href="contacto.php"><i class="fa-solid fa-angle-right"></i> Contáctanos</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Contacto Oficial</h4>
                <ul class="footer-links">
                    <li><a href="<?= htmlspecialchars($whatsappLink1) ?>" target="_blank"><i class="fa-brands fa-whatsapp"></i> WhatsApp 1: 953 697 434</a></li>
                    <li><a href="<?= htmlspecialchars($whatsappLink2) ?>" target="_blank"><i class="fa-brands fa-whatsapp"></i> WhatsApp 2: 900 439 160</a></li>
                    <li><a href="contacto.php"><i class="fa-brands fa-facebook"></i> Facebook / Instagram / TikTok</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Sistema de Control Interno</h4>
                <p style="font-size:0.86rem;color:#9EC3F0;margin-bottom:14px;">
                    Panel administrativo para el control de clientes, estados financieros, subida de videos promocionales, imágenes y campañas de descuento.
                </p>
                <a href="admin/index.php" class="btn btn-outline-light" style="width:100%;">
                    <i class="fa-solid fa-lock"></i> Ingresar al Panel de Control
                </a>
            </div>
        </div>

        <div style="max-width:1280px;margin:24px auto 0;text-align:center;font-size:0.84rem;color:#6E97C8;">
            &copy; <?= date('Y') ?> <strong>IMPULSO ACADÉMICO - Sistema de Gestión y Apoyo a la Investigación</strong> (Lima – 2026). ¡Tu éxito es nuestro impulso!
        </div>
    </footer>

    <!-- Botón flotante de WhatsApp al número oficial 953697434 -->
    <a href="<?= htmlspecialchars($whatsappLink1) ?>" target="_blank" class="whatsapp-float" title="WhatsApp Oficial: 953 697 434">
        <i class="fa-brands fa-whatsapp"></i>
    </a>

    <script src="assets/js/main.js"></script>
</body>
</html>
