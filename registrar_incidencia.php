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

// ===== OBTENER CONTADORES DE INCIDENCIAS =====
$sql_contadores = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN tipo = 'Vacaciones' THEN 1 ELSE 0 END) as vacaciones,
    SUM(CASE WHEN tipo = 'Comisión' THEN 1 ELSE 0 END) as comisiones,
    SUM(CASE WHEN tipo = 'Permiso Económico' THEN 1 ELSE 0 END) as permisos,
    SUM(CASE WHEN tipo = 'Omisión Entrada' THEN 1 ELSE 0 END) as omision_entrada,
    SUM(CASE WHEN tipo = 'Omisión Salida' THEN 1 ELSE 0 END) as omision_salida,
    SUM(CASE WHEN tipo = 'Cumpleaños Confianza' THEN 1 ELSE 0 END) as cumpleanos
FROM incidencias 
WHERE usuario_id = $usuario_id";
$result_contadores = $conn->query($sql_contadores);
$contadores = $result_contadores->fetch_assoc();

// ===== PROCESAR REGISTRO DE INCIDENCIA =====
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo = $_POST['tipo'];
    $fecha = $_POST['fecha'];
    $dias = $_POST['dias'];
    $motivo = $_POST['motivo'];
    $observaciones = $_POST['observaciones'];
    $fecha_registro = date('Y-m-d H:i:s');
    
    // Validar campos obligatorios
    if (empty($tipo) || empty($fecha) || empty($dias) || empty($motivo)) {
        $mensaje = 'Todos los campos obligatorios deben estar llenos.';
        $tipo_mensaje = 'danger';
    } else {
        // Insertar incidencia
        $sql_insert = "INSERT INTO incidencias (
            usuario_id, 
            tipo, 
            fecha, 
            dias, 
            motivo, 
            observaciones, 
            fecha_registro
        ) VALUES (
            $usuario_id, 
            '$tipo', 
            '$fecha', 
            '$dias', 
            '$motivo', 
            '$observaciones', 
            '$fecha_registro'
        )";
        
        if ($conn->query($sql_insert)) {
            $mensaje = '✅ Incidencia registrada correctamente.';
            $tipo_mensaje = 'success';
            
            // Actualizar contadores
            $sql_contadores = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN tipo = 'Vacaciones' THEN 1 ELSE 0 END) as vacaciones,
                SUM(CASE WHEN tipo = 'Comisión' THEN 1 ELSE 0 END) as comisiones,
                SUM(CASE WHEN tipo = 'Permiso Económico' THEN 1 ELSE 0 END) as permisos,
                SUM(CASE WHEN tipo = 'Omisión Entrada' THEN 1 ELSE 0 END) as omision_entrada,
                SUM(CASE WHEN tipo = 'Omisión Salida' THEN 1 ELSE 0 END) as omision_salida,
                SUM(CASE WHEN tipo = 'Cumpleaños Confianza' THEN 1 ELSE 0 END) as cumpleanos
            FROM incidencias 
            WHERE usuario_id = $usuario_id";
            $result_contadores = $conn->query($sql_contadores);
            $contadores = $result_contadores->fetch_assoc();
        } else {
            $mensaje = '❌ Error al registrar la incidencia: ' . $conn->error;
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
    <title>Registrar Incidencia - <?php echo htmlspecialchars($usuario['nombre']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* ========================================
           ESTILOS PARA INCIDENCIAS
           ======================================== */
        .perfil-usuario {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid #1a3a5c;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .perfil-usuario .campo {
            display: flex;
            flex-direction: column;
        }
        
        .perfil-usuario .campo label {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .perfil-usuario .campo span {
            font-size: 16px;
            font-weight: 600;
            color: #1a3a5c;
            margin-top: 3px;
        }
        
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
            transition: all 0.3s;
        }
        
        .contador-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
        
        .contador-card.vacaciones .numero { color: #28a745; }
        .contador-card.comisiones .numero { color: #17a2b8; }
        .contador-card.permisos .numero { color: #fd7e14; }
        .contador-card.omisiones .numero { color: #dc3545; }
        .contador-card.cumpleanos .numero { color: #6f42c1; }
        .contador-card.total .numero { color: #1a3a5c; }
        
        .form-incidencia {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-top: 20px;
            border: 1px solid #e6eaef;
        }
        
        .form-incidencia h3 {
            color: #1a3a5c;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e6eaef;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }
        
        .form-grid .campo {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .form-grid .campo.ancho-completo {
            grid-column: 1 / -1;
        }
        
        .form-grid label {
            font-weight: 600;
            font-size: 14px;
            color: #2c3e50;
        }
        
        .form-grid input,
        .form-grid select,
        .form-grid textarea {
            padding: 10px 12px;
            border: 2px solid #dce1e8;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.3s;
            font-family: inherit;
        }
        
        .form-grid input:focus,
        .form-grid select:focus,
        .form-grid textarea:focus {
            border-color: #1a3a5c;
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1);
        }
        
        .form-grid textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .acciones-form {
            display: flex;
            gap: 15px;
            margin-top: 20px;
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
        
        .btn-ver-incidencias {
            background: #17a2b8;
            color: #fff;
        }
        
        .btn-ver-incidencias:hover {
            background: #138496;
        }
        
        /* ========================================
           ALERTAS
           ======================================== */
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
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .perfil-usuario {
                grid-template-columns: 1fr;
            }
            
            .contadores {
                grid-template-columns: repeat(2, 1fr);
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
             PERFIL DEL USUARIO
             ======================================== -->
        <div class="perfil-usuario">
            <div class="campo">
                <label>👤 Nombre</label>
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
            <div class="campo">
                <label>🔑 NIP</label>
                <span><?php echo htmlspecialchars($usuario['nip'] ?? 'No asignado'); ?></span>
            </div>
        </div>
        
        <!-- ========================================
             CONTADORES DE INCIDENCIAS
             ======================================== -->
        <div class="contadores">
            <div class="contador-card total">
                <span class="numero"><?php echo $contadores['total'] ?? 0; ?></span>
                <span class="etiqueta">📊 Total Incidencias</span>
            </div>
            <div class="contador-card vacaciones">
                <span class="numero"><?php echo $contadores['vacaciones'] ?? 0; ?></span>
                <span class="etiqueta">🏖️ Vacaciones</span>
            </div>
            <div class="contador-card comisiones">
                <span class="numero"><?php echo $contadores['comisiones'] ?? 0; ?></span>
                <span class="etiqueta">📋 Comisiones</span>
            </div>
            <div class="contador-card permisos">
                <span class="numero"><?php echo $contadores['permisos'] ?? 0; ?></span>
                <span class="etiqueta">💰 Permisos Económicos</span>
            </div>
            <div class="contador-card omisiones">
                <span class="numero"><?php echo ($contadores['omision_entrada'] ?? 0) + ($contadores['omision_salida'] ?? 0); ?></span>
                <span class="etiqueta">⚠️ Omisiones</span>
            </div>
            <div class="contador-card cumpleanos">
                <span class="numero"><?php echo $contadores['cumpleanos'] ?? 0; ?></span>
                <span class="etiqueta">🎂 Cumpleaños Confianza</span>
            </div>
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
             FORMULARIO DE REGISTRO DE INCIDENCIA
             ======================================== -->
        <div class="form-incidencia">
            <h3>📝 Registrar Nueva Incidencia</h3>
            <form method="POST" action="">
                <div class="form-grid">
                    <!-- Tipo de incidencia -->
                    <div class="campo">
                        <label for="tipo">Tipo de Incidencia *</label>
                        <select id="tipo" name="tipo" required>
                            <option value="">Selecciona el tipo</option>
                            <option value="Vacaciones">🏖️ Vacaciones</option>
                            <option value="Comisión">📋 Comisión</option>
                            <option value="Permiso Económico">💰 Permiso Económico</option>
                            <option value="Omisión Entrada">⚠️ Omisión de Entrada</option>
                            <option value="Omisión Salida">⚠️ Omisión de Salida</option>
                            <option value="Cumpleaños Confianza">🎂 Cumpleaños Personal de Confianza</option>
                            <option value="Otra">📌 Otra</option>
                        </select>
                    </div>
                    
                    <!-- Fecha -->
                    <div class="campo">
                        <label for="fecha">Fecha de Incidencia *</label>
                        <input type="date" id="fecha" name="fecha" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <!-- Días a justificar -->
                    <div class="campo">
                        <label for="dias">Días a Justificar *</label>
                        <input type="number" id="dias" name="dias" min="0.5" step="0.5" required value="1">
                    </div>
                    
                    <!-- Motivo -->
                    <div class="campo ancho-completo">
                        <label for="motivo">Motivo de la Incidencia *</label>
                        <textarea id="motivo" name="motivo" rows="3" required placeholder="Describe detalladamente el motivo..."></textarea>
                    </div>
                    
                    <!-- Observaciones -->
                    <div class="campo ancho-completo">
                        <label for="observaciones">Observaciones Adicionales</label>
                        <textarea id="observaciones" name="observaciones" rows="2" placeholder="Comentarios adicionales (opcional)..."></textarea>
                    </div>
                </div>
                
                <div class="acciones-form">
                    <a href="ver_incidencia.php?usuario_id=<?php echo $usuario_id; ?>" class="btn btn-ver-incidencias">
                        📋 Ver Incidencias
                    </a>
                    <a href="index.php" class="btn btn-cancelar">✕ Cancelar</a>
                    <button type="submit" class="btn btn-guardar">💾 Registrar Incidencia</button>
                </div>
            </form>
        </div>
        
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