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
$usuario = $result_usuario->fetch_assoc();

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha = $_POST['fecha'];
    $tipo = $_POST['tipo'];
    
    if (empty($fecha) || empty($tipo)) {
        $mensaje = 'Todos los campos son obligatorios.';
        $tipo_mensaje = 'danger';
    } else {
        $sql_insert = "INSERT INTO faltas (usuario_id, fecha, tipo) VALUES ($usuario_id, '$fecha', '$tipo')";
        
        if ($conn->query($sql_insert)) {
            header('Location: faltas.php?usuario_id=' . $usuario_id . '&mensaje=Falta registrada correctamente');
            exit;
        } else {
            $mensaje = 'Error al registrar: ' . $conn->error;
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
    <title>Agregar Falta</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        
        .form-container h2 {
            color: #1a3a5c;
            border-bottom: 2px solid #1a3a5c;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        
        .campo {
            margin-bottom: 18px;
        }
        
        .campo label {
            display: block;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .campo input, .campo select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #dce1e8;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.3s;
        }
        
        .campo input:focus, .campo select:focus {
            border-color: #1a3a5c;
            outline: none;
        }
        
        .acciones-form {
            display: flex;
            gap: 15px;
            margin-top: 20px;
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
            background: #28a745;
            color: #fff;
        }
        
        .btn-guardar:hover {
            background: #218838;
        }
        
        .btn-cancelar {
            background: #e9edf2;
            color: #3d4f62;
        }
        
        .btn-cancelar:hover {
            background: #d5dce6;
        }
        
        .alert {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>📝 Agregar Falta</h2>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="campo">
                    <label for="fecha">Fecha de la falta *</label>
                    <input type="date" id="fecha" name="fecha" required value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="campo">
                    <label for="tipo">Tipo de falta *</label>
                    <select id="tipo" name="tipo" required>
                        <option value="">Selecciona el tipo</option>
                        <option value="Ausencia">Ausencia</option>
                        <option value="Retardo">Retardo</option>
                        <option value="Salida Temprano">Salida Temprano</option>
                        <option value="Inasistencia">Inasistencia</option>
                        <option value="Otra">Otra</option>
                    </select>
                </div>
                
                <div class="acciones-form">
                    <a href="faltas.php?usuario_id=<?php echo $usuario_id; ?>" class="btn btn-cancelar">✕ Cancelar</a>
                    <button type="submit" class="btn btn-guardar">💾 Registrar Falta</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>