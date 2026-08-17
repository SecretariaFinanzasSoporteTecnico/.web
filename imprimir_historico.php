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

$year = isset($_GET['year']) ? $_GET['year'] : '2025';
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : ''; 

$titulo = "Histórico de Servicios";

$sql = "SELECT * FROM servicios WHERE anio_archivado IS NOT NULL AND anio_archivado > 0";

if ($year != 'todos') { 
    $sql .= " AND anio_archivado = '$year'";
    $titulo = "Histórico de Servicios - Año $year";
}

if (!empty($busqueda)) {
    $busqueda_segura = mysqli_real_escape_string($conn, $busqueda);
    $sql .= " AND (nombre_solicitante LIKE '%$busqueda_segura%' OR descripcion_equipo LIKE '%$busqueda_segura%')";
    $titulo .= " - Buscando: '$busqueda'";
}

$sql .= " ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Histórico de Servicios</title>
    <style>
        /* --- ESTILOS GENERALES (VISTA EN PANTALLA) --- */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 40px;
            background: #f0f2f5;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }

        /* --- ENCABEZADO INSTITUCIONAL --- */
        .header-reporte {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #1a3a5c;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header-reporte .logo-img {
            max-height: 70px;
            width: auto;
        }
        .header-reporte .textos-derecha {
            text-align: right;
            line-height: 1.2;
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
            font-size: 22px;
            font-weight: 800;
            color: #b8860b;
        }
        .header-reporte .textos-derecha .gobierno-periodo {
            display: block;
            font-size: 15px;
            font-weight: 600;
            color: #1a3a5c;
        }

        /* --- TÍTULO --- */
        h1 {
            text-align: center;
            color: #1a3a5c;
            border-bottom: 2px solid #1a3a5c;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .fecha-reporte {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
            font-size: 14px;
        }

        /* --- TABLA --- */
        .table-responsive { overflow-x: auto; }
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
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 10px 15px;
            border-bottom: 1px solid #e6eaef;
            vertical-align: middle;
        }
        tbody tr:hover { background: #f5f8fc; }
        
        .badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-pendiente { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .badge-resuelto { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        /* --- PIE DE PÁGINA --- */
        .footer-pagina {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #888;
        }

        /* ============================================================ */
        /* 👇 ESTILOS EXCLUSIVOS PARA CUANDO SE IMPRIME 👇              */
        /* (Esto hace que la página se convierta en un reporte de papel) */
        /* ============================================================ */
        @media print {
            /* 1. Limpiar el fondo y quitar la caja con sombra */
            body {
                padding: 0 !important;
                background: white !important;
            }
            .container {
                max-width: 100% !important;
                padding: 20px !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
            }

            /* 2. Ajustar el encabezado */
            .header-reporte {
                margin-bottom: 15px !important;
                padding-bottom: 10px !important;
                border-bottom-color: #000 !important;
            }
            .header-reporte .logo-img { max-height: 60px !important; }
            .header-reporte .textos-derecha .humanismo { font-size: 14px !important; }
            .header-reporte .textos-derecha .secret { font-size: 18px !important; }
            .header-reporte .textos-derecha .gobierno-periodo { font-size: 12px !important; }

            /* 3. Ajustar el título y la tabla para que quepa en 1 hoja */
            h1 { font-size: 18px !important; margin-bottom: 5px !important; border-bottom-color: #000 !important; padding-bottom: 10px !important; }
            .fecha-reporte { font-size: 11px !important; margin-bottom: 15px !important; }
            
            table { font-size: 10px !important; }
            th, td { padding: 6px 8px !important; }
            .badge { font-size: 9px !important; padding: 1px 8px !important; }

            /* 4. Forzar colores en la impresión */
            thead { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- ENCABEZADO INSTITUCIONAL -->
        <div class="header-reporte">
            <img src="<?php echo $ruta_logo_web; ?>" alt="Logotipo" class="logo-img" onerror="this.style.display='none'">
            <div class="textos-derecha">
                <span class="humanismo">HUMANISMO QUE TRANSFORMA</span>
                <span class="secret">SECRETARÍA DE FINANZAS</span>
                <span class="gobierno-periodo">GOBIERNO DE CHIAPAS 2024 - 2030</span>
            </div>
        </div>

        <h1>📜 <?php echo $titulo; ?></h1>
        <p class="fecha-reporte">Reporte generado el <?php echo date('d/m/Y \a \l\a\s H:i'); ?></p>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                        <th>Año Archivado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): 
                            $anio_registro = date('Y', strtotime($row['fecha_registro']));
                            $folio_mostrar = (!empty($row['folio_anual'])) ? $anio_registro . '-' . str_pad($row['folio_anual'], 4, '0', STR_PAD_LEFT) : 'Sin asignar';
                            $resuelto = isset($row['resuelto']) ? $row['resuelto'] : 0;
                        ?>
                            <tr>
                                <td><strong><?php echo $folio_mostrar; ?></strong></td>
                                <td><?php echo htmlspecialchars($row['nombre_solicitante'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['descripcion_equipo'] ?? ''); ?></td>
                                <td>
                                    <span class="badge <?php echo ($resuelto == 1) ? 'badge-resuelto' : 'badge-pendiente'; ?>">
                                        <?php echo ($resuelto == 1) ? '✅ Resuelto' : '⏳ Pendiente'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($row['fecha_registro'] ?? 'now')); ?></td>
                                <td><strong><?php echo $row['anio_archivado']; ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:40px; color:#999;">
                                📭 No hay servicios archivados que coincidan con tu búsqueda.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="footer-pagina">
            <p>Este documento es una constancia oficial del histórico de servicios.</p>
            <p style="font-size: 10px;">Página 1 de 1 | Generado automáticamente por el Sistema de Gestión</p>
        </div>

    </div>
</body>
</html>