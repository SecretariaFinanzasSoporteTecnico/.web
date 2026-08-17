<?php
include 'config.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: gestion_servicio.php');
    exit;
}

$id = intval($_GET['id']);
$mensaje = '';
$tipo_mensaje = '';

// Obtener datos del servicio
$sql = "SELECT * FROM servicios WHERE id = $id";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    header('Location: gestion_servicio.php');
    exit;
}
$row = $result->fetch_assoc();

// Procesar formulario de edición
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $folio = $_POST['folio'];
    $fecha_solicitud = $_POST['fecha_solicitud'];
    $nombre_solicitante = $_POST['nombre_solicitante'];
    $subsecretaria = $_POST['subsecretaria'];
    $direccion = $_POST['direccion'];
    $departamento = $_POST['departamento'];
    $descripcion = $_POST['descripcion'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $serie = $_POST['serie'];
    $numero_inventario = $_POST['numero_inventario']; // NUEVO CAMPO AGREGADO
    $falla_reportada = $_POST['falla_reportada'];
    $nombre_entrega = $_POST['nombre_entrega'];
    $soporte_recibe = $_POST['soporte_recibe'];
    $accion_realizada = $_POST['accion_realizada'];
    $estatus = $_POST['estatus'];
    $modalidad = $_POST['modalidad'];
    $fecha_realizacion = $_POST['fecha_realizacion'];
    $hora_conclusion = $_POST['hora_conclusion'];
    $nombre_recibe = $_POST['nombre_recibe'];
    $soporte_entrega = $_POST['soporte_entrega'];

    if (empty($nombre_solicitante) || empty($descripcion)) {
        $mensaje = 'Los campos obligatorios no pueden estar vacíos.';
        $tipo_mensaje = 'danger';
    } else {
        $sql_update = "UPDATE servicios SET 
            folio='$folio', fecha_solicitud='$fecha_solicitud', nombre_solicitante='$nombre_solicitante',
            subsecretaria='$subsecretaria', direccion='$direccion', departamento='$departamento',
            descripcion_equipo='$descripcion', marca='$marca', modelo='$modelo', serie='$serie',
            numero_inventario='$numero_inventario', falla_reportada='$falla_reportada', nombre_entrega='$nombre_entrega',
            soporte_recibe='$soporte_recibe', accion_realizada='$accion_realizada', estatus='$estatus',
            modalidad='$modalidad', fecha_realizacion='$fecha_realizacion', hora_conclusion='$hora_conclusion',
            nombre_recibe='$nombre_recibe', soporte_entrega='$soporte_entrega'
            WHERE id = $id";

        if ($conn->query($sql_update)) {
            header('Location: ver_servicio.php?id=' . $id . '&mensaje=Servicio actualizado correctamente');
            exit;
        } else {
            $mensaje = '❌ Error al actualizar: ' . $conn->error;
            $tipo_mensaje = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Servicio</title>
    <style>
        .container { max-width: 1000px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 8px 30px rgba(0,0,0,0.1); }
        .header-form { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; border-bottom: 3px solid #ffc107; padding-bottom: 15px; margin-bottom: 25px; }
        .header-form h1 { color: #ffc107; }
        .btn-volver { background: #6c757d; color: #fff; padding: 10px 25px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s; }
        .btn-volver:hover { background: #5a6268; transform: translateY(-2px); }
        .seccion-form { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px; border-left: 4px solid #ffc107; }
        .seccion-form h3 { color: #ffc107; margin-bottom: 15px; font-size: 18px; border-bottom: 1px solid #e6eaef; padding-bottom: 10px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 25px; }
        .form-grid .campo-full { grid-column: 1 / -1; }
        .campo { display: flex; flex-direction: column; gap: 4px; }
        .campo label { font-weight: 600; font-size: 14px; color: #2c3e50; }
        .campo input, .campo select, .campo textarea { padding: 10px 12px; border: 2px solid #dce1e8; border-radius: 8px; font-size: 14px; transition: 0.3s; font-family: inherit; width: 100%; }
        .campo input:focus, .campo select:focus, .campo textarea:focus { border-color: #ffc107; outline: none; box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.1); }
        .campo textarea { resize: vertical; min-height: 80px; }
        .tabla-equipos { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 14px; }
        .tabla-equipos thead { background: #ffc107; color: #fff; }
        .tabla-equipos th { padding: 10px; text-align: left; font-weight: 600; }
        .tabla-equipos td { padding: 8px 10px; border-bottom: 1px solid #e6eaef; }
        .tabla-equipos select, .tabla-equipos input { width: 100%; padding: 8px; border: 2px solid #dce1e8; border-radius: 6px; font-size: 13px; }
        .tabla-equipos select:focus, .tabla-equipos input:focus { border-color: #ffc107; outline: none; }
        .acciones-form { display: flex; gap: 15px; margin-top: 25px; padding-top: 20px; border-top: 2px solid #e6eaef; justify-content: flex-end; }
        .btn { padding: 10px 25px; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; transition: all 0.3s; }
        .btn-guardar { background: #ffc107; color: #fff; }
        .btn-guardar:hover { background: #e0a800; transform: translateY(-2px); }
        .btn-cancelar { background: #e9edf2; color: #3d4f62; }
        .btn-cancelar:hover { background: #d5dce6; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; font-weight: 500; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .close-btn { background: none; border: none; font-size: 20px; cursor: pointer; color: inherit; opacity: 0.7; }
        .close-btn:hover { opacity: 1; }
        @media (max-width: 768px) { .container { padding: 15px; } .header-form { flex-direction: column; align-items: stretch; text-align: center; } .form-grid { grid-template-columns: 1fr; } .acciones-form { flex-direction: column; } .acciones-form .btn { width: 100%; text-align: center; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-form">
            <h1>✏️ Editar Servicio</h1>
            <a href="gestion_servicio.php" class="btn-volver">⬅ Volver al Listado</a>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
                <button class="close-btn" onclick="this.parentElement.style.display='none'">✕</button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- DATOS GENERALES -->
            <div class="seccion-form">
                <h3>📋 Datos Generales</h3>
                <div class="form-grid">
                    <div class="campo">
                        <label>No. de Folio</label>
                        <input type="text" name="folio" value="<?php echo htmlspecialchars($row['folio']); ?>" readonly style="background:#f1f3f5; font-weight:bold;">
                    </div>
                    <div class="campo">
                        <label for="fecha_solicitud">Fecha de solicitud</label>
                        <input type="date" id="fecha_solicitud" name="fecha_solicitud" value="<?php echo htmlspecialchars($row['fecha_solicitud']); ?>">
                    </div>
                    <div class="campo campo-full">
                        <label for="nombre_solicitante">Nombre del Solicitante <span class="obligatorio">*</span></label>
                        <input type="text" id="nombre_solicitante" name="nombre_solicitante" value="<?php echo htmlspecialchars($row['nombre_solicitante']); ?>" required>
                    </div>
                    <div class="campo">
                        <label for="subsecretaria">Subsecretaría</label>
                        <select id="subsecretaria" name="subsecretaria">
                            <option value="">Selecciona una Subsecretaría</option>
                            <option value="Subsecretaría de Administración" <?php if($row['subsecretaria'] == 'Subsecretaría de Administración') echo 'selected'; ?>>Subsecretaría de Administración</option>
                            <option value="Subsecretaría de Ingresos" <?php if($row['subsecretaria'] == 'Subsecretaría de Ingresos') echo 'selected'; ?>>Subsecretaría de Ingresos</option>
                            <option value="Subsecretaría de Egresos" <?php if($row['subsecretaria'] == 'Subsecretaría de Egresos') echo 'selected'; ?>>Subsecretaría de Egresos</option>
                            <option value="Subsecretaría de Planeación" <?php if($row['subsecretaria'] == 'Subsecretaría de Planeación') echo 'selected'; ?>>Subsecretaría de Planeación</option>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="direccion">Dirección</label>
                        <select id="direccion" name="direccion">
                            <option value="">Selecciona una Dirección</option>
                            <option value="Dirección de Tecnologías" <?php if($row['direccion'] == 'Dirección de Tecnologías') echo 'selected'; ?>>Dirección de Tecnologías</option>
                            <option value="Dirección de Recursos Humanos" <?php if($row['direccion'] == 'Dirección de Recursos Humanos') echo 'selected'; ?>>Dirección de Recursos Humanos</option>
                            <option value="Dirección de Finanzas" <?php if($row['direccion'] == 'Dirección de Finanzas') echo 'selected'; ?>>Dirección de Finanzas</option>
                            <option value="Dirección de Administración" <?php if($row['direccion'] == 'Dirección de Administración') echo 'selected'; ?>>Dirección de Administración</option>
                        </select>
                    </div>
                    <div class="campo campo-full">
                        <label for="departamento">Departamento/Área</label>
                        <select id="departamento" name="departamento">
                            <option value="">Selecciona un Departamento</option>
                            <option value="Soporte Técnico" <?php if($row['departamento'] == 'Soporte Técnico') echo 'selected'; ?>>Soporte Técnico</option>
                            <option value="Redes y Telecomunicaciones" <?php if($row['departamento'] == 'Redes y Telecomunicaciones') echo 'selected'; ?>>Redes y Telecomunicaciones</option>
                            <option value="Desarrollo de Software" <?php if($row['departamento'] == 'Desarrollo de Software') echo 'selected'; ?>>Desarrollo de Software</option>
                            <option value="Infraestructura" <?php if($row['departamento'] == 'Infraestructura') echo 'selected'; ?>>Infraestructura</option>
                            <option value="Seguridad Informática" <?php if($row['departamento'] == 'Seguridad Informática') echo 'selected'; ?>>Seguridad Informática</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- EQUIPO -->
            <div class="seccion-form">
                <h3>💻 Datos del Equipo</h3>
                <table class="tabla-equipos">
                    <thead>
                        <tr>
                            <th>DESCRIPCIÓN</th>
                            <th>MARCA</th>
                            <th>MODELO</th>
                            <th>SERIE</th>
                            <th>N° INVENTARIO</th> <!-- NUEVA COLUMNA -->
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="descripcion">
                                    <option value="">Selecciona un Equipo</option>
                                    <option value="Computadora" <?php if($row['descripcion_equipo'] == 'Computadora') echo 'selected'; ?>>Computadora</option>
                                    <option value="Laptop" <?php if($row['descripcion_equipo'] == 'Laptop') echo 'selected'; ?>>Laptop</option>
                                    <option value="Impresora" <?php if($row['descripcion_equipo'] == 'Impresora') echo 'selected'; ?>>Impresora</option>
                                    <option value="Monitor" <?php if($row['descripcion_equipo'] == 'Monitor') echo 'selected'; ?>>Monitor</option>
                                    <option value="Servidor" <?php if($row['descripcion_equipo'] == 'Servidor') echo 'selected'; ?>>Servidor</option>
                                    <option value="Switch" <?php if($row['descripcion_equipo'] == 'Switch') echo 'selected'; ?>>Switch</option>
                                    <option value="Router" <?php if($row['descripcion_equipo'] == 'Router') echo 'selected'; ?>>Router</option>
                                    <option value="Teléfono" <?php if($row['descripcion_equipo'] == 'Teléfono') echo 'selected'; ?>>Teléfono</option>
                                    <option value="Proyector" <?php if($row['descripcion_equipo'] == 'Proyector') echo 'selected'; ?>>Proyector</option>
                                    <option value="Escáner" <?php if($row['descripcion_equipo'] == 'Escáner') echo 'selected'; ?>>Escáner</option>
                                </select>
                            </td>
                            <td>
                                <select name="marca">
                                    <option value="">Selecciona una Marca</option>
                                    <option value="HP" <?php if($row['marca'] == 'HP') echo 'selected'; ?>>HP</option>
                                    <option value="Dell" <?php if($row['marca'] == 'Dell') echo 'selected'; ?>>Dell</option>
                                    <option value="Lenovo" <?php if($row['marca'] == 'Lenovo') echo 'selected'; ?>>Lenovo</option>
                                    <option value="Apple" <?php if($row['marca'] == 'Apple') echo 'selected'; ?>>Apple</option>
                                    <option value="Samsung" <?php if($row['marca'] == 'Samsung') echo 'selected'; ?>>Samsung</option>
                                    <option value="Epson" <?php if($row['marca'] == 'Epson') echo 'selected'; ?>>Epson</option>
                                    <option value="Brother" <?php if($row['marca'] == 'Brother') echo 'selected'; ?>>Brother</option>
                                    <option value="Cisco" <?php if($row['marca'] == 'Cisco') echo 'selected'; ?>>Cisco</option>
                                </select>
                            </td>
                            <td>
                                <select name="modelo">
                                    <option value="">Selecciona un Modelo</option>
                                    <option value="OptiPlex" <?php if($row['modelo'] == 'OptiPlex') echo 'selected'; ?>>OptiPlex</option>
                                    <option value="Latitude" <?php if($row['modelo'] == 'Latitude') echo 'selected'; ?>>Latitude</option>
                                    <option value="ThinkPad" <?php if($row['modelo'] == 'ThinkPad') echo 'selected'; ?>>ThinkPad</option>
                                    <option value="MacBook" <?php if($row['modelo'] == 'MacBook') echo 'selected'; ?>>MacBook</option>
                                    <option value="ProBook" <?php if($row['modelo'] == 'ProBook') echo 'selected'; ?>>ProBook</option>
                                    <option value="PowerEdge" <?php if($row['modelo'] == 'PowerEdge') echo 'selected'; ?>>PowerEdge</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="serie" value="<?php echo htmlspecialchars($row['serie'] ?? ''); ?>" placeholder="Número de serie">
                            </td>
                            <!-- 👇 NUEVO CAMPO DE INVENTARIO AGREGADO 👇 -->
                            <td>
                                <input type="text" name="numero_inventario" value="<?php echo htmlspecialchars($row['numero_inventario'] ?? ''); ?>" placeholder="Ej: P-211111..." style="width: 140px;">
                            </td>
                            <!-- 👆 FIN DEL CAMPO 👆 -->
                        </tr>
                    </tbody>
                </table>

                <div class="campo campo-full" style="margin-top: 15px;">
                    <label for="falla_reportada">Falla reportada y/o servicio solicitado <span class="obligatorio">*</span></label>
                    <textarea id="falla_reportada" name="falla_reportada" rows="3" required><?php echo htmlspecialchars($row['falla_reportada'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- SOPORTE -->
            <div class="seccion-form">
                <h3>🔧 Datos de Soporte</h3>
                <div class="form-grid">
                    <div class="campo">
                        <label for="nombre_entrega">Nombre de quién entrega el equipo</label>
                        <input type="text" id="nombre_entrega" name="nombre_entrega" value="<?php echo htmlspecialchars($row['nombre_entrega'] ?? ''); ?>" placeholder="Nombre de quién entrega el equipo">
                    </div>
                    <div class="campo">
                        <label for="soporte_recibe">Soporte Técnico quién recibe la solicitud</label>
                        <select id="soporte_recibe" name="soporte_recibe">
                            <option value="">Selecciona el personal</option>
                            <option value="Ana María Pérez" <?php if($row['soporte_recibe'] == 'Ana María Pérez') echo 'selected'; ?>>Ana María Pérez</option>
                            <option value="Carlos Eduardo Ramírez" <?php if($row['soporte_recibe'] == 'Carlos Eduardo Ramírez') echo 'selected'; ?>>Carlos Eduardo Ramírez</option>
                            <option value="Laura Gutiérrez Ortiz" <?php if($row['soporte_recibe'] == 'Laura Gutiérrez Ortiz') echo 'selected'; ?>>Laura Gutiérrez Ortiz</option>
                            <option value="Jorge Luis Sánchez" <?php if($row['soporte_recibe'] == 'Jorge Luis Sánchez') echo 'selected'; ?>>Jorge Luis Sánchez</option>
                        </select>
                    </div>
                    <div class="campo campo-full">
                        <label for="accion_realizada">Acción realizada</label>
                        <textarea id="accion_realizada" name="accion_realizada" rows="2"><?php echo htmlspecialchars($row['accion_realizada'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- SITUACIÓN FINAL DEL SERVICIO -->
            <div class="seccion-form">
                <h3>📌 SITUACIÓN FINAL DEL SERVICIO</h3>
                
                <div class="campo campo-full" style="margin-bottom: 15px;">
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 20px;">
                        <label style="font-weight: 600; display: flex; align-items: center; gap: 5px; cursor: pointer;">
                            <input type="radio" name="estatus" value="Falla corregida" <?php if($row['estatus'] == 'Falla corregida') echo 'checked'; ?>> Falla corregida
                        </label>
                        <label style="font-weight: 600; display: flex; align-items: center; gap: 5px; cursor: pointer;">
                            <input type="radio" name="estatus" value="Falla NO corregida" <?php if($row['estatus'] == 'Falla NO corregida' || empty($row['estatus'])) echo 'checked'; ?>> Falla NO corregida
                        </label>
                    </div>
                </div>

                <!-- Modalidad y Fechas -->
                <div class="form-grid">
                    <div class="campo campo-full">
                        <label>Modalidad de la reparación</label>
                        <div class="grupo-checkboxes">
                            <label><input type="radio" name="modalidad" value="Definitiva" <?php if($row['modalidad'] == 'Definitiva') echo 'checked'; ?>> Definitiva</label>
                            <label><input type="radio" name="modalidad" value="Temporal" <?php if($row['modalidad'] == 'Temporal') echo 'checked'; ?>> Temporal</label>
                        </div>
                    </div>
                    <div class="campo">
                        <label for="fecha_realizacion">Fecha de realización</label>
                        <input type="date" id="fecha_realizacion" name="fecha_realizacion" value="<?php echo htmlspecialchars($row['fecha_realizacion'] ?? ''); ?>">
                    </div>
                    <div class="campo">
                        <label for="hora_conclusion">Hora de conclusión</label>
                        <input type="time" id="hora_conclusion" name="hora_conclusion" value="<?php echo htmlspecialchars($row['hora_conclusion'] ?? ''); ?>">
                    </div>
                </div>

                <!-- PANEL AMARILLO DE REPARACIÓN TEMPORAL -->
                <div id="panelJustificacionTemporal" style="display: <?php echo ($row['modalidad'] == 'Temporal') ? 'block' : 'none'; ?>; margin-top: 20px; padding: 20px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px;">
                    
                    <h4 style="color: #856404; margin-top: 0; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                        ⏳ Justificación de reparación temporal
                    </h4>

                    <div style="margin-bottom: 10px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 8px; color: #856404;">Selecciona el motivo principal:</label>
                        <div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
                            
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500; color: #856404;">
                                <input type="radio" name="motivo_temporal" value="Falta de piezas"> 🔧 Falta de piezas
                            </label>
                            
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500; color: #856404;">
                                <input type="radio" name="motivo_temporal" value="Equipo ya no funciona"> ⚠️ Equipo ya no funciona
                            </label>
                            
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500; color: #856404;">
                                <input type="radio" name="motivo_temporal" value="Se necesitan REFACCIONES para una reparación DEFINITIVA"> ⚙️ Se necesitan REFACCIONES para una reparación DEFINITIVA
                            </label>
                            
                        </div>
                    </div>

                    <hr style="border: 0; border-top: 1px solid #e0b574; margin: 15px 0;">

                    <div style="padding-bottom: 5px;">
                        <div id="seccionFaltaPiezas" style="display: none; margin-bottom: 5px;">
                            <label style="font-weight: 600; display: block; margin-bottom: 4px; color: #856404;">Descripción de la pieza faltante:</label>
                            <input type="text" name="descripcion_pieza_faltante" style="width: 100%; padding: 8px; border: 2px solid #dce1e8; border-radius: 6px; font-size: 14px; background: #fff;">
                        </div>
                        <div id="seccionNoFunciona" style="display: none; margin-bottom: 5px;">
                            <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500; color: #856404;">
                                    <input type="checkbox" name="motivo_irreparable"> El daño es IRREPARABLE.
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500; color: #856404;">
                                    <input type="checkbox" name="motivo_obsoleto"> El equipo es OBSOLETO.
                                </label>
                            </div>
                        </div>
                        <div id="seccionRefacciones" style="display: none; margin-bottom: 5px;">
                            <label style="font-weight: 600; display: block; margin-bottom: 4px; color: #856404;">Número de Parte:</label>
                            <input type="text" name="numero_parte" style="width: 100%; padding: 8px; border: 2px solid #dce1e8; border-radius: 6px; font-size: 14px; background: #fff;">
                        </div>
                    </div>

                    <hr style="border: 0; border-top: 1px solid #e0b574; margin: 15px 0;">

                    <div>
                        <label style="font-weight: 600; display: block; margin-bottom: 8px; color: #856404;">Observaciones adicionales (opcional):</label>
                        <textarea name="obs_temporal" rows="3" style="width: 100%; padding: 10px; border: 2px solid #dce1e8; border-radius: 8px; font-size: 14px; font-family: inherit; resize: vertical; background: #fff;" placeholder="Detalla cualquier información adicional sobre la reparación temporal..."></textarea>
                    </div>

                </div>

                <div class="form-grid" style="margin-top: 20px;">
                    <div class="campo">
                        <label for="nombre_recibe">Nombre de quién recibe el equipo</label>
                        <input type="text" id="nombre_recibe" name="nombre_recibe" value="<?php echo htmlspecialchars($row['nombre_recibe'] ?? ''); ?>" placeholder="Nombre de quién recibe el equipo">
                    </div>
                    <div class="campo">
                        <label for="soporte_entrega">Soporte Técnico quién entrega el equipo</label>
                        <select id="soporte_entrega" name="soporte_entrega">
                            <option value="">Selecciona el personal</option>
                            <option value="Ana María Pérez" <?php if($row['soporte_entrega'] == 'Ana María Pérez') echo 'selected'; ?>>Ana María Pérez</option>
                            <option value="Carlos Eduardo Ramírez" <?php if($row['soporte_entrega'] == 'Carlos Eduardo Ramírez') echo 'selected'; ?>>Carlos Eduardo Ramírez</option>
                            <option value="Laura Gutiérrez Ortiz" <?php if($row['soporte_entrega'] == 'Laura Gutiérrez Ortiz') echo 'selected'; ?>>Laura Gutiérrez Ortiz</option>
                            <option value="Jorge Luis Sánchez" <?php if($row['soporte_entrega'] == 'Jorge Luis Sánchez') echo 'selected'; ?>>Jorge Luis Sánchez</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="acciones-form">
                <a href="gestion_servicio.php" class="btn btn-cancelar">✕ Cancelar</a>
                <button type="submit" class="btn btn-guardar">💾 Guardar Cambios</button>
            </div>
        </form>

    </div>

    <script>
        // Lógica para mostrar/ocultar el panel amarillo (Temporal)
        const radiosModalidad = document.querySelectorAll('input[name="modalidad"]');
        const panelJustificacion = document.getElementById('panelJustificacionTemporal');

        radiosModalidad.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'Temporal') {
                    panelJustificacion.style.display = 'block';
                } else {
                    panelJustificacion.style.display = 'none';
                }
            });
        });

        // Lógica para mostrar/ocultar sub-secciones según el motivo
        const radiosMotivo = document.querySelectorAll('input[name="motivo_temporal"]');
        const seccionFaltaPiezas = document.getElementById('seccionFaltaPiezas');
        const seccionNoFunciona = document.getElementById('seccionNoFunciona');
        const seccionRefacciones = document.getElementById('seccionRefacciones');

        radiosMotivo.forEach(radio => {
            radio.addEventListener('change', function() {
                seccionFaltaPiezas.style.display = 'none';
                seccionNoFunciona.style.display = 'none';
                seccionRefacciones.style.display = 'none';

                if (this.value === 'Falta de piezas') {
                    seccionFaltaPiezas.style.display = 'block';
                } else if (this.value === 'Equipo ya no funciona') {
                    seccionNoFunciona.style.display = 'block';
                } else if (this.value === 'Se necesitan REFACCIONES para una reparación DEFINITIVA') {
                    seccionRefacciones.style.display = 'block';
                }
            });
        });
    </script>
</body>
</html>