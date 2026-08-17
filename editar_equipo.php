<?php
include 'config.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar que se recibió un ID
if (!isset($_GET['id'])) {
    header('Location: inventario.php');
    exit;
}

$id = intval($_GET['id']);
$mensaje = '';
$tipo_mensaje = '';

// Obtener datos del equipo
$sql = "SELECT * FROM servicios WHERE id = $id";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    header('Location: inventario.php');
    exit;
}
$row = $result->fetch_assoc();

// Procesar formulario de edición (Solo los campos del formulario simplificado)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_solicitante = $_POST['nombre_solicitante'];
    $subsecretaria = $_POST['subsecretaria'];
    $direccion = $_POST['direccion'];
    $departamento = $_POST['departamento'];
    $descripcion = $_POST['descripcion'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $serie = $_POST['serie'];
    $numero_inventario = $_POST['numero_inventario'];
    $fecha_registro = $_POST['fecha_registro'];

    if (empty($nombre_solicitante) || empty($descripcion)) {
        $mensaje = 'El nombre del responsable y la descripción del equipo son obligatorios.';
        $tipo_mensaje = 'danger';
    } else {
        // UPDATE simplificado (sin folio, sin soporte, sin situación final)
        $sql_update = "UPDATE servicios SET 
            nombre_solicitante='$nombre_solicitante',
            subsecretaria='$subsecretaria', direccion='$direccion', departamento='$departamento',
            descripcion_equipo='$descripcion', marca='$marca', modelo='$modelo', serie='$serie',
            numero_inventario='$numero_inventario', fecha_registro='$fecha_registro'
            WHERE id = $id";

        if ($conn->query($sql_update)) {
            header('Location: inventario.php?mensaje=Equipo actualizado correctamente');
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
    <title>Editar Equipo</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container { max-width: 1000px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 8px 30px rgba(0,0,0,0.1); }

        /* ============================================== */
        /* 👇 TODOS LOS CAMBIOS DE COLOR SON AQUÍ ABAJO 👇 */
        /* ============================================== */

        /* Línea separadora y título principal */
        .header-form { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; border-bottom: 3px solid #ffc107; padding-bottom: 15px; margin-bottom: 25px; }
        .header-form h1 { color: #ffc107; font-size: 24px; }
        
        .btn-volver { background: #6c757d; color: #fff; padding: 10px 25px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s; }
        .btn-volver:hover { background: #5a6268; transform: translateY(-2px); }

        /* Borde izquierdo y títulos de las secciones */
        .seccion-form { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px; border-left: 4px solid #ffc107; }
        .seccion-form h3 { color: #ffc107; margin-bottom: 15px; font-size: 18px; border-bottom: 1px solid #e6eaef; padding-bottom: 10px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 25px; }
        .form-grid .campo-full { grid-column: 1 / -1; }
        .campo { display: flex; flex-direction: column; gap: 4px; }
        .campo label { font-weight: 600; font-size: 14px; color: #2c3e50; }
        .campo label .obligatorio { color: #dc3545; font-weight: 700; }
        .campo input, .campo select { padding: 10px 12px; border: 2px solid #dce1e8; border-radius: 8px; font-size: 14px; transition: 0.3s; font-family: inherit; width: 100%; }
        .campo input:focus, .campo select:focus { border-color: #ffc107; outline: none; box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.1); }
        
        /* Tabla de equipos (Encabezado y bordes al enfocar) */
        .tabla-equipos { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 14px; }
        .tabla-equipos thead { background: #ffc107; color: #fff; }
        .tabla-equipos th { padding: 10px; text-align: left; font-weight: 600; }
        .tabla-equipos td { padding: 8px 10px; border-bottom: 1px solid #e6eaef; }
        .tabla-equipos select, .tabla-equipos input { width: 100%; padding: 8px; border: 2px solid #dce1e8; border-radius: 6px; font-size: 13px; }
        .tabla-equipos select:focus, .tabla-equipos input:focus { border-color: #ffc107; outline: none; }

        /* Botones de abajo */
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
            <h1>✏️ Editar Equipo</h1>
            <a href="inventario.php" class="btn-volver">⬅ Volver al Inventario</a>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
                <button class="close-btn" onclick="this.parentElement.style.display='none'">✕</button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            
            <div class="seccion-form">
                <h3>💻 Datos del Equipo y Dependencia</h3>
                
                <table class="tabla-equipos">
                    <thead>
                        <tr>
                            <th>DESCRIPCIÓN</th>
                            <th>MARCA</th>
                            <th>MODELO</th>
                            <th>SERIE</th>
                            <th>N° INVENTARIO</th>
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
                            <td>
                                <input type="text" name="numero_inventario" value="<?php echo htmlspecialchars($row['numero_inventario'] ?? ''); ?>" placeholder="Ej: P-211111..." style="width: 140px;">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div style="margin-top: 20px; display: flex; gap: 20px; flex-wrap: wrap; align-items: center;">
                    <div style="flex: 1; min-width: 200px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px; font-size: 14px;">📅 Fecha de Registro:</label>
                        <input type="date" name="fecha_registro" value="<?php echo htmlspecialchars($row['fecha_registro'] ?? date('Y-m-d')); ?>" style="padding: 10px; border: 2px solid #dce1e8; border-radius: 8px; font-size: 14px; width: 100%;">
                    </div>
                </div>

                <div style="margin-top: 15px;">
                    <h4 style="color: #ffc107; margin-bottom: 10px; border-top: 1px solid #e6eaef; padding-top: 15px;">🏛️ Datos de la Dependencia</h4>
                    <div class="form-grid">
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
                
                <div style="margin-top: 15px;">
                    <div class="campo campo-full">
                        <label for="nombre_solicitante">Nombre del Responsable del equipo <span class="obligatorio">*</span></label>
                        <input type="text" id="nombre_solicitante" name="nombre_solicitante" value="<?php echo htmlspecialchars($row['nombre_solicitante'] ?? ''); ?>" placeholder="Nombre de la persona a cargo del equipo" required>
                    </div>
                </div>
            </div>

            <div class="acciones-form">
                <a href="inventario.php" class="btn btn-cancelar">✕ Cancelar</a>
                <button type="submit" class="btn btn-guardar">💾 Guardar Cambios</button>
            </div>
        </form>
    </div>
</body>
</html>