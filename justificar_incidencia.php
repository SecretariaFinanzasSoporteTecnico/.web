<?php
include 'config.php';

// ===== OBTENER ID DE LA INCIDENCIA Y USUARIO =====
$incidencia_id = isset($_GET['incidencia_id']) ? intval($_GET['incidencia_id']) : 0;
$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;

if ($incidencia_id == 0 || $usuario_id == 0) {
    header('Location: index.php?error=Datos no válidos');
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

// ===== OBTENER DATOS DE LA INCIDENCIA =====
$sql_incidencia = "SELECT * FROM incidencias WHERE id = $incidencia_id AND usuario_id = $usuario_id";
$result_incidencia = $conn->query($sql_incidencia);
if ($result_incidencia->num_rows == 0) {
    header('Location: ver_incidencia.php?usuario_id=' . $usuario_id . '&error=Incidencia no encontrada');
    exit;
}
$incidencia = $result_incidencia->fetch_assoc();

// ===== PROCESAR FORMULARIO =====
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = $_POST['titulo'];
    $prioridad = $_POST['prioridad'];
    $descripcion = $_POST['descripcion'];
    $pasos = $_POST['pasos'];
    $evidencia = $_POST['evidencia'];
    $causa_raiz = $_POST['causa_raiz'];
    $accion_inmediata = $_POST['accion_inmediata'];
    $solucion_definitiva = $_POST['solucion_definitiva'];
    $plazo = $_POST['plazo'];
    
    // Validar campos obligatorios
    if (empty($titulo) || empty($prioridad) || empty($descripcion)) {
        $mensaje = 'Los campos Título, Prioridad y Descripción son obligatorios.';
        $tipo_mensaje = 'danger';
    } else {
        // Actualizar la incidencia con la justificación
        $sql_update = "UPDATE incidencias SET 
            justificada = 1,
            titulo = '$titulo',
            prioridad = '$prioridad',
            descripcion = '$descripcion',
            pasos = '$pasos',
            evidencia = '$evidencia',
            causa_raiz = '$causa_raiz',
            accion_inmediata = '$accion_inmediata',
            solucion_definitiva = '$solucion_definitiva',
            plazo = '$plazo',
            fecha_justificacion = NOW()
        WHERE id = $incidencia_id AND usuario_id = $usuario_id";
        
        if ($conn->query($sql_update)) {
            $mensaje = '✅ Incidencia justificada correctamente.';
            $tipo_mensaje = 'success';
            // Redirigir después de 2 segundos
            echo '<script>setTimeout(function(){ window.location.href = "ver_incidencia.php?usuario_id=' . $usuario_id . '&mensaje=Incidencia justificada correctamente"; }, 2000);</script>';
        } else {
            $mensaje = '❌ Error al justificar: ' . $conn->error;
            $tipo_mensaje = 'danger';
        }
    }
}

// Generar ID de justificación
$id_justificacion = 'INC-' . date('Y') . '-' . str_pad($incidencia_id, 3, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Justificar Incidencia - <?php echo htmlspecialchars($usuario['nombre']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* ========================================
           ESTILOS DEL FORMULARIO DE JUSTIFICACIÓN
           ======================================== */
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        
        .header-form {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            border-bottom: 3px solid #1a3a5c;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        
        .header-form h2 {
            color: #1a3a5c;
            margin: 0;
        }
        
        .header-form .id-justificacion {
            background: #f8f9fa;
            padding: 8px 20px;
            border-radius: 8px;
            border-left: 4px solid #b8860b;
            font-weight: 700;
            color: #1a3a5c;
            font-size: 16px;
        }
        
        .info-usuario {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        
        .info-usuario .campo {
            display: flex;
            flex-direction: column;
        }
        
        .info-usuario .campo label {
            font-size: 11px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        .info-usuario .campo span {
            font-size: 14px;
            font-weight: 600;
            color: #1a3a5c;
        }
        
        /* RECUADRO AMARILLO CORREGIDO AL 100% */
        .info-incidencia {
            background: #fff3cd;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #ffc107;
            line-height: 1.6;
        }
        
        .info-incidencia strong {
            color: #856404;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px 25px;
        }
        
        .form-grid .campo-full {
            grid-column: 1 / -1;
        }
        
        .campo {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .campo label {
            font-weight: 600;
            font-size: 14px;
            color: #2c3e50;
        }
        
        .campo label .obligatorio {
            color: #dc3545;
            font-weight: 700;
        }
        
        .campo input,
        .campo select,
        .campo textarea {
            padding: 10px 12px;
            border: 2px solid #dce1e8;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.3s;
            font-family: inherit;
        }
        
        .campo input:focus,
        .campo select:focus,
        .campo textarea:focus {
            border-color: #1a3a5c;
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1);
        }
        
        .campo textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .campo .ayuda {
            font-size: 12px;
            color: #6c757d;
            margin-top: 3px;
        }
        
        .campo .ayuda .icono {
            margin-right: 3px;
        }
        
        .acciones-form {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #e6eaef;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-guardar {
            background: #1a3a5c;
            color: #fff;
        }
        
        .btn-guardar:hover {
            background: #0f2a44;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 58, 92, 0.3);
        }
        
        .btn-cancelar {
            background: #e9edf2;
            color: #3d4f62;
        }
        
        .btn-cancelar:hover {
            background: #d5dce6;
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
        
        .close-btn:hover {
            opacity: 1;
        }
        
        .badge-prioridad {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-critica { background: #dc3545; color: #fff; }
        .badge-alta { background: #fd7e14; color: #fff; }
        .badge-media { background: #ffc107; color: #1e2a3a; }
        .badge-baja { background: #28a745; color: #fff; }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .header-form {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
            
            .info-usuario {
                grid-template-columns: 1fr;
            }
            
            .acciones-form {
                flex-direction: column;
            }
            
            .acciones-form .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- ========================================
             CABECERA
             ======================================== -->
        <div class="header-form">
            <h2>📝 Justificar Incidencia</h2>
            <div class="id-justificacion">📄 <?php echo $id_justificacion; ?></div>
        </div>
        
        <!-- ========================================
             INFORMACIÓN DEL USUARIO
             ======================================== -->
        <div class="info-usuario">
            <div class="campo">
                <label>👤 Empleado</label>
                <span><?php echo htmlspecialchars($usuario['nombre']); ?></span>
            </div>
            <div class="campo">
                <label>📧 Email</label>
                <span><?php echo htmlspecialchars($usuario['email']); ?></span>
            </div>
            <div class="campo">
                <label>🏢 Área</label>
                <span><?php echo htmlspecialchars($usuario['area']); ?></span>
            </div>
            <div class="campo">
                <label>💼 Puesto</label>
                <span><?php echo htmlspecialchars($usuario['puesto']); ?></span>
            </div>
        </div>
        
        <!-- ========================================
             INFORMACIÓN DE LA INCIDENCIA
             ======================================== -->
        <div class="info-incidencia">
            <?php 
                $tipo_real = $incidencia['tipo_incidencia'] ?? 'No especificado';
                $motivo_real = $incidencia['motivo'] ?? 'Sin motivo';
                
                $dias_calculados = 0;
                if (!empty($incidencia['fecha_inicio']) && !empty($incidencia['fecha_fin'])) {
                    $inicio = new DateTime($incidencia['fecha_inicio']);
                    $fin = new DateTime($incidencia['fecha_fin']);
                    $intervalo = $inicio->diff($fin);
                    $dias_calculados = $intervalo->days + 1;
                }
                
                $fecha_inicio_mostrar = !empty($incidencia['fecha_inicio']) ? date('d/m/Y', strtotime($incidencia['fecha_inicio'])) : 'Sin fecha';
            ?>

            <strong>Tipo:</strong> <span class="tipo"><?php echo htmlspecialchars($tipo_real); ?></span>
            &nbsp;|&nbsp;
            <strong>Fecha Inicio:</strong> <span class="fecha"><?php echo $fecha_inicio_mostrar; ?></span>
            &nbsp;|&nbsp;
            <strong>Días:</strong> <?php echo $dias_calculados; ?>
            &nbsp;|&nbsp;
            <strong>Motivo original:</strong> <?php echo htmlspecialchars($motivo_real); ?>
        </div>
        
        <!-- ========================================
             MENSAJES DE ALERTA
             ======================================== -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
                <button class="close-btn" onclick="this.parentElement.style.display='none'">✕</button>
            </div>
        <?php endif; ?>
        
        <!-- ========================================
             FORMULARIO DE JUSTIFICACIÓN
             ======================================== -->
        <form method="POST" action="">
            <div class="form-grid">
                
                <!-- Título -->
                <div class="campo campo-full">
                    <label for="titulo">📌 Título de la incidencia <span class="obligatorio">*</span></label>
                    <input type="text" id="titulo" name="titulo" required placeholder="Ej: Caída del servicio de correo en el área comercial" value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>">
                    <span class="ayuda"><span class="icono">ℹ️</span> Describe brevemente el problema</span>
                </div>
                
                <!-- Prioridad -->
                <div class="campo">
                    <label for="prioridad">⚡ Prioridad <span class="obligatorio">*</span></label>
                    <select id="prioridad" name="prioridad" required>
                        <option value="">Selecciona la prioridad</option>
                        <option value="Crítica" <?php echo (isset($_POST['prioridad']) && $_POST['prioridad'] == 'Crítica') ? 'selected' : ''; ?>>🔴 Crítica - Afecta a múltiples usuarios</option>
                        <option value="Alta" <?php echo (isset($_POST['prioridad']) && $_POST['prioridad'] == 'Alta') ? 'selected' : ''; ?>>🟠 Alta - Afecta a un área o proceso clave</option>
                        <option value="Media" <?php echo (isset($_POST['prioridad']) && $_POST['prioridad'] == 'Media') ? 'selected' : ''; ?>>🟡 Media - Afecta a usuarios individuales</option>
                        <option value="Baja" <?php echo (isset($_POST['prioridad']) && $_POST['prioridad'] == 'Baja') ? 'selected' : ''; ?>>🟢 Baja - Problema menor o cosmético</option>
                    </select>
                </div>
                
                <!-- Plazo -->
                <div class="campo">
                    <label for="plazo">⏱️ Plazo de solución</label>
                    <input type="text" id="plazo" name="plazo" placeholder="Ej: 48 horas para implementar la política" value="<?php echo isset($_POST['plazo']) ? htmlspecialchars($_POST['plazo']) : ''; ?>">
                </div>
                
                <!-- Descripción -->
                <div class="campo campo-full">
                    <label for="descripcion">📄 Descripción del problema <span class="obligatorio">*</span></label>
                    <textarea id="descripcion" name="descripcion" rows="4" required placeholder="Describe detalladamente el problema..."><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
                    <span class="ayuda"><span class="icono">💡</span> Incluye contexto, impacto y detalles relevantes</span>
                </div>
                
                <!-- Pasos para reproducir -->
                <div class="campo campo-full">
                    <label for="pasos">🔍 Pasos para reproducir</label>
                    <textarea id="pasos" name="pasos" rows="3" placeholder="1. Abrir Outlook. 2. Intentar enviar correo con adjunto mayor a 5MB. 3. Ver el error."><?php echo isset($_POST['pasos']) ? htmlspecialchars($_POST['pasos']) : ''; ?></textarea>
                    <span class="ayuda"><span class="icono">📋</span> Enumera los pasos para replicar el problema</span>
                </div>
                
                <!-- Evidencia -->
                <div class="campo campo-full">
                    <label for="evidencia">📎 Evidencia</label>
                    <textarea id="evidencia" name="evidencia" rows="2" placeholder="Adjuntar captura de pantalla del error o enlace al registro"><?php echo isset($_POST['evidencia']) ? htmlspecialchars($_POST['evidencia']) : ''; ?></textarea>
                    <span class="ayuda"><span class="icono">🖼️</span> Capturas de pantalla, logs, enlaces, etc.</span>
                </div>
                
                <!-- Causa raíz -->
                <div class="campo campo-full">
                    <label for="causa_raiz">🔬 Causa raíz</label>
                    <textarea id="causa_raiz" name="causa_raiz" rows="2" placeholder="El buzón de correo llegó al 100% de su capacidad"><?php echo isset($_POST['causa_raiz']) ? htmlspecialchars($_POST['causa_raiz']) : ''; ?></textarea>
                    <span class="ayuda"><span class="icono">🔎</span> ¿Cuál es la causa principal del problema?</span>
                </div>
                
                <!-- Acción inmediata -->
                <div class="campo campo-full">
                    <label for="accion_inmediata">⚡ Acción inmediata</label>
                    <textarea id="accion_inmediata" name="accion_inmediata" rows="2" placeholder="Se amplió temporalmente el almacenamiento en la nube"><?php echo isset($_POST['accion_inmediata']) ? htmlspecialchars($_POST['accion_inmediata']) : ''; ?></textarea>
                    <span class="ayuda"><span class="icono">🛠️</span> ¿Qué se hizo para mitigar el problema de forma inmediata?</span>
                </div>
                
                <!-- Solución definitiva -->
                <div class="campo campo-full">
                    <label for="solucion_definitiva">✅ Solución definitiva</label>
                    <textarea id="solucion_definitiva" name="solucion_definitiva" rows="2" placeholder="Implementar política de borrado automático de correos antiguos"><?php echo isset($_POST['solucion_definitiva']) ? htmlspecialchars($_POST['solucion_definitiva']) : ''; ?></textarea>
                    <span class="ayuda"><span class="icono">🎯</span> ¿Cuál es la solución permanente?</span>
                </div>
                
            </div>
            
            <!-- ========================================
                 BOTONES DE ACCIÓN (ÚNICOS Y BIEN UBICADOS)
                 ======================================== -->
            <div class="acciones-form">
                <a href="ver_incidencia.php?usuario_id=<?php echo $usuario_id; ?>" class="btn btn-cancelar">✕ Cancelar</a>
                <button type="submit" class="btn btn-guardar">💾 Guardar Justificación</button>
            </div>
        </form>
        
    </div>
    
    <script>
        // ========================================
        // AUTO-OCULTAR MENSAJES
        // ========================================
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>