<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// =========================================================
// VINCULACIÓN CON EL LOGO DE LA PÁGINA WEB
// =========================================================
$ruta_logo_web = '';
foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
    $archivo = __DIR__ . '/logos/logo_izquierdo.' . $ext;
    if (file_exists($archivo)) {
        $ruta_logo_web = 'logos/logo_izquierdo.' . $ext . '?v=' . filemtime($archivo);
        break;
    }
}

// Obtener todos los servicios para imprimir
$sql = "SELECT * FROM servicios ORDER BY fecha_registro DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario General - Secretaría de Finanzas</title>
    <style>
        /* --- CONFIGURACIÓN GENERAL --- */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f0f2f5;
            color: #333;
        }

        /* --- CONTENEDOR PRINCIPAL (Bonito en pantalla) --- */
        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }

        /* --- BOTONES (En pantalla) --- */
        .no-print {
            text-align: right;
            margin-bottom: 20px;
        }
        .btn {
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            font-size: 14px;
        }
        .btn-imprimir { background: #17a2b8; color: #fff; }
        .btn-imprimir:hover { background: #138496; transform: translateY(-2px); }
        .btn-volver { background: #6c757d; color: #fff; }
        .btn-volver:hover { background: #5a6268; transform: translateY(-2px); }

        /* --- ENCABEZADO INSTITUCIONAL --- */
        .header-reporte {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #1a3a5c;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header-reporte .logo-izquierda {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .header-reporte .logo-izquierda img {
            max-height: 80px;
            width: auto;
        }
        .header-reporte .textos-derecha {
            text-align: right;
            line-height: 1.3;
        }
        .header-reporte .textos-derecha .humanismo {
            display: block;
            font-size: 16px;
            font-weight: 700;
            color: #1a3a5c;
            letter-spacing: 1px;
        }
        .header-reporte .textos-derecha .secret {
            display: block;
            font-size: 24px;
            font-weight: 800;
            color: #b8860b;
        }
        .header-reporte .textos-derecha .gobierno-periodo {
            display: block;
            font-size: 16px;
            font-weight: 600;
            color: #1a3a5c;
        }

        /* --- TÍTULO DEL REPORTE --- */
        .titulo-reporte {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: #1a3a5c;
            margin: 0 0 5px 0;
        }
        .fecha-reporte {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin: 0 0 25px 0;
        }

        /* --- TABLA OPTIMIZADA --- */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        thead {
            background: #1a3a5c;
            color: #fff;
        }
        th {
            padding: 10px 12px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 8px 12px;
            border-bottom: 1px solid #e6eaef;
            vertical-align: middle;
        }
        tbody tr:hover {
            background: #f5f8fc;
        }

        /* --- ESTILOS DE ETIQUETAS --- */
        .etiqueta-inventario {
            font-family: 'Courier New', monospace;
            background: #f1f2f6;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 13px;
            color: #2f3542;
        }
        .badge-estado {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-activo { background: #d4edda; color: #155724; }
        .badge-archivado { background: #e2e3e5; color: #383d41; }

        /* --- PIE DE PÁGINA --- */
        .footer-pagina {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #888;
        }

        /* =================================================== */
        /* 👇 ESTILOS ESPECIALES SOLO PARA IMPRESIÓN 👇      */
        /* (Esto hace que al imprimir quepa en 1 sola hoja)   */
        /* =================================================== */
        @media print {
            body { 
                padding: 0; 
                background: white; 
            }
            .container { 
                box-shadow: none; 
                border: none; 
                padding: 20px; 
                max-width: 100%; 
                border-radius: 0; 
            }
            .no-print { display: none !important; }
            .header-reporte { border-bottom-color: #000; margin-bottom: 15px; }
            .header-reporte .logo-izquierda img { max-height: 60px; }
            .header-reporte .textos-derecha .humanismo { font-size: 12px; }
            .header-reporte .textos-derecha .secret { font-size: 16px; }
            .header-reporte .textos-derecha .gobierno-periodo { font-size: 11px; }
            
            .titulo-reporte { font-size: 16px; margin: 0 0 3px 0; }
            .fecha-reporte { font-size: 11px; margin: 0 0 10px 0; }
            
            table { font-size: 10px; }
            th, td { padding: 4px 6px; }
            .etiqueta-inventario { font-size: 9px; padding: 1px 4px; }
            .badge-estado { font-size: 9px; padding: 1px 6px; }
            .footer-pagina { margin-top: 15px; padding-top: 8px; font-size: 9px; }
            
            thead { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge-estado { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .etiqueta-inventario { border: 1px solid #ccc; background: #f9f9f9; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- BOTONES (Solamente visibles en pantalla) -->
        <div class="no-print">
            <button onclick="window.print()" class="btn btn-imprimir">🖨️ Imprimir Reporte</button>
            <a href="inventario.php" class="btn btn-volver">⬅ Volver</a>
        </div>

        <!-- ENCABEZADO INSTITUCIONAL -->
        <div class="header-reporte">
            <div class="logo-izquierda">
                <img src="<?php echo $ruta_logo_web; ?>" alt="Logotipo" onerror="this.style.display='none'">
            </div>
            <div class="textos-derecha">
                <span class="humanismo">HUMANISMO QUE TRANSFORMA</span>
                <span class="secret">SECRETARÍA DE FINANZAS</span>
                <span class="gobierno-periodo">GOBIERNO DE CHIAPAS 2024 - 2030</span>
            </div>
        </div>

        <h1 class="titulo-reporte">📦 Inventario General</h1>
        <p class="fecha-reporte">Reporte generado el <?php echo date('d/m/Y \a \l\a\s H:i'); ?></p>

        <table>
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">#</th>
                    <th>N° Inventario</th>
                    <th>Responsable</th>
                    <th>Descripción</th>
                    <th style="width: 100px;">Estado</th>
                    <th style="width: 110px;">Fecha Registro</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $contador = 1; while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td style="text-align: center; font-weight: bold;"><?php echo $contador++; ?></td>
                            <td>
                                <?php if(!empty($row['numero_inventario'])): ?>
                                    <span class="etiqueta-inventario"><?php echo htmlspecialchars($row['numero_inventario']); ?></span>
                                <?php else: ?>
                                    <span style="color:#bbb; font-style:italic;">Sin etiqueta</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['nombre_solicitante'] ?? 'No asignado'); ?></td>
                            <td><?php echo htmlspecialchars($row['descripcion_equipo'] ?? 'Sin equipo'); ?></td>
                            <td>
                                <?php if($row['anio_archivado'] > 0): ?>
                                    <span class="badge-estado badge-archivado">📦 Archivado <?php echo $row['anio_archivado']; ?></span>
                                <?php else: ?>
                                    <span class="badge-estado badge-activo">🟢 Activo</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 11px; color: #555;"><?php echo date('d/m/Y', strtotime($row['fecha_registro'] ?? 'now')); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding:40px; color:#999;">📭 No hay registros en el inventario.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="footer-pagina">
            <p>Este documento es una constancia oficial del inventario de la Secretaría de Finanzas.</p>
            <p style="font-size: 10px;">Página 1 de 1 | Generado automáticamente por el Sistema de Gestión</p>
        </div>
    </div>
</body>
</html>