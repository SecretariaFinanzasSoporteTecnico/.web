<?php
include 'config.php';

$falta_id = isset($_GET['falta_id']) ? $_GET['falta_id'] : 0;

// Obtener datos del justificante
$sql = "SELECT 
            f.*, 
            u.nombre as usuario_nombre,
            u.email as usuario_email,
            j.justificante_texto,
            j.fecha_justificacion
        FROM faltas f 
        JOIN usuarios u ON f.usuario_id = u.id 
        LEFT JOIN justificantes j ON f.id = j.falta_id 
        WHERE f.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $falta_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    header('Location: index.php?error=Justificante no encontrado');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Justificante de Falta</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .justificante {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header-justificante {
            text-align: center;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header-justificante h1 {
            color: #667eea;
            margin: 0;
        }
        .header-justificante .subtitle {
            color: #666;
            margin-top: 5px;
        }
        .info-usuario {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .info-usuario p {
            margin: 8px 0;
        }
        .texto-justificante {
            background: #fff;
            padding: 25px;
            border-left: 4px solid #28a745;
            margin: 20px 0;
            line-height: 1.8;
            white-space: pre-wrap;
        }
        .footer-justificante {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #999;
            font-size: 14px;
        }
        .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }
        @media print {
            .no-print { display: none; }
            .justificante { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="justificante" id="justificante">
            <div class="header-justificante">
                <h1>📄 JUSTIFICANTE DE FALTA</h1>
                <div class="subtitle">Documento oficial de justificación</div>
            </div>

            <div class="info-usuario">
                <p><strong>👤 Usuario:</strong> <?php echo htmlspecialchars($data['usuario_nombre']); ?></p>
                <p><strong>📧 Email:</strong> <?php echo htmlspecialchars($data['usuario_email']); ?></p>
                <p><strong>📅 Fecha de falta:</strong> <?php echo date('d/m/Y', strtotime($data['fecha_falta'])); ?></p>
                <p><strong>📝 Motivo:</strong> <?php echo htmlspecialchars($data['motivo']); ?></p>
                <p><strong>✅ Estado:</strong> <span class="badge badge-success">Justificada</span></p>
            </div>

            <h3>📋 Texto del Justificante</h3>
            <div class="texto-justificante">
                <?php echo nl2br(htmlspecialchars($data['justificante_texto'])); ?>
            </div>

            <div style="margin-top: 30px;">
                <p><strong>📅 Fecha de justificación:</strong> <?php echo date('d/m/Y H:i', strtotime($data['fecha_justificacion'])); ?></p>
            </div>

            <div class="footer-justificante">
                <p>Este justificante es válido y certifica la ausencia del usuario en la fecha indicada.</p>
                <p><em>Generado automáticamente por el Sistema de Gestión</em></p>
            </div>
        </div>

        <div class="actions no-print">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir</button>
            <button onclick="descargarPDF()" class="btn btn-success">📥 Descargar PDF</button>
            <a href="faltas.php?usuario_id=<?php echo $data['usuario_id']; ?>" class="btn btn-danger">← Volver</a>
        </div>
    </div>

    <script>
        function descargarPDF() {
            // Esta función requiere una librería externa o plugin
            alert('Para descargar PDF, usa la función de imprimir y selecciona "Guardar como PDF" en el diálogo de impresión.');
            window.print();
        }
    </script>
</body>
</html>