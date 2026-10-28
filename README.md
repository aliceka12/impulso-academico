# 🎓 Impulso Académico - Sistema de Gestión y Apoyo a la Investigación (Lima – 2026)

Plataforma web multipágina de asesoría de tesis (Pregrado y Posgrado) + **Panel de Control Administrativo** para gestionar clientes, estados financieros (ingresos, egresos, cuentas por cobrar, utilidad neta), campañas de descuento, videos promocionales e imágenes desde la base de datos **MySQL**.

---

## ☁️ Cómo hacerlo correr desde la nube en GitHub (2 Opciones)

### Opción 1: GitHub Codespaces (Recomendado — Ejecuta PHP + MySQL + Panel de Control completo en la nube)
Este repositorio ya incluye la carpeta `.devcontainer` configurada automáticamente:
1. En tu repositorio de GitHub, haz clic en el botón verde **`<> Code`**.
2. Selecciona la pestaña **`Codespaces`**.
3. Haz clic en **`Create codespace on main`**.
4. GitHub levantará automáticamente un servidor en la nube con **PHP 8.3 + MySQL**, creará la base de datos `impulso_academico` y abrirá el puerto **8000** con un enlace público (`https://...-8000.app.github.dev`) donde funcionará tanto la web pública como el **Panel de Control (`admin` / `impulso2026`)**.

### Opción 2: GitHub Pages (Vista web pública automática)
Este repositorio incluye el flujo automático `.github/workflows/deploy-pages.yml`:
1. En tu repositorio de GitHub, ve a **`Settings`** (Configuración) → menú izquierdo **`Pages`**.
2. En **Build and deployment** → **Source**, selecciona **`GitHub Actions`**.
3. GitHub compilará automáticamente tus páginas desde MySQL y las publicará en tu enlace `https://<tu-usuario>.github.io/impulso-academico/`.

---

## 💻 Ejecución Local (Laragon / XAMPP)
1. Coloca el proyecto en `C:\laragon\www\impulso_academico` o `C:\xampp\htdocs\impulso_academico`.
2. Inicia **Apache** y **MySQL**.
3. Abre `http://localhost/impulso_academico/` (la base de datos `impulso_academico` se crea y pobla automáticamente al abrir la página por primera vez).
4. Panel de Control: `http://localhost/impulso_academico/admin/login.php` (`admin` / `impulso2026`).
