<?php
include 'config.php';

// ===== OBTENER ID DEL USUARIO =====
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id == 0) {
    header('Location: index.php?error=ID de usuario no válido');
    exit;
}

// ===== OBTENER DATOS DEL USUARIO =====
$sql = "SELECT * FROM usuarios WHERE id = $id";
$resultado = $conn->query($sql);

if ($resultado->num_rows == 0) {
    header('Location: index.php?error=Usuario no encontrado');
    exit;
}

$usuario = $resultado->fetch_assoc();

// ===== PROCESAR ACTUALIZACIÓN =====
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $area = $_POST['area'];
    $puesto = $_POST['puesto'];
    $nip = $_POST['nip'];
    
    // Validar campos obligatorios
    if (empty($nombre) || empty($email)) {
        $mensaje = 'Nombre y email son obligatorios';
        $tipo_mensaje = 'danger';
    } else {
        // Actualizar usuario
        $sql_update = "UPDATE usuarios SET 
            nombre = '$nombre',
            email = '$email',
            telefono = '$telefono',
            area = '$area',
            puesto = '$puesto',
            nip = '$nip'
        WHERE id = $id";
        
        if ($conn->query($sql_update)) {
            header('Location: index.php?mensaje=Usuario actualizado correctamente');
            exit;
        } else {
            $mensaje = 'Error al actualizar: ' . $conn->error;
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
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container {
            max-width: 600px;
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
        
        .campo input {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #dce1e8;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.3s;
        }
        
        .campo input:focus {
            border-color: #1a3a5c;
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1);
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
            background: #1a3a5c;
            color: #fff;
        }
        
        .btn-guardar:hover {
            background: #0f2a44;
            transform: translateY(-2px);
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
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        @media (max-width: 768px) {
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
        <div class="form-container">
            <h2>✏️ Editar Usuario</h2>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="campo">
                    <label for="nombre">Nombre completo *</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                </div>
                
                <div class="campo">
                    <label for="email">Correo electrónico *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                </div>
                
                <div class="campo">
                    <label for="telefono">Teléfono / Extensión</label>
                    <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                </div>
                
                <div class="campo">
                    <label for="area">Área</label>
                    <input type="text" id="area" name="area" value="<?php echo htmlspecialchars($usuario['area'] ?? ''); ?>">
                </div>
                
                <div class="campo">
                    <label for="puesto">Puesto *</label>
                    <input type="text" id="puesto" name="puesto" value="<?php echo htmlspecialchars($usuario['puesto'] ?? ''); ?>" required>
                </div>
                
                <div class="campo">
                    <label for="nip">NIP</label>
                    <input type="text" id="nip" name="nip" value="<?php echo htmlspecialchars($usuario['nip'] ?? ''); ?>">
                </div>
                
                <div class="acciones-form">
                    <a href="index.php" class="btn btn-cancelar">✕ Cancelar</a>
                    <button type="submit" class="btn btn-guardar">💾 Actualizar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>