<?php
include 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    header('Location: index.php?error=Justificación no encontrada');
    exit();
}

// Obtener datos
$sql = "SELECT 
            j.*,
            u.nombre as usuario_nombre,
            u.area as usuario_area,
            u.puesto as usuario_puesto,
            u.nip as usuario_nip,
            u.telefono as usuario_telefono
        FROM justificaciones j
        JOIN usuarios u ON j.usuario_id = u.id
        WHERE j.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    header('Location: index.php?error=Justificación no encontrada');
    exit();
}

// Tipo de incidencia legible
$tipos = [
    'VACACIONES' => 'Vacaciones',
    'PERMISO_ECONOMICO' => 'Permiso Económico',
    'COMISION' => 'Comisión',
    'OPOSICION_ENTRADA' => 'Oposición de Entrada',
    'OPOSICION_SALIDA' => 'Oposición de Salida'
];
$tipo_mostrar = isset($tipos[$data['tipo_incidencia']]) ? $tipos[$data['tipo_incidencia']] : $data['tipo_incidencia'];

// Fecha de elaboración formateada
$meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$fecha_elaboracion = date('d', strtotime($data['fecha_elaboracion'])) . ' de ' . 
                     $meses[date('n', strtotime($data['fecha_elaboracion']))-1] . ' de ' . 
                     date('Y', strtotime($data['fecha_elaboracion']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa - Justificación</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f0f2f5; }
        .preview-container { max-width: 1000px; margin: 0 auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .preview-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; border-bottom: 2px solid #667eea; padding-bottom: 15px; margin-bottom: 25px; }
        .preview-header h1 { font-size: 20px; margin: 0; }
        .preview-header .consecutivo { font-size: 14px; color: #666; background: #f8f9fa; padding: 8px 15px; border-radius: 8px; }
        .preview-header .consecutivo strong { color: #667eea; }
        .preview-info { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px 30px; }
        .preview-info p { margin: 5px 0; font-size: 14px; }
        .preview-info strong { color: #333; }
        .preview-section { margin-bottom: 25px; }
        .preview-section h3 { color: #333; border-bottom: 1px solid #e0e0e0; padding-bottom: 8px; margin-bottom: 15px; font-size: 16px; }
        .preview-checkboxes { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }
        .preview-checkboxes .item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: #f8f9fa; border-radius: 6px; font-size: 14px; }
        .preview-checkboxes .item .check { display: inline-block; width: 20px; height: 20px; border: 2px solid #ccc; border-radius: 4px; text-align: center; line-height: 16px; font-size: 14px; font-weight: bold; background: white; }
        .preview-checkboxes .item .check.marcado { border-color: #28a745; background: #28a745; color: white; }
        .preview-motivo { background: #fff; padding: 15px; border-left: 4px solid #667eea; margin: 10px 0; font-size: 14px; }
        .preview-firmas { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 20px; }
        .preview-firmas .firma { text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px; }
        .preview-firmas .firma .nombre { font-weight: bold; font-size: 14px; }
        .preview-firmas .firma .puesto { font-size: 12px; color: #666; margin-top: 5px; }
        .preview-firmas .firma .linea { border-top: 1px solid #333; width: 80%; margin: 8px auto 5px; }
        .preview-firmas .firma .label-firma { font-size: 11px; color: #999; }
        .preview-actions { display: flex; gap: 15px; justify-content: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; }
        .btn-imprimir { padding: 12px 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; }
        .btn-imprimir:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4); }
        .btn-cerrar { padding: 12px 30px; background: #6c757d; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; }
        .btn-cerrar:hover { background: #5a6268; transform: translateY(-2px); }
        .footer-preview { text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e0e0e0; color: #999; font-size: 12px; }
        @media (max-width: 768px) {
            .preview-container { padding: 20px; }
            .preview-info { grid-template-columns: 1fr; }
            .preview-checkboxes { grid-template-columns: 1fr 1fr; }
            .preview-firmas { grid-template-columns: 1fr; }
            .preview-actions { flex-direction: column; align-items: center; }
            .btn-imprimir, .btn-cerrar { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="preview-container">
            <div class="preview-header">
                <h1>📄 Impresión de Formato de Justificación</h1>
                <div class="consecutivo"><strong>Consecutivo:</strong> <?php echo $data['consecutivo']; ?></div>
            </div>
            
            <div class="preview-info">
                <p><strong>Fecha de elaboración:</strong> <?php echo $fecha_elaboracion; ?></p>
                <p><strong>Nombre del servidor público:</strong> <?php echo htmlspecialchars($data['usuario_nombre']); ?></p>
                <p><strong>Categoría:</strong> <?php echo htmlspecialchars($data['usuario_puesto']); ?></p>
                <p><strong>Área de Adscripción o Comisión:</strong> <?php echo htmlspecialchars($data['usuario_area']); ?></p>
                <p><strong>NIP o Tarjeta de control de asistencia:</strong> <?php echo htmlspecialchars($data['usuario_nip']); ?></p>
                <p><strong>Número de Teléfono o Extensión:</strong> <?php echo htmlspecialchars($data['usuario_telefono']); ?></p>
            </div>
            
            <div class="preview-section">
                <h3>📋 Tipo de incidencia</h3>
                <div class="preview-checkboxes">
                    <?php 
                    $tipos_check = ['VACACIONES', 'PERMISO_ECONOMICO', 'COMISION', 'OPOSICION_ENTRADA', 'OPOSICION_SALIDA'];
                    foreach ($tipos_check as $t): 
                        $marcado = ($data['tipo_incidencia'] == $t);
                        $label = isset($tipos[$t]) ? $tipos[$t] : $t;
                    ?>
                        <div class="item">
                            <span class="check <?php echo $marcado ? 'marcado' : ''; ?>"><?php echo $marcado ? '✓' : ''; ?></span>
                            <?php echo $label; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="preview-section">
                <h3>📝 Motivos de la incidencia:</h3>
                <div class="preview-motivo"><?php echo nl2br(htmlspecialchars($data['motivo'])); ?></div>
            </div>
            
            <div class="preview-section">
                <h3>📅 Días a justificar:</h3>
                <div class="preview-motivo" style="border-left-color: #ffc107; font-weight: bold;">
                    <?php echo htmlspecialchars($data['dias_justificar']); ?>
                </div>
            </div>
            
            <div class="preview-section">
                <h3>✍️ Datos para complementar el Formato de justificación</h3>
                <div class="preview-firmas">
                    <div class="firma">
                        <div class="nombre"><?php echo htmlspecialchars($data['autoriza_nombre']); ?></div>
                        <div class="puesto"><?php echo htmlspecialchars($data['autoriza_puesto']); ?></div>
                        <div class="linea"></div>
                        <div class="label-firma">Autoriza</div>
                    </div>
                    <div class="firma">
                        <div class="nombre"><?php echo htmlspecialchars($data['visto_bueno_nombre']); ?></div>
                        <div class="puesto"><?php echo htmlspecialchars($data['visto_bueno_puesto']); ?></div>
                        <div class="linea"></div>
                        <div class="label-firma">Visto Bueno</div>
                    </div>
                </div>
            </div>
            
            <div class="preview-actions">
                <a href="imprimir_justificacion.php?id=<?php echo $data['id']; ?>" target="_blank" class="btn-imprimir">🖨️ Imprimir Formato</a>
                <a href="index.php" class="btn-cerrar">❌ Cerrar</a>
            </div>
            
            <div class="footer-preview">© <?php echo date('Y'); ?> - Área de Soporte Técnico</div>
        </div>
    </div>
    
    <script>
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() { alert.style.display = 'none'; }, 500);
            });
        }, 5000);
    </script>
</body>
</html>