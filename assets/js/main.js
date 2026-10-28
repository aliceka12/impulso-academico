/**
 * IMPULSO ACADÉMICO - Interactividad, Animaciones y Reproductor Dinámico
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Navbar sticky shadow
    const navbar = document.getElementById('mainNavbar');
    window.addEventListener('scroll', () => {
        if (!navbar) return;
        if (window.scrollY > 30) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // 2. Menú móvil
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const navLinks = document.getElementById('navLinks');
    if (mobileBtn && navLinks) {
        mobileBtn.addEventListener('click', () => {
            navLinks.classList.toggle('open');
        });
    }

    // 3. Animaciones al hacer scroll (IntersectionObserver)
    const revealEls = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.12 });

    revealEls.forEach((el) => observer.observe(el));

    // 4. Contadores animados en Hero / Estadísticas
    const counters = document.querySelectorAll('[data-counter]');
    const counterObserver = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            const el = entry.target;
            const target = parseInt(el.getAttribute('data-counter'), 10) || 0;
            const suffix = el.getAttribute('data-suffix') || '';
            const prefix = el.getAttribute('data-prefix') || '';
            let current = 0;
            const step = Math.max(1, Math.ceil(target / 45));
            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                el.textContent = `${prefix}${current}${suffix}`;
            }, 28);
            obs.unobserve(el);
        });
    }, { threshold: 0.4 });

    counters.forEach((c) => counterObserver.observe(c));

    // 5. Reproductor de Videos Promocionales Dinámico (Desde BD)
    const playlistItems = document.querySelectorAll('.playlist-item');
    const videoContainer = document.getElementById('mainVideoContainer');
    const mainVideoTitle = document.getElementById('mainVideoTitle');
    const mainVideoDesc = document.getElementById('mainVideoDesc');
    const mainVideoCat = document.getElementById('mainVideoCat');

    playlistItems.forEach((item) => {
        item.addEventListener('click', () => {
            playlistItems.forEach((i) => i.classList.remove('active'));
            item.classList.add('active');

            const tipo = item.getAttribute('data-tipo');
            const url = item.getAttribute('data-url');
            const poster = item.getAttribute('data-poster');
            const titulo = item.getAttribute('data-titulo');
            const desc = item.getAttribute('data-desc');
            const cat = item.getAttribute('data-cat');

            if (mainVideoTitle) mainVideoTitle.textContent = titulo;
            if (mainVideoDesc) mainVideoDesc.textContent = desc;
            if (mainVideoCat) mainVideoCat.textContent = cat;

            if (videoContainer) {
                if (tipo === 'EXTERNO' && (url.includes('youtube.com') || url.includes('youtu.be') || url.includes('vimeo.com'))) {
                    videoContainer.innerHTML = `<iframe src="${url}" allowfullscreen allow="autoplay; encrypted-media"></iframe>`;
                } else {
                    videoContainer.innerHTML = `
                        <video id="activePromoVideo" controls autoplay poster="${poster}">
                            <source src="${url}" type="video/mp4">
                            Tu navegador no soporta reproducción de video HTML5.
                        </video>
                    `;
                }
            }
        });
    });

    // 6. Botones para reclamar campaña de descuento o seleccionar servicio hacia el formulario interno
    document.querySelectorAll('.js-select-promo').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const codigo = btn.getAttribute('data-codigo') || '';
            const campanaTitulo = btn.getAttribute('data-titulo') || '';
            const inputPromo = document.getElementById('inputCodigoPromo');
            const inputMensaje = document.getElementById('inputMensaje');

            if (inputPromo) {
                inputPromo.value = codigo;
                inputPromo.style.borderColor = '#0074F0';
                inputPromo.style.backgroundColor = '#EEF5FF';
            }
            if (inputMensaje && campanaTitulo) {
                inputMensaje.value = `Deseo aplicar la campaña "${campanaTitulo}" (${codigo}) y coordinar por interno mi presupuesto y facilidades de pago.`;
            }

            const formSection = document.getElementById('cotizar');
            if (formSection) {
                formSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    document.querySelectorAll('.js-select-service').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const servicio = btn.getAttribute('data-servicio') || '';
            const selectServicio = document.getElementById('selectServicio');
            if (selectServicio && servicio) {
                for (let i = 0; i < selectServicio.options.length; i++) {
                    if (selectServicio.options[i].value === servicio) {
                        selectServicio.selectedIndex = i;
                        break;
                    }
                }
            }
            const formSection = document.getElementById('cotizar');
            if (formSection) {
                formSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // 7. Envío del Formulario de Solicitud Interna (Sin Pasarela de Pago)
    const formSolicitud = document.getElementById('formSolicitudInterna');
    const feedbackBox = document.getElementById('formFeedback');

    if (formSolicitud) {
        formSolicitud.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btnSubmit = formSolicitud.querySelector('button[type="submit"]');
            const originalText = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Registrando solicitud...';

            try {
                const formData = new FormData(formSolicitud);
                const res = await fetch('api/solicitud.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.ok) {
                    feedbackBox.style.display = 'block';
                    feedbackBox.style.background = '#ECFDF5';
                    feedbackBox.style.border = '1.5px solid #10B981';
                    feedbackBox.style.color = '#065F46';
                    feedbackBox.innerHTML = `
                        <div style="font-weight:800;margin-bottom:6px;">
                            <i class="fa-solid fa-circle-check"></i> ${data.mensaje}
                        </div>
                        <div style="margin-top:10px;">
                            <a href="${data.whatsapp_url}" target="_blank" class="btn btn-whatsapp" style="padding:9px 18px;font-size:0.86rem;">
                                <i class="fa-brands fa-whatsapp"></i> Coordinar ahora mismo por WhatsApp
                            </a>
                        </div>
                    `;
                    formSolicitud.reset();
                } else {
                    feedbackBox.style.display = 'block';
                    feedbackBox.style.background = '#FEF2F2';
                    feedbackBox.style.border = '1.5px solid #EF4444';
                    feedbackBox.style.color = '#991B1B';
                    feedbackBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${data.mensaje}`;
                }
            } catch (err) {
                const formData = new FormData(formSolicitud);
                const nombre = formData.get('nombre') || '';
                const universidad = formData.get('universidad') || '';
                const carrera = formData.get('carrera') || '';
                const grado = formData.get('grado_academico') || 'PREGRADO';
                const servicio = formData.get('servicio_interes') || 'Asesoría de Tesis';
                const promo = formData.get('codigo_descuento') || '';
                const textoWa = encodeURIComponent(
                    `Hola Impulso Académico, soy ${nombre} (${grado} - ${universidad} / ${carrera}). Solicito coordinar por interno información y presupuesto para: ${servicio}.${promo ? ' Código promo: ' + promo : ''}`
                );
                const waUrl = `https://wa.me/51953697434?text=${textoWa}`;
                feedbackBox.style.display = 'block';
                feedbackBox.style.background = 'rgba(16, 185, 129, 0.16)';
                feedbackBox.style.border = '1.5px solid #10B981';
                feedbackBox.style.color = '#ECFDF5';
                feedbackBox.innerHTML = `
                    <div style="font-weight:800;margin-bottom:6px;">
                        <i class="fa-solid fa-circle-check" style="color:#34D399;"></i> ¡Solicitud lista! Coordina directamente con un asesor académico por interno:
                    </div>
                    <div style="margin-top:10px;">
                        <a href="${waUrl}" target="_blank" class="btn btn-whatsapp" style="padding:9px 18px;font-size:0.86rem;">
                            <i class="fa-brands fa-whatsapp"></i> Coordinar ahora mismo por WhatsApp
                        </a>
                    </div>
                `;
                formSolicitud.reset();
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
            }
        });
    }
});
