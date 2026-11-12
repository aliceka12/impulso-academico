    <!-- ==================== FOOTER COMPARTIDO MULTIPÁGINA ==================== -->
    <footer class="site-footer">
        <div class="footer-grid">
            <div>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
                    <img src="<?= htmlspecialchars($imgLogo ?? 'uploads/img/logo.jpg') ?>" alt="Logo Impulso Académico" width="58" height="58" loading="lazy" decoding="async" style="width:58px;height:58px;border-radius:50%;border:2px solid #0074F0;object-fit:cover;">
                    <div>
                        <strong style="color:#fff;font-size:1.22rem;display:block;">Impulso <span style="color:#3898FF;">Académico</span></strong>
                        <small style="color:#85C1FF;font-size:0.75rem;"><?= htmlspecialchars($cfg['subtitulo_empresa'] ?? 'Sistema de Gestión y Apoyo a la Investigación') ?></small>
                    </div>
                </div>
                <p style="font-size:0.9rem;color:#9EC3F0;margin-bottom:12px;">
                    <strong><?= htmlspecialchars($cfg['lema_oficial'] ?? '¡Tu éxito es nuestro impulso!') ?></strong><br>
                    Acompañamiento profesional para pregrado y posgrado.
                </p>
                <div style="display:flex;gap:10px;margin-top:10px;">
                    <a href="https://www.facebook.com/profile.php?id=61586263905668" target="_blank" rel="noopener noreferrer" title="Facebook Oficial" style="width:38px;height:38px;border-radius:10px;background:rgba(24,119,242,0.22);border:1px solid rgba(56,152,255,0.45);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:1.05rem;text-decoration:none;">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/impulso_academico2026/" target="_blank" rel="noopener noreferrer" title="Instagram Oficial" style="width:38px;height:38px;border-radius:10px;background:rgba(225,48,108,0.22);border:1px solid rgba(244,114,182,0.45);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;text-decoration:none;">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="https://www.tiktok.com/@impulsoacademico00" target="_blank" rel="noopener noreferrer" title="TikTok Oficial" style="width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,0.12);border:1px solid rgba(148,163,184,0.45);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:1.05rem;text-decoration:none;">
                        <i class="fa-brands fa-tiktok"></i>
                    </a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Páginas del Sitio</h4>
                <ul class="footer-links">
                    <li><a href="./"><i class="fa-solid fa-angle-right"></i> Inicio</a></li>
                    <li><a href="nosotros"><i class="fa-solid fa-angle-right"></i> ¿Quiénes Somos? y Misión</a></li>
                    <li><a href="servicios"><i class="fa-solid fa-angle-right"></i> Servicios y Especialidades</a></li>
                    <li><a href="metodologia"><i class="fa-solid fa-angle-right"></i> Metodología y Modalidad</a></li>
                    <li><a href="tarifas"><i class="fa-solid fa-angle-right"></i> Tarifas de Servicios</a></li>
                    <li><a href="contacto"><i class="fa-solid fa-angle-right"></i> Contáctanos</a></li>
                    <li><a href="terminos"><i class="fa-solid fa-scale-balanced"></i> Términos y Condiciones</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Contacto y Redes Oficiales</h4>
                <ul class="footer-links">
                    <li><a href="<?= htmlspecialchars($whatsappLink1) ?>" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp" style="color:#10B981;"></i> WhatsApp 1: 953 697 434</a></li>
                    <li><a href="<?= htmlspecialchars($whatsappLink2) ?>" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp" style="color:#10B981;"></i> WhatsApp 2: 900 439 160</a></li>
                    <li><a href="https://www.facebook.com/profile.php?id=61586263905668" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-facebook" style="color:#3898FF;"></i> Facebook Oficial</a></li>
                    <li><a href="https://www.instagram.com/impulso_academico2026/" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-instagram" style="color:#F472B6;"></i> Instagram @impulso_academico2026</a></li>
                    <li><a href="https://www.tiktok.com/@impulsoacademico00" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-tiktok" style="color:#E2E8F0;"></i> TikTok @impulsoacademico00</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Sistema de Control Interno</h4>
                <p style="font-size:0.86rem;color:#9EC3F0;margin-bottom:14px;">
                    Panel administrativo para el control de clientes, estados financieros, subida de videos promocionales, imágenes y campañas de descuento.
                </p>
                <a href="admin/" class="btn btn-outline-light" style="width:100%;">
                    <i class="fa-solid fa-lock"></i> Ingresar al Panel de Control
                </a>
            </div>
        </div>

        <div style="max-width:1280px;margin:24px auto 0;text-align:center;font-size:0.84rem;color:#6E97C8;">
            &copy; <?= date('Y') ?> <strong>IMPULSO ACADÉMICO - Sistema de Gestión y Apoyo a la Investigación</strong>. ¡Tu éxito es nuestro impulso!
            &nbsp;•&nbsp; <a href="terminos" style="color:#85C1FF;text-decoration:underline;font-weight:700;">Términos, Condiciones y Confidencialidad</a>
        </div>
    </footer>

    <!-- Botón flotante de WhatsApp al número oficial 953697434 -->
    <a href="<?= htmlspecialchars($whatsappLink1) ?>" target="_blank" class="whatsapp-float" title="WhatsApp Oficial: 953 697 434">
        <i class="fa-brands fa-whatsapp"></i>
    </a>

    <script src="assets/js/main.js"></script>
</body>
</html>