<?php
session_start();
include 'config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Obtener el ID del usuario desde la URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id == 0) {
    header('Location: index.php');
    exit;
}

// Consultar datos del usuario
$sql = "SELECT * FROM usuarios WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header('Location: index.php');
    exit;
}

$usuario = $result->fetch_assoc();
$nombre_usuario = $usuario['nombre'];
$email_usuario = $usuario['email'];
$telefono_usuario = $usuario['telefono'];
$fecha_registro = $usuario['fecha_registro'];

// =========================================================
// 👇 VINCULACIÓN CON EL LOGO DE LA PÁGINA WEB 👇
// =========================================================
// Buscar si existe el logo izquierdo en png, jpg, jpeg o webp
$ruta_logo_web = '';
foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
    $archivo = __DIR__ . '/logos/logo_izquierdo.' . $ext;
    if (file_exists($archivo)) {
        $ruta_logo_web = 'logos/logo_izquierdo.' . $ext . '?v=' . filemtime($archivo);
        break;
    }
}
// Si no hay ningún logo, dejar vacío (el texto se mostrará igual)
// =========================================================

// Consultar faltas del usuario
$sql_faltas = "SELECT * FROM faltas WHERE usuario_id = $id ORDER BY fecha_falta DESC"; 
$result_faltas = $conn->query($sql_faltas);

// Contadores
$total_faltas = $result_faltas->num_rows;
$justificadas = 0;
$sin_justificar = 0;

// Calcular cuántas están justificadas y cuántas no
while($falta = $result_faltas->fetch_assoc()) {
    if(isset($falta['justificada']) && $falta['justificada'] == 1) {
        $justificadas++;
    } else {
        $sin_justificar++;
    }
}
// Reiniciamos el puntero para poder usar el resultado en la tabla de abajo
$result_faltas->data_seek(0); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Usuario - <?php echo htmlspecialchars($nombre_usuario); ?></title>
    <style>
        /* --- ESTILOS GENERALES --- */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 40px;
            background: #fff;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* --- ESTILOS DEL ENCABEZADO INSTITUCIONAL --- */
        .header-institucional {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 3px solid #1a3a5c;
            padding-bottom: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .header-institucional .logo-img {
            max-height: 70px;
            width: auto;
            display: block;
        }
        .header-institucional .textos-derecha {
            text-align: right;
            line-height: 1.3;
        }
        .header-institucional .textos-derecha .humanismo {
            display: block;
            font-size: 16px;
            font-weight: 700;
            color: #1a3a5c;
            letter-spacing: 1px;
        }
        .header-institucional .textos-derecha .secret {
            display: block;
            font-size: 22px;
            font-weight: 800;
            color: #b8860b;
        }
        .header-institucional .textos-derecha .gobierno-periodo {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1a3a5c;
        }

        /* --- BOTÓN DE IMPRIMIR --- */
        .no-print { 
            text-align: right; 
            margin-bottom: 20px; 
        }
        .btn-imprimir {
            background: #1a3a5c;
            color: #fff;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn-imprimir:hover {
            background: #0f2a44;
        }

        /* --- ESTILOS DEL REPORTE --- */
        .info-usuario {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid #1a3a5c;
        }
        .info-usuario p { margin: 5px 0; font-size: 15px; }
        .resumen-tarjetas {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .tarjeta {
            background: #f8f9fa;
            padding: 15px 30px;
            border-radius: 10px;
            text-align: center;
            min-width: 120px;
        }
        .tarjeta h2 { font-size: 32px; margin: 0; }
        .tarjeta p { margin: 5px 0 0; font-size: 14px; color: #666; }
        .total-blue h2 { color: #1a3a5c; }
        .justi-green h2 { color: #28a745; }
        .sin-justi-red h2 { color: #dc3545; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }
        thead {
            background: #6c8ae0;
            color: #fff;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-justificada { background: #d4edda; color: #155724; }
        .badge-sin-justificar { background: #f8d7da; color: #721c24; }

        .footer-pagina {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        /* --- CONFIGURACIÓN PARA IMPRESIÓN --- */
        @media print {
            body { padding: 0.5cm; }
            .no-print { display: none !important; }
            .header-institucional { border-bottom-color: #000; }
            thead { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .tarjeta, .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="container">

        <!-- BOTÓN DE IMPRIMIR -->
        <div class="no-print">
            <button onclick="window.print()" class="btn-imprimir">🖨️ Imprimir Reporte</button>
            <a href="index.php" class="btn-imprimir" style="background: #6c757d; text-decoration: none;">⬅ Volver</a>
        </div>

        <!-- ENCABEZADO INSTITUCIONAL VINCULADO -->
        <div class="header-institucional">
            
            <!-- 👇 LOGO VINCULADO CON EL DE LA PÁGINA WEB 👇 -->
            <!-- Si el logo existe en la carpeta logos/, se mostrará. Si no, se ocultará y solo se verá el texto. -->
            <img src="<?php echo $ruta_logo_web; ?>" alt="Logotipo Secretaría de Finanzas" class="logo-img" onerror="this.style.display='none'">

            <!-- TEXTOS DE LA DEPENDENCIA -->
            <div class="textos-derecha">
                <span class="humanismo">HUMANISMO QUE TRANSFORMA</span>
                <span class="secret">SECRETARÍA DE FINANZAS</span>
                <span class="gobierno-periodo">GOBIERNO DE CHIAPAS 2024 - 2030</span>
            </div>
        </div>

        <!-- CUERPO DEL REPORTE -->
        <h1 style="text-align: center; color: #6c8ae0; margin-bottom: 10px;">📄 REPORTE DE USUARIO</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">Sistema de Gestión de Usuarios y Faltas</p>

        <div class="info-usuario">
            <p><strong>👤 Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre']); ?></p>
            <p><strong>📧 Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
            <p><strong>📱 Teléfono:</strong> <?php echo htmlspecialchars($usuario['telefono']); ?></p>
            <p><strong>📅 Fecha de registro:</strong> <?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?></p>
        </div>

        <h3 style="color: #1a3a5c; border-bottom: 2px solid #eee; padding-bottom: 10px;">📊 Resumen de Faltas</h3>
        <div class="resumen-tarjetas">
            <div class="tarjeta total-blue">
                <h2><?php echo $total_faltas; ?></h2>
                <p>Total Faltas</p>
            </div>
            <div class="tarjeta justi-green">
                <h2><?php echo $justificadas; ?></h2>
                <p>✅ Justificadas</p>
            </div>
            <div class="tarjeta sin-justi-red">
                <h2><?php echo $sin_justificar; ?></h2>
                <p>❌ Sin Justificar</p>
            </div>
        </div>

        <h3 style="color: #1a3a5c; border-bottom: 2px solid #eee; padding-bottom: 10px;">📜 Historial de Faltas</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Motivo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_faltas->num_rows > 0): ?>
                    <?php 
                    $contador = 1;
                    while($falta = $result_faltas->fetch_assoc()): 
                        $esta_justificada = isset($falta['justificada']) && $falta['justificada'] == 1;
                    ?>
                        <tr>
                            <td><?php echo $contador++; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($falta['fecha_falta'])); ?></td>
                            <td><?php echo htmlspecialchars($falta['motivo'] ?? 'Sin motivo'); ?></td>
                            <td>
                                <span class="badge <?php echo $esta_justificada ? 'badge-justificada' : 'badge-sin-justificar'; ?>">
                                    <?php echo $esta_justificada ? '✅ Justificada' : '❌ Sin justificar'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px; color: #999;">
                            Este usuario no tiene faltas registradas.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="footer-pagina">
            <p>Reporte generado el <?php echo date('d/m/Y H:i'); ?></p>
            <p style="font-size: 11px;">Este documento es válido como constancia del historial del usuario</p>
        </div>

    </div>
</body>
</html>