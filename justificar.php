<?php
include 'config.php';

$falta_id = isset($_GET['falta_id']) ? $_GET['falta_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $falta_id = $_POST['falta_id'];
    $justificante_texto = $_POST['justificante_texto'];
    
    // Actualizar falta como justificada
    $stmt = $conn->prepare("UPDATE faltas SET justificada = 1 WHERE id = ?");
    $stmt->bind_param("i", $falta_id);
    $stmt->execute();
    $stmt->close();
    
    // Insertar justificante
    $stmt = $conn->prepare("INSERT INTO justificantes (falta_id, justificante_texto) VALUES (?, ?)");
    $stmt->bind_param("is", $falta_id, $justificante_texto);
    $stmt->execute();
    $stmt->close();
    
    // Obtener usuario_id para redirigir
    $stmt = $conn->prepare("SELECT usuario_id FROM faltas WHERE id = ?");
    $stmt->bind_param("i", $falta_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $falta = $result->fetch_assoc();
    $stmt->close();
    
    header('Location: faltas.php?usuario_id=' . $falta['usuario_id'] . '&mensaje=✅ Falta justificada correctamente');
    exit();
}

// Mostrar formulario de justificación
$stmt = $conn->prepare("SELECT f.*, u.nombre as usuario_nombre FROM faltas f JOIN usuarios u ON f.usuario_id = u.id WHERE f.id = ?");
$stmt->bind_param("i", $falta_id);
$stmt->execute();
$falta = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$falta) {
    header('Location: index.php?error=Falta no encontrada');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Justificar Falta</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .justificante-form {
            max-width: 600px;
            margin: 0 auto;
        }
        .justificante-form textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            resize: vertical;
            min-height: 150px;
        }
        .justificante-form textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .info-falta {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📝 Justificar Falta</h1>
        
        <div class="info-falta">
            <p><strong>Usuario:</strong> <?php echo htmlspecialchars($falta['usuario_nombre']); ?></p>
            <p><strong>Fecha de falta:</strong> <?php echo date('d/m/Y', strtotime($falta['fecha_falta'])); ?></p>
            <p><strong>Motivo:</strong> <?php echo htmlspecialchars($falta['motivo']); ?></p>
        </div>

        <form method="POST" class="justificante-form">
            <input type="hidden" name="falta_id" value="<?php echo $falta_id; ?>">
            
            <div class="form-group">
                <label for="justificante_texto"><strong>Texto del Justificante:</strong></label>
                <textarea name="justificante_texto" id="justificante_texto" rows="6" required placeholder="Escribe aquí el justificante de la falta..."></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">✅ Guardar Justificante</button>
                <a href="faltas.php?usuario_id=<?php echo $falta['usuario_id']; ?>" class="btn btn-danger">❌ Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>