<?php
/**
 * IMPULSO ACADÉMICO - Login del Panel de Control Administrativo
 */
require_once dirname(__DIR__) . '/config/db.php';

$pdo = getPDO();
$cfg = obtenerConfiguracion($pdo);
$error = '';

if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in'], $_SESSION['admin_nombre'], $_SESSION['admin_usuario']);
    header('Location: login.php');
    exit;
}

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['usuario'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE usuario = ? LIMIT 1");
    $stmt->execute([$user]);
    $admin = $stmt->fetch();

    if ($admin && (password_verify($pass, $admin['password_hash']) || $pass === 'impulso2026')) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_usuario'] = $admin['usuario'];
        $_SESSION['admin_nombre'] = $admin['nombre'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos. Usa: admin / impulso2026';
    }
}

$logoUrl = '../' . ($cfg['logo_url'] ?? 'uploads/img/logo.jpg');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo | Impulso Académico</title>
    <link rel="icon" type="image/jpeg" href="<?= htmlspecialchars($logoUrl) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            min-height: 100vh;
            background: radial-gradient(circle at 80% 20%, rgba(0,116,240,0.32), transparent 45%),
                        linear-gradient(145deg, #021536 0%, #042152 60%, #062D6F 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #042152;
        }
        .login-card {
            width: 100%;
            max-width: 440px;
            background: #041F4A;
            border-radius: 26px;
            border: 2px solid #0074F0;
            box-shadow: 0 25px 60px rgba(0,0,0,0.65);
            overflow: hidden;
            color: #fff;
        }
        .login-header {
            background: #02122C;
            color: #fff;
            padding: 32px 28px 26px;
            text-align: center;
            border-bottom: 3px solid #0074F0;
        }
        .login-logo {
            width: 92px;
            height: 92px;
            border-radius: 50%;
            border: 3px solid #0074F0;
            object-fit: cover;
            margin-bottom: 12px;
            box-shadow: 0 0 24px rgba(0,116,240,0.55);
        }
        .login-body {
            padding: 30px 28px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 800;
            margin-bottom: 6px;
            color: #85C1FF;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px;
            background: rgba(2, 18, 44, 0.85);
            color: #fff;
            border: 1.5px solid rgba(56, 152, 255, 0.4);
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: inherit;
        }
        .form-control:focus {
            outline: none;
            border-color: #3898FF;
            box-shadow: 0 0 0 3px rgba(56,152,255,0.25);
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 999px;
            background: linear-gradient(135deg, #0074F0, #0051B3);
            color: #fff;
            font-weight: 800;
            font-size: 0.98rem;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(0,116,240,0.38);
            transition: transform 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
        }
        .demo-credentials {
            margin-top: 18px;
            padding: 12px;
            background: rgba(2, 18, 44, 0.7);
            border: 1px dashed #3898FF;
            border-radius: 12px;
            font-size: 0.82rem;
            text-align: center;
            color: #DCEBFF;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo Impulso Académico" class="login-logo">
            <h1 style="font-size:1.45rem;font-weight:900;">Impulso <span style="color:#3898FF;">Académico</span></h1>
            <p style="font-size:0.8rem;color:#9EC3F0;margin-top:4px;">Panel de Control • Clientes, Finanzas y Contenido Web</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
            <div style="background:#FEF2F2;border:1px solid #EF4444;color:#991B1B;padding:10px 14px;border-radius:10px;font-size:0.86rem;margin-bottom:16px;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="adminLoginForm">
                <div id="jsLoginError" style="display:none;background:#FEF2F2;border:1px solid #EF4444;color:#991B1B;padding:10px 14px;border-radius:10px;font-size:0.86rem;margin-bottom:16px;">
                    <i class="fa-solid fa-circle-exclamation"></i> Usuario o contraseña incorrectos. Usa: admin / impulso2026
                </div>
                <div class="form-group">
                    <label><i class="fa-solid fa-user-shield" style="color:#0074F0;"></i> Usuario Administrador</label>
                    <input type="text" name="usuario" id="loginUsuario" class="form-control" value="admin" required>
                </div>
                <div class="form-group">
                    <label><i class="fa-solid fa-key" style="color:#0074F0;"></i> Contraseña</label>
                    <input type="password" name="password" id="loginPassword" class="form-control" value="impulso2026" required>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-right-to-bracket"></i> Ingresar al Sistema de Control
                </button>
            </form>

            <div class="demo-credentials">
                <strong>Credenciales listas para ingresar:</strong><br>
                Usuario: <code>admin</code> &nbsp;|&nbsp; Clave: <code>impulso2026</code>
            </div>

            <div style="text-align:center;margin-top:16px;">
                <a href="../index.php" style="color:#0074F0;text-decoration:none;font-size:0.86rem;font-weight:700;">
                    <i class="fa-solid fa-arrow-left"></i> Volver a la Página Pública
                </a>
            </div>
        </div>
    </div>
    <script>
        if (window.location.pathname.endsWith('.html') || window.location.hostname.includes('github.io')) {
            document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                var u = document.getElementById('loginUsuario').value.trim();
                var p = document.getElementById('loginPassword').value.trim();
                if (u === 'admin' && p === 'impulso2026') {
                    sessionStorage.setItem('ia_admin_auth', '1');
                    window.location.href = 'index.html';
                } else {
                    document.getElementById('jsLoginError').style.display = 'block';
                }
            });
        }
    </script>
</body>
</html>
