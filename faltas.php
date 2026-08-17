<?php
include 'config.php';

// ===== OBTENER ID DEL USUARIO =====
$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
if ($usuario_id == 0) {
    header('Location: index.php?error=Usuario no válido');
    exit;
}

// ===== OBTENER DATOS DEL USUARIO =====
$sql_usuario = "SELECT * FROM usuarios WHERE id = $usuario_id";
$result_usuario = $conn->query($sql_usuario);
if ($result_usuario->num_rows == 0) {
    header('Location: index.php?error=Usuario no encontrado');
    exit;
}
$usuario = $result_usuario->fetch_assoc();

// ===== PROCESAR AGREGAR FALTA =====
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // ===== AGREGAR NUEVA FALTA =====
    if (isset($_POST['agregar_falta'])) {
        $fecha = $_POST['fecha'];
        $tipo = $_POST['tipo'];
        
        if (empty($fecha) || empty($tipo)) {
            $mensaje = 'Todos los campos son obligatorios.';
            $tipo_mensaje = 'danger';
        } else {
            // Usar fecha_falta
            $sql_insert = "INSERT INTO faltas (usuario_id, fecha_falta, tipo) VALUES ($usuario_id, '$fecha', '$tipo')";
            
            if ($conn->query($sql_insert)) {
                $mensaje = '✅ Falta registrada correctamente.';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = '❌ Error al registrar: ' . $conn->error;
                $tipo_mensaje = 'danger';
            }
        }
    }
    
    // ===== JUSTIFICAR FALTA =====
    if (isset($_POST['justificar'])) {
        $falta_id = intval($_POST['falta_id']);
        $motivo = trim($_POST['motivo']);
        $justificada = isset($_POST['justificada']) ? 1 : 0;
        
        if (empty($motivo)) {
            $mensaje = 'Debes escribir un motivo para justificar la falta.';
            $tipo_mensaje = 'danger';
        } else {
            $sql_update = "UPDATE faltas SET 
                justificada = $justificada, 
                motivo = '$motivo' 
            WHERE id = $falta_id AND usuario_id = $usuario_id";
            
            if ($conn->query($sql_update)) {
                $mensaje = '✅ Falta actualizada correctamente.';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = '❌ Error al actualizar: ' . $conn->error;
                $tipo_mensaje = 'danger';
            }
        }
    }
    
    // ===== ELIMINAR FALTA =====
    if (isset($_POST['eliminar'])) {
        $falta_id = intval($_POST['falta_id']);
        $sql_delete = "DELETE FROM faltas WHERE id = $falta_id AND usuario_id = $usuario_id";
        
        if ($conn->query($sql_delete)) {
            $mensaje = '✅ Falta eliminada correctamente.';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = '❌ Error al eliminar: ' . $conn->error;
            $tipo_mensaje = 'danger';
        }
    }
}

// ===== OBTENER FALTAS DEL USUARIO =====
// USAR fecha_falta
$sql_faltas = "SELECT * FROM faltas WHERE usuario_id = $usuario_id ORDER BY fecha_falta DESC";
$result_faltas = $conn->query($sql_faltas);

// ===== CONTADORES =====
$sql_contadores = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN justificada = 0 THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN justificada = 1 THEN 1 ELSE 0 END) as justificadas
FROM faltas WHERE usuario_id = $usuario_id";
$result_contadores = $conn->query($sql_contadores);
$contadores = $result_contadores->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faltas de <?php echo htmlspecialchars($usuario['nombre']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .header-faltas {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .header-faltas h2 { color: #1a3a5c; }
        .btn-volver {
            background: #6c757d;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-volver:hover { background: #5a6268; transform: translateY(-2px); }
        
        .form-agregar {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e6eaef;
            margin-bottom: 25px;
        }
        .form-agregar h3 { color: #1a3a5c; margin-bottom: 15px; }
        .form-agregar .form-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .form-agregar .form-row .campo {
            flex: 1;
            min-width: 150px;
        }
        .form-agregar .form-row .campo label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            color: #2c3e50;
            margin-bottom: 4px;
        }
        .form-agregar .form-row .campo input,
        .form-agregar .form-row .campo select {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #dce1e8;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-agregar .form-row .campo input:focus,
        .form-agregar .form-row .campo select:focus {
            border-color: #1a3a5c;
            outline: none;
        }
        .btn-agregar-falta {
            background: #28a745;
            color: #fff;
            padding: 8px 25px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            height: 40px;
        }
        .btn-agregar-falta:hover { background: #218838; transform: translateY(-2px); }
        
        .contadores {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0 30px;
        }
        .contador-card {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #e6eaef;
        }
        .contador-card .numero {
            font-size: 32px;
            font-weight: 700;
            color: #1a3a5c;
            display: block;
        }
        .contador-card .etiqueta {
            font-size: 13px;
            color: #6c757d;
            font-weight: 500;
            margin-top: 5px;
        }
        .contador-card.total .numero { color: #1a3a5c; }
        .contador-card.pendientes .numero { color: #dc3545; }
        .contador-card.justificadas .numero { color: #28a745; }
        
        .table-responsive {
            overflow-x: auto;
            margin-top: 20px;
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
        }
        td {
            padding: 10px;
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
        .badge-justificada { background: #d4edda; color: #155724; }
        .badge-pendiente { background: #f8d7da; color: #721c24; }
        
        .btn-accion {
            padding: 5px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-editar { background: #ffc107; color: #1e2a3a; }
        .btn-editar:hover { background: #e0a800; }
        .btn-eliminar { background: #dc3545; color: #fff; }
        .btn-eliminar:hover { background: #c82333; }
        
        .modal-justificar {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-justificar.active { display: flex; }
        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .modal-content h3 {
            color: #1a3a5c;
            margin-bottom: 20px;
            border-bottom: 2px solid #e6eaef;
            padding-bottom: 10px;
        }
        .modal-content .campo { margin-bottom: 15px; }
        .modal-content .campo label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 4px;
        }
        .modal-content .campo textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #dce1e8;
            border-radius: 8px;
            font-size: 14px;
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
        }
        .modal-content .campo textarea:focus {
            border-color: #1a3a5c;
            outline: none;
        }
        .modal-content .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
        }
        .modal-content .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #28a745;
            cursor: pointer;
        }
        .modal-content .checkbox-group label {
            font-weight: 600;
            font-size: 14px;
            color: #2c3e50;
            cursor: pointer;
        }
        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: flex-end;
            border-top: 1px solid #e6eaef;
            padding-top: 15px;
        }
        .btn-modal-guardar {
            background: #28a745;
            color: #fff;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-modal-guardar:hover { background: #218838; }
        .btn-modal-cancelar {
            background: #e9edf2;
            color: #3d4f62;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-modal-cancelar:hover { background: #d5dce6; }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 500;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .close-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
        }
        .close-btn:hover { opacity: 1; }
        
        @media (max-width: 768px) {
            .header-faltas { flex-direction: column; align-items: stretch; gap: 10px; }
            .header-faltas h2 { font-size: 18px; }
            .form-agregar .form-row { flex-direction: column; }
            .form-agregar .form-row .campo { min-width: 100%; }
            .contadores { grid-template-columns: repeat(2, 1fr); }
            .modal-content { padding: 20px; }
            .modal-actions { flex-direction: column; }
            .modal-actions .btn { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-faltas">
            <h2>📋 Gestión de Faltas - <?php echo htmlspecialchars($usuario['nombre']); ?></h2>
            <a href="index.php" class="btn-volver">⬅ Volver al Inicio</a>
        </div>
        
        <div class="form-agregar">
            <h3>➕ Agregar Nueva Falta</h3>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="campo">
                        <label for="fecha">📅 Fecha</label>
                        <input type="date" id="fecha" name="fecha" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="campo">
                        <label for="tipo">📌 Tipo de Falta</label>
                        <select id="tipo" name="tipo" required>
                            <option value="">Selecciona...</option>
                            <option value="Ausencia">Ausencia</option>
                            <option value="Retardo">Retardo</option>
                            <option value="Salida Temprano">Salida Temprano</option>
                            <option value="Inasistencia">Inasistencia</option>
                            <option value="Otra">Otra</option>
                        </select>
                    </div>
                    <div class="campo" style="min-width: 120px; display: flex; align-items: flex-end;">
                        <button type="submit" name="agregar_falta" class="btn-agregar-falta">➕ Agregar</button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="contadores">
            <div class="contador-card total">
                <span class="numero"><?php echo $contadores['total'] ?? 0; ?></span>
                <span class="etiqueta">📊 Total Faltas</span>
            </div>
            <div class="contador-card pendientes">
                <span class="numero"><?php echo $contadores['pendientes'] ?? 0; ?></span>
                <span class="etiqueta">⏳ Pendientes</span>
            </div>
            <div class="contador-card justificadas">
                <span class="numero"><?php echo $contadores['justificadas'] ?? 0; ?></span>
                <span class="etiqueta">✅ Justificadas</span>
            </div>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
                <button class="close-btn" onclick="this.parentElement.style.display='none'">✕</button>
            </div>
        <?php endif; ?>
        
        <?php if ($result_faltas->num_rows > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Motivo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $contador = 0;
                        while($row = $result_faltas->fetch_assoc()): 
                            $contador++;
                            $estado = $row['justificada'] ? 'Justificada' : 'Pendiente';
                            $badge_class = $row['justificada'] ? 'badge-justificada' : 'badge-pendiente';
                            $motivo = $row['motivo'] ? htmlspecialchars($row['motivo']) : '--';
                        ?>
                            <tr>
                                <td><?php echo $contador; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['fecha_falta'])); ?></td>
                                <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo $estado; ?></span></td>
                                <td><?php echo $motivo; ?></td>
                                <td>
                                    <button class="btn-accion btn-editar" onclick="abrirModal(<?php echo $row['id']; ?>, <?php echo $row['justificada']; ?>, '<?php echo addslashes($row['motivo']); ?>')">
                                        ✏️ Editar
                                    </button>
                                    <form method="POST" action="" style="display: inline-block;">
                                        <input type="hidden" name="falta_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="eliminar" class="btn-accion btn-eliminar" onclick="return confirm('¿Eliminar esta falta?')">
                                            🗑️ Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; color: #999;">
                <div style="font-size: 60px; margin-bottom: 20px;">📭</div>
                <h3 style="color: #555;">No hay faltas registradas</h3>
                <p style="margin-top: 10px;">Este usuario no tiene faltas en el sistema.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="modal-justificar" id="modalJustificar">
        <div class="modal-content">
            <h3>✏️ Editar / Justificar Falta</h3>
            <form method="POST" action="">
                <input type="hidden" name="falta_id" id="falta_id" value="">
                <div class="campo">
                    <label for="motivo">📝 Motivo de la falta</label>
                    <textarea id="motivo" name="motivo" required placeholder="Describe el motivo de la falta..."></textarea>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" id="justificada" name="justificada" value="1">
                    <label for="justificada">✅ Falta Justificada</label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-modal-cancelar" onclick="cerrarModal()">✕ Cancelar</button>
                    <button type="submit" class="btn-modal-guardar" name="justificar">💾 Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function abrirModal(faltaId, justificada, motivo) {
            document.getElementById('falta_id').value = faltaId;
            document.getElementById('motivo').value = motivo !== '--' ? motivo : '';
            document.getElementById('justificada').checked = justificada == 1;
            document.getElementById('modalJustificar').classList.add('active');
        }
        function cerrarModal() {
            document.getElementById('modalJustificar').classList.remove('active');
        }
        document.getElementById('modalJustificar').addEventListener('click', function(e) {
            if (e.target === this) cerrarModal();
        });
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