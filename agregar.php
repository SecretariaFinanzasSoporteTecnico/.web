<?php
include 'config.php';

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
    <title>Registrar Usuario</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e0e0e0;
        }
        .form-container .header-form {
            text-align: center;
            border-bottom: 2px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .form-container .header-form h2 {
            color: #667eea;
            font-size: 24px;
            margin: 0;
        }
        .form-container .header-form .subtitle {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .seccion-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid #667eea;
        }
        .seccion-form h4 {
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .seccion-form h4 .icono {
            font-size: 20px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .form-group label {
            font-weight: 600;
            color: #555;
            font-size: 13px;
        }
        .form-group label .required {
            color: #dc3545;
            margin-left: 3px;
        }
        .form-group input, .form-group select {
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            background: white;
            width: 100%;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .btn-guardar {
            padding: 14px 50px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-guardar:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(40, 167, 69, 0.4);
        }
        .btn-cancelar {
            padding: 14px 30px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .btn-cancelar:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        .info-ayuda {
            background: #e7f3ff;
            padding: 12px 18px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            margin-top: 15px;
            color: #333;
            font-size: 13px;
        }
        .info-ayuda strong {
            color: #667eea;
        }
        @media (max-width: 768px) {
            .form-container { padding: 20px; }
            .form-grid { grid-template-columns: 1fr; }
            .form-actions { flex-direction: column; }
            .btn-guardar, .btn-cancelar { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <!-- ✅ ELIMINÉ EL ENCABEZADO DE ARRIBA (Gestión de Usuarios y Volver) -->
    
    <div class="container">
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
                <button class="close-btn" onclick="this.parentElement.style.display='none'">✕</button>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <div class="header-form">
                <h2>📄 REGISTRO DE USUARIO</h2>
                <div class="subtitle">Sistema de Gestión de Personal</div>
            </div>

            <form action="guardar_usuario_completo.php" method="POST" id="formUsuario">

                <div class="seccion-form">
                    <h4><span class="icono">👤</span> Datos Personales</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nombre completo <span class="required">*</span></label>
                            <input type="text" name="nombre" placeholder="Ej: ALEJANDRO MANUEL VELA SÁNCHEZ" required>
                        </div>
                        <div class="form-group">
                            <label>Correo electrónico <span class="required">*</span></label>
                            <input type="email" name="email" placeholder="Ej: alejandro.vela@email.com" required>
                        </div>
                        <div class="form-group">
                            <label>Número de Identificación Personal (NIP)</label>
                            <input type="text" name="nip" placeholder="Ej: 1566">
                        </div>
                        <div class="form-group">
                            <label>Teléfono / Extensión</label>
                            <input type="text" name="telefono" placeholder="Ej: 65806">
                        </div>
                        <div class="form-group">
                            <label>Área de Adscripción <span class="required">*</span></label>
                            <input type="text" name="area" placeholder="Ej: ÁREA DE SOPORTE TECNICO" required>
                        </div>
                        <div class="form-group">
                            <label>Puesto <span class="required">*</span></label>
                            <input type="text" name="puesto" placeholder="Ej: ANALISTA H" required>
                        </div>
                        <div class="form-group">
                            <label>Tipo de Contrato</label>
                            <select name="tipo_contrato">
                                <option value="">Seleccionar...</option>
                                <option value="BASE">BASE</option>
                                <option value="CONFIANZA">CONFIANZA</option>
                                <option value="SINDICALIZADO">SINDICALIZADO</option>
                                <option value="HONORARIOS">HONORARIOS</option>
                                <option value="TEMPORAL">TEMPORAL</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fecha de Ingreso</label>
                            <input type="date" name="fecha_ingreso">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-guardar">💾 Guardar Usuario</button>
                    <a href="index.php" class="btn-cancelar">❌ Cancelar</a>
                </div>
            </form>

            <div class="info-ayuda">
                <strong>ℹ️ Información:</strong> Los campos marcados con <strong>*</strong> son obligatorios. 
                Los datos personales proporcionados serán tratados de manera confidencial.
            </div>
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
        
        document.getElementById('formUsuario').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.btn-guardar');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '⏳ Guardando...';
        });
    </script>
</body>
</html>