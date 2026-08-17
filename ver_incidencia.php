
<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

include 'config.php';

// ===== OBTENER ID DEL USUARIO =====
$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
if ($usuario_id == 0) {
    header('Location: index.php?mensaje=Por favor, selecciona un usuario válido para ver sus incidencias.');
    exit;
}

// ===== OBTENER DATOS DEL USUARIO =====
$sql_usuario = "SELECT * FROM usuarios WHERE id = $usuario_id";
$result_usuario = $conn->query($sql_usuario);
if ($result_usuario->num_rows == 0) {
    header('Location: index.php?mensaje=El usuario seleccionado ya no existe.');
    exit;
}
$usuario = $result_usuario->fetch_assoc();

// ===== OBTENER INCIDENCIAS DEL USUARIO =====
$sql_incidencias = "SELECT * FROM incidencias WHERE usuario_id = $usuario_id ORDER BY fecha_registro DESC";
$result_incidencias = $conn->query($sql_incidencias);

// ===== CAPTURAR MENSAJES =====
$mensaje = '';
$tipo_mensaje = '';

if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
    $tipo_mensaje = 'success';
}
if (isset($_GET['error'])) {
    $mensaje = $_GET['error'];
    $tipo_mensaje = 'danger';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidencias de <?php echo htmlspecialchars($usuario['nombre'] ?? 'Usuario'); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            padding: 25px 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        .header-incidencias {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            border-bottom: 3px solid #1a3a5c;
            padding-bottom: 15px;
        }
        .header-incidencias h2 {
            color: #1a3a5c;
            margin: 0;
        }
        .header-incidencias .botones {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-registrar {
            background: #28a745;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }
        .btn-registrar:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        .btn-imprimir-todo {
            background: #17a2b8;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }
        .btn-imprimir-todo:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        .btn-volver {
            background: #6c757d;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }
        .btn-volver:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 500;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .close-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
        }
        .close-btn:hover { opacity: 1; }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-vacaciones { background: #d4edda; color: #155724; }
        .badge-comision { background: #d1ecf1; color: #0c5460; }
        .badge-permiso { background: #fff3cd; color: #856404; }
        .badge-omision { background: #f8d7da; color: #721c24; }
        .badge-cumpleanos { background: #e8d4f8; color: #4a148c; }
        .badge-otra { background: #e9ecef; color: #495057; }
        .badge-justificada { background: #d4edda; color: #155724; }
        .badge-pendiente { background: #f8d7da; color: #721c24; }
        .table-responsive {
            overflow-x: auto;
            margin-top: 10px;
        }
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
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            white-space: nowrap;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #e6eaef;
            vertical-align: middle;
        }
        tbody tr:hover {
            background: #f5f8fc;
        }
        .btn-justificar-incidencia {
            background: linear-gradient(135deg, #fd7e14 0%, #e06b0a 100%);
            color: white;
            padding: 5px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-justificar-incidencia:hover {
            background: #e06b0a;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(253, 126, 20, 0.3);
        }
        .btn-justificado {
            background: #28a745;
            color: white;
            padding: 5px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: default;
            display: inline-block;
        }
        .btn-imprimir-directo {
            background: #17a2b8;
            color: #fff;
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            font-size: 12px;
            transition: all 0.3s;
        }
        .btn-imprimir-directo:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        .sin-incidencias {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .sin-incidencias .icono {
            font-size: 60px;
            margin-bottom: 15px;
        }
        .sin-incidencias h3 {
            color: #555;
        }
        .acciones-incidencia {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        @media (max-width: 768px) {
            .container { padding: 15px; }
            .header-incidencias { flex-direction: column; align-items: stretch; gap: 10px; }
            .header-incidencias h2 { font-size: 18px; text-align: center; }
            .header-incidencias .botones { justify-content: center; }
            .header-incidencias .botones .btn { width: 100%; text-align: center; }
            table { font-size: 12px; }
            th, td { padding: 6px 4px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-incidencias">
            <h2>📋 Incidencias de <?php echo htmlspecialchars($usuario['nombre'] ?? 'Usuario'); ?></h2>
            <div class="botones">
                <a href="agregar_justificacion.php?usuario_id=<?php echo $usuario_id; ?>" class="btn-registrar">
                    ➕ Registrar Nueva Incidencia
                </a>
                <a href="imprimir.php?usuario_id=<?php echo $usuario_id; ?>" class="btn-imprimir-todo">
                    🖨️ Imprimir incidencias
                </a>
                <a href="index.php" class="btn-volver">⬅ Volver al Inicio</a>
            </div>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
                <button class="close-btn" onclick="this.parentElement.style.display='none'">✕</button>
            </div>
        <?php endif; ?>

        <?php if ($result_incidencias->num_rows > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tipo</th>
                            <th>Fecha Inicio</th>
                            <th>Días</th>
                            <th>Motivo</th>
                            <th>Observaciones</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $contador = 0;
                        while($row = $result_incidencias->fetch_assoc()): 
                            $contador++;
                            
                            $badge_class = 'badge-otra';
                            $tipo_texto = $row['tipo_incidencia'] ?? 'Otra';
                            if ($tipo_texto == 'Vacaciones') $badge_class = 'badge-vacaciones';
                            elseif ($tipo_texto == 'Comisión') $badge_class = 'badge-comision';
                            elseif ($tipo_texto == 'Permiso Económico') $badge_class = 'badge-permiso';
                            elseif (strpos($tipo_texto, 'Omisión') !== false) $badge_class = 'badge-omision';
                            elseif ($tipo_texto == 'Cumpleaños Confianza') $badge_class = 'badge-cumpleanos';
                            
                            $justificada = isset($row['justificada']) && $row['justificada'] == 1;
                            $estado = $justificada ? 'Justificada' : 'Pendiente';
                            $badge_estado = $justificada ? 'badge-justificada' : 'badge-pendiente';
                            
                            $dias_calculados = 0;
                            if (!empty($row['fecha_inicio']) && !empty($row['fecha_fin'])) {
                                $inicio = new DateTime($row['fecha_inicio']);
                                $fin = new DateTime($row['fecha_fin']);
                                $intervalo = $inicio->diff($fin);
                                $dias_calculados = $intervalo->days + 1;
                            }
                        ?>
                            <tr>
                                <!-- ✅ Mostrar número correlativo (1, 2, 3...) -->
                                <td><strong><?php echo $contador; ?></strong></td>
                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($tipo_texto); ?></span></td>
                                <td><?php echo !empty($row['fecha_inicio']) ? date('d/m/Y', strtotime($row['fecha_inicio'])) : '--'; ?></td>
                                <td><?php echo $dias_calculados; ?></td>
                                <td><?php echo htmlspecialchars($row['motivo'] ?? '--'); ?></td>
                                <td><?php echo htmlspecialchars($row['observaciones'] ?? '--'); ?></td>
                                <td><span class="badge <?php echo $badge_estado; ?>"><?php echo $estado; ?></span></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_registro'])); ?></td>
                                <td>
                                    <div class="acciones-incidencia">
                                        <?php if (!$justificada): ?>
                                            <a href="justificar_incidencia.php?usuario_id=<?php echo $usuario_id; ?>&incidencia_id=<?php echo $row['id']; ?>" class="btn-justificar-incidencia">
                                                📝 Justificar
                                            </a>
                                        <?php else: ?>
                                            <span class="btn-justificado">✅ Justificada</span>
                                        <?php endif; ?>
                                        
                                        <!-- ✅ Envía el número correlativo para la impresión -->
                                        <a href="imprimir.php?usuario_id=<?php echo $usuario_id; ?>&incidencia_id=<?php echo $row['id']; ?>&num_incidencia=<?php echo $contador; ?>" target="_blank" class="btn-imprimir-directo">
                                            🖨️ Imprimir
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="sin-incidencias">
                <div class="icono">📭</div>
                <h3>No hay incidencias registradas</h3>
                <p>Este usuario aún no tiene incidencias registradas en el sistema.</p>
                <a href="agregar_justificacion.php?usuario_id=<?php echo $usuario_id; ?>" class="btn-registrar" style="display: inline-block; margin-top: 15px;">
                    ➕ Registrar Nueva Incidencia
                </a>
            </div>
        <?php endif; ?>
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