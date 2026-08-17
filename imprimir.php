<?php
session_start();
include 'config.php';

// Si el usuario no está logueado, lo enviamos al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
$incidencia_id = isset($_GET['incidencia_id']) ? intval($_GET['incidencia_id']) : 0;
$num_incidencia = isset($_GET['num_incidencia']) ? intval($_GET['num_incidencia']) : 0;

if ($usuario_id == 0) {
    header('Location: index.php?mensaje=Usuario no válido para imprimir.');
    exit;
}

// ===== OBTENER DATOS DEL USUARIO =====
$sql_usuario = "SELECT * FROM usuarios WHERE id = $usuario_id";
$result_usuario = $conn->query($sql_usuario);
$usuario = $result_usuario->fetch_assoc();

// ===== SI HAY INCIDENCIA_ID, IMPRIMIR UNA SOLA INCIDENCIA =====
if ($incidencia_id > 0) {
    $sql_incidencia = "SELECT 
        id, tipo_incidencia, fecha_inicio, fecha_fin, motivo, observaciones, 
        justificada, titulo, prioridad, plazo, descripcion, pasos, evidencia, 
        causa_raiz, accion_inmediata, solucion_definitiva
        FROM incidencias 
        WHERE id = $incidencia_id AND usuario_id = $usuario_id";
    
    $result_incidencia = $conn->query($sql_incidencia);
    $incidencia = $result_incidencia->fetch_assoc();
}

// ===== SI NO HAY INCIDENCIA_ID, OBTENER TODAS LAS INCIDENCIAS =====
if ($incidencia_id == 0) {
    $sql_todas = "SELECT 
        id, tipo_incidencia, fecha_inicio, fecha_fin, motivo, observaciones, 
        justificada, titulo, prioridad, plazo, descripcion, pasos, evidencia, 
        causa_raiz, accion_inmediata, solucion_definitiva
        FROM incidencias 
        WHERE usuario_id = $usuario_id 
        ORDER BY fecha_registro DESC";
    $result_todas = $conn->query($sql_todas);
}

// Función para calcular días
function calcularDias($inicio, $fin) {
    if (empty($inicio) || empty($fin)) return 0;
    $inicioDt = new DateTime($inicio);
    $finDt = new DateTime($fin);
    $intervalo = $inicioDt->diff($finDt);
    return $intervalo->days + 1;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Incidencias</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            padding: 20px; 
            background: #f9f9f9;
        }
        h1 { 
            color: #1a3a5c; 
            border-bottom: 3px solid #1a3a5c; 
            padding-bottom: 15px; 
            margin-bottom: 25px;
        }
        .seccion {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .seccion h3 {
            color: #1a3a5c;
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }
        .campo { 
            margin-bottom: 10px; 
        }
        .campo strong { 
            display: inline-block; 
            width: 200px; 
            font-weight: 600;
            color: #333;
        }
        .campo span {
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background: #1a3a5c;
            color: white;
            padding: 10px 12px;
            text-align: left;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: top;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-justificada { background: #d4edda; color: #155724; }
        .badge-pendiente { background: #f8d7da; color: #721c24; }
        .btn-imprimir {
            background: #1a3a5c; 
            color: #fff; 
            padding: 10px 25px; 
            border: none; 
            border-radius: 6px; 
            font-size: 16px; 
            font-weight: 600; 
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-imprimir:hover {
            background: #0f2a44;
        }
        @media print { 
            .no-print { display: none !important; } 
            body { background: white; padding: 0; }
            .seccion { border: 1px solid #ccc; box-shadow: none; }
        }
    </style>
</head>
<body>
    
    <h1>📄 Reporte de Incidencias</h1>

    <!-- DATOS DEL USUARIO -->
    <div class="seccion">
        <div class="campo"><strong>Empleado:</strong> <span><?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?></span></div>
        <div class="campo"><strong>Email:</strong> <span><?php echo htmlspecialchars($usuario['email'] ?? ''); ?></span></div>
    </div>

    <?php if ($incidencia_id > 0 && isset($incidencia)): ?>
        <!-- =================================================== -->
        <!-- MODO: IMPRIMIR UNA SOLA INCIDENCIA -->
        <!-- =================================================== -->
        
        <div class="seccion">
            <!-- ✅ Mostrar el número correlativo que coincide con la vista -->
            <h3>📌 Detalles de la Incidencia #<?php echo ($num_incidencia > 0) ? $num_incidencia : $incidencia_id; ?></h3>
            
            <div class="campo"><strong>Tipo:</strong> <span><?php echo htmlspecialchars($incidencia['tipo_incidencia'] ?? 'No especificado'); ?></span></div>
            <div class="campo"><strong>Fecha Inicio:</strong> <span><?php echo !empty($incidencia['fecha_inicio']) ? date('d/m/Y', strtotime($incidencia['fecha_inicio'])) : '--'; ?></span></div>
            <div class="campo"><strong>Días:</strong> <span><?php echo calcularDias($incidencia['fecha_inicio'] ?? '', $incidencia['fecha_fin'] ?? ''); ?></span></div>
            <div class="campo"><strong>Motivo original:</strong> <span><?php echo htmlspecialchars($incidencia['motivo'] ?? '--'); ?></span></div>
        </div>

        <div class="seccion">
            <h3>📌 Detalles de la Justificación</h3>
            <div class="campo"><strong>Título:</strong> <span><?php echo htmlspecialchars($incidencia['titulo'] ?? 'No especificado'); ?></span></div>
            <div class="campo"><strong>Prioridad:</strong> <span><?php echo htmlspecialchars($incidencia['prioridad'] ?? 'No especificada'); ?></span></div>
            <div class="campo"><strong>Plazo:</strong> <span><?php echo htmlspecialchars($incidencia['plazo'] ?? 'No especificado'); ?></span></div>
            <div class="campo"><strong>Descripción:</strong><br>
                <span style="white-space: pre-wrap;"><?php echo htmlspecialchars($incidencia['descripcion'] ?? 'No especificada'); ?></span>
            </div>
            <div class="campo"><strong>Pasos:</strong><br>
                <span style="white-space: pre-wrap;"><?php echo htmlspecialchars($incidencia['pasos'] ?? 'No especificados'); ?></span>
            </div>
            <div class="campo"><strong>Evidencia:</strong><br>
                <span><?php echo htmlspecialchars($incidencia['evidencia'] ?? 'No especificada'); ?></span>
            </div>
            <div class="campo"><strong>Causa raíz:</strong><br>
                <span><?php echo htmlspecialchars($incidencia['causa_raiz'] ?? 'No especificada'); ?></span>
            </div>
            <div class="campo"><strong>Acción inmediata:</strong><br>
                <span><?php echo htmlspecialchars($incidencia['accion_inmediata'] ?? 'No especificada'); ?></span>
            </div>
            <div class="campo"><strong>Solución definitiva:</strong><br>
                <span><?php echo htmlspecialchars($incidencia['solucion_definitiva'] ?? 'No especificada'); ?></span>
            </div>
        </div>

    <?php else: ?>
        <!-- =================================================== -->
        <!-- MODO: IMPRIMIR TODAS LAS INCIDENCIAS -->
        <!-- =================================================== -->
        
        <div class="seccion">
            <h3>📋 Todas las incidencias de <?php echo htmlspecialchars($usuario['nombre'] ?? 'Usuario'); ?></h3>
            
            <?php if ($result_todas && $result_todas->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tipo</th>
                            <th>Fecha Inicio</th>
                            <th>Días</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th>Título</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $contador = 0;
                        while($row = $result_todas->fetch_assoc()): 
                            $contador++;
                        ?>
                            <tr>
                                <td><strong><?php echo $contador; ?></strong></td>
                                <td><?php echo htmlspecialchars($row['tipo_incidencia'] ?? 'Otra'); ?></td>
                                <td><?php echo !empty($row['fecha_inicio']) ? date('d/m/Y', strtotime($row['fecha_inicio'])) : '--'; ?></td>
                                <td><?php echo calcularDias($row['fecha_inicio'] ?? '', $row['fecha_fin'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['motivo'] ?? '--'); ?></td>
                                <td>
                                    <span class="badge <?php echo (!empty($row['justificada']) && $row['justificada'] == 1) ? 'badge-justificada' : 'badge-pendiente'; ?>">
                                        <?php echo (!empty($row['justificada']) && $row['justificada'] == 1) ? '✅ Justificada' : '❌ Pendiente'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['titulo'] ?? '--'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 20px;">📭 Este usuario no tiene incidencias registradas.</p>
            <?php endif; ?>
        </div>

    <?php endif; ?>

    <button onclick="window.print()" class="btn-imprimir no-print">🖨️ Imprimir este reporte</button>
    
</body>
</html>