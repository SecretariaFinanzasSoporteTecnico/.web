<?php
include 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    die('❌ Justificación no encontrada');
}

// Obtener datos
$sql = "SELECT 
            j.*,
            u.nombre as usuario_nombre,
            u.area as usuario_area,
            u.puesto as usuario_puesto,
            u.nip as usuario_nip,
            u.telefono as usuario_telefono
        FROM justificaciones j
        JOIN usuarios u ON j.usuario_id = u.id
        WHERE j.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    die('❌ Justificación no encontrada');
}

// Tipo de incidencia
$tipos = [
    'VACACIONES' => 'Vacaciones',
    'PERMISO_ECONOMICO' => 'Permiso Económico',
    'COMISION' => 'Comisión',
    'OPOSICION_ENTRADA' => 'Oposición de Entrada',
    'OPOSICION_SALIDA' => 'Oposición de Salida'
];

// Fecha de expedición formateada
$fecha_exp = strtoupper(date('d \D\E F \D\E Y', strtotime($data['fecha_expedicion'])));
$meses = ['ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'];
$fecha_exp = str_replace(date('F', strtotime($data['fecha_expedicion'])), $meses[date('n', strtotime($data['fecha_expedicion']))-1], $fecha_exp);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Justificación de Incidencias</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Arial, sans-serif;
            padding: 40px;
            background: white;
        }
        .justificante {
            max-width: 900px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 30px;
            background: white;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .header .sub {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }
        .header .codigo {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .header .datos {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
        }
        .aviso {
            font-size: 10px;
            text-align: justify;
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-left: 3px solid #667eea;
        }
        .seccion {
            margin-bottom: 20px;
        }
        .seccion .titulo {
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .fila {
            display: flex;
            margin-bottom: 5px;
            font-size: 12px;
        }
        .fila .label {
            font-weight: bold;
            min-width: 200px;
        }
        .fila .valor {
            flex: 1;
        }
        .fila .valor .check {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 1px solid #333;
            margin-right: 5px;
            text-align: center;
            line-height: 18px;
            font-size: 14px;
        }
        .fila .valor .check.marcado {
            background: #333;
            color: white;
        }
        .fila .valor .check.marcado::after {
            content: "✓";
        }
        .fila-check {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 25px;
            font-size: 12px;
        }
        .fila-check label {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .fila-check .check {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 1px solid #333;
            text-align: center;
            line-height: 18px;
            font-size: 14px;
        }
        .fila-check .check.marcado {
            background: #333;
            color: white;
        }
        .fila-check .check.marcado::after {
            content: "✓";
        }
        .firmas {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }
        .firma {
            text-align: center;
            font-size: 11px;
        }
        .firma .nombre {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 3px;
        }
        .firma .puesto {
            font-size: 10px;
            color: #555;
        }
        .firma .linea {
            border-top: 1px solid #333;
            width: 80%;
            margin: 8px auto 5px;
        }
        .firma .label-firma {
            font-size: 10px;
            color: #666;
        }
        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #333;
            font-size: 9px;
            text-align: center;
            color: #666;
        }
        .sello {
            text-align: center;
            font-size: 10px;
            margin-top: 10px;
            padding: 10px;
            border: 1px dashed #ccc;
        }
        .nota {
            font-size: 9px;
            margin-top: 15px;
            text-align: justify;
            color: #666;
        }
        .nota strong {
            color: #333;
        }
        .botones {
            text-align: center;
            margin-top: 30px;
        }
        .botones button {
            padding: 10px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin: 0 5px;
        }
        .botones button:hover {
            background: #5a6fd6;
        }
        .botones .btn-cerrar {
            background: #6c757d;
        }
        .botones .btn-cerrar:hover {
            background: #5a6268;
        }
        .qr {
            text-align: right;
            font-size: 9px;
            color: #999;
            margin-top: 5px;
        }
        @media print {
            .botones { display: none; }
            body { padding: 20px; background: white; }
            .justificante { border: 1px solid #ccc; padding: 20px; }
        }
        @media (max-width: 768px) {
            .firmas {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            .fila {
                flex-direction: column;
            }
            .fila .label {
                min-width: auto;
            }
            .header .datos {
                flex-direction: column;
                gap: 5px;
            }
            .fila-check {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="justificante">
        <!-- HEADER -->
        <div class="header">
            <h1>UNIDAD DE APOYO ADMINISTRATIVO</h1>
            <div class="sub">ÁREA DE RECURSOS HUMANOS</div>
            <div style="font-size: 16px; font-weight: bold; margin-top: 10px;">JUSTIFICACIÓN DE INCIDENCIAS</div>
            <div class="datos">
                <span><strong>CÓDIGO:</strong> SF/UAA/005/F</span>
                <span><strong>REVISIÓN:</strong> 5</span>
                <span><strong>FECHA:</strong> <?php echo date('d/M/Y', strtotime($data['fecha_expedicion'])); ?></span>
            </div>
        </div>

        <!-- AVISO -->
        <div class="aviso">
            Los datos personales proporcionados serán tratados de manera confidencial y utilizados únicamente para fines relacionados con la prestación de nuestros trámites y/o servicios y el cumplimiento de obligaciones legales.
        </div>

        <!-- LUGAR Y FECHA -->
        <div style="font-size: 12px; margin-bottom: 15px; text-align: right;">
            <strong>Lugar de Expedición:</strong> <?php echo $data['lugar_expedicion']; ?><br>
            <strong>Fecha:</strong> <?php echo $fecha_exp; ?>
        </div>

        <!-- DATOS PERSONALES -->
        <div class="seccion">
            <div class="titulo">Datos Personales</div>
            <div class="fila">
                <span class="label">Nombre del Servidor Público:</span>
                <span class="valor"><?php echo htmlspecialchars($data['usuario_nombre']); ?></span>
            </div>
            <div class="fila">
                <span class="label">Área de Adscripción o Comisión:</span>
                <span class="valor"><?php echo htmlspecialchars($data['usuario_area']); ?></span>
            </div>
            <div class="fila">
                <span class="label">Número de Identificación Personal (NIP):</span>
                <span class="valor"><?php echo htmlspecialchars($data['usuario_nip']); ?></span>
            </div>
            <div class="fila">
                <span class="label">Número de Teléfono o Extensión:</span>
                <span class="valor"><?php echo htmlspecialchars($data['usuario_telefono']); ?></span>
            </div>
        </div>

        <!-- TIPO DE INCIDENCIA -->
        <div class="seccion">
            <div class="titulo">Tipo de Incidencia</div>
            <div class="fila-check">
                <?php 
                $tipos_check = ['VACACIONES', 'PERMISO_ECONOMICO', 'COMISION', 'OPOSICION_ENTRADA', 'OPOSICION_SALIDA'];
                foreach ($tipos_check as $t): 
                    $marcado = ($data['tipo_incidencia'] == $t);
                    $label = isset($tipos[$t]) ? $tipos[$t] : $t;
                ?>
                    <label>
                        <span class="check <?php echo $marcado ? 'marcado' : ''; ?>"></span> <?php echo $label; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- DESCRIPCIÓN -->
        <div class="seccion">
            <div class="titulo">Descripción de la Incidencia</div>
            <div class="fila">
                <span class="label">Motivos de la Incidencia:</span>
                <span class="valor"><?php echo htmlspecialchars($data['motivo']); ?></span>
            </div>
            <div class="fila" style="margin-top: 8px;">
                <span class="label">Fecha(s) a Justificar:</span>
                <span class="valor"><strong><?php echo htmlspecialchars($data['dias_justificar']); ?></strong></span>
            </div>
        </div>

        <!-- FIRMAS -->
        <div class="seccion">
            <div class="titulo">Firmas de Validación</div>
            <div class="firmas">
                <div class="firma">
                    <div class="nombre"><?php echo htmlspecialchars($data['usuario_nombre']); ?></div>
                    <div class="puesto"><?php echo htmlspecialchars($data['usuario_puesto']); ?></div>
                    <div class="linea"></div>
                    <div class="label-firma">Solicita</div>
                </div>
                <div class="firma">
                    <div class="nombre"><?php echo htmlspecialchars($data['autoriza_nombre']); ?></div>
                    <div class="puesto"><?php echo htmlspecialchars($data['autoriza_puesto']); ?></div>
                    <div class="linea"></div>
                    <div class="label-firma">Autoriza</div>
                </div>
                <div class="firma">
                    <div class="nombre"><?php echo htmlspecialchars($data['visto_bueno_nombre']); ?></div>
                    <div class="puesto"><?php echo htmlspecialchars($data['visto_bueno_puesto']); ?></div>
                    <div class="linea"></div>
                    <div class="label-firma">Visto Bueno</div>
                </div>
            </div>
        </div>

        <!-- SELLO -->
        <div class="sello">
            <strong>Sello Oficial</strong>
        </div>

        <!-- NOTA -->
        <div class="nota">
            <strong>NOTA:</strong> El formato no debe presentar alteraciones o tachaduras y deberá cumplir con lo establecido dentro del instructivo de llenado para ser aceptado por el Área de Recursos Humanos; asimismo, no se otorgará permiso con goce de sueldo, antes o después de los periodos vacacionales y días festivos.
        </div>

        <!-- FOOTER -->
        <div class="footer">
            Consulte el aviso de privacidad escaneando el Código QR &nbsp;&nbsp;|&nbsp;&nbsp; SF/UP/001/F REV.1
        </div>
        
        <div class="qr">
            * Consecutivo: <?php echo $data['consecutivo']; ?>
        </div>
    </div>

    <!-- BOTONES -->
    <div class="botones">
        <button onclick="window.print()">🖨️ Imprimir</button>
        <button onclick="window.close()" class="btn-cerrar">❌ Cerrar</button>
    </div>
</body>
</html>