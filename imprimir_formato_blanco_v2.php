<?php
include 'config.php';

// =========================================================
// 👇 VINCULACIÓN CON EL LOGO DE LA PÁGINA WEB 👇
// =========================================================
$ruta_logo_web = '';
foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
    $archivo = __DIR__ . '/logos/logo_izquierdo.' . $ext;
    if (file_exists($archivo)) {
        $ruta_logo_web = 'logos/logo_izquierdo.' . $ext . '?v=' . filemtime($archivo);
        break;
    }
}
// =========================================================

// Obtener lista de usuarios para el select
$usuarios_result = $conn->query("SELECT id, nombre, area, puesto, nip, telefono FROM usuarios ORDER BY nombre");

$usuarios = [];
if ($usuarios_result) {
    while($u = $usuarios_result->fetch_assoc()) {
        $usuarios[] = $u;
    }
}

// Variables por defecto
$nombre_servidor = '';
$area_servidor = '';
$nip_servidor = '';
$telefono_servidor = '';
$tipo_incidencia = '';
$motivo_incidencia = '';
$fechas_justificar = '';
$solicita_nombre = '';
$solicita_puesto = '';
$autoriza_nombre = '';
$autoriza_puesto = '';
$visto_bueno_nombre = '';
$visto_bueno_puesto = '';
$lugar_expedicion = 'TUXTLA GUTIÉRREZ, CHIAPAS';
$fecha_expedicion = strtoupper(date('d \D\E F \D\E Y'));

// Si hay un usuario_id en la URL, cargar sus datos
$usuario_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : 0;
if ($usuario_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($usuario) {
        $nombre_servidor = $usuario['nombre'];
        $area_servidor = $usuario['area'];
        $nip_servidor = $usuario['nip'];
        $telefono_servidor = $usuario['telefono'];
        $solicita_nombre = $usuario['nombre'];
        $solicita_puesto = $usuario['puesto'];
    }
}

// Si hay un ID de justificación, cargar todos los datos
$justificacion_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($justificacion_id > 0) {
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
    $stmt->bind_param("i", $justificacion_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($data) {
        $nombre_servidor = $data['usuario_nombre'];
        $area_servidor = $data['usuario_area'];
        $nip_servidor = $data['usuario_nip'];
        $telefono_servidor = $data['usuario_telefono'];
        $tipo_incidencia = $data['tipo_incidencia'];
        $motivo_incidencia = $data['motivo'];
        $fechas_justificar = $data['dias_justificar'];
        $solicita_nombre = $data['solicita_nombre'];
        $solicita_puesto = $data['solicita_puesto'];
        $autoriza_nombre = $data['autoriza_nombre'];
        $autoriza_puesto = $data['autoriza_puesto'];
        $visto_bueno_nombre = $data['visto_bueno_nombre'];
        $visto_bueno_puesto = $data['visto_bueno_puesto'];
        $lugar_expedicion = $data['lugar_expedicion'];
        $fecha_expedicion = strtoupper(date('d \D\E F \D\E Y', strtotime($data['fecha_expedicion'])));
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Justificación de Incidencias</title>
    <style>
        /* ========================================
           CONFIGURACIÓN GENERAL DEL PAPEL A4
           ======================================== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            background: #f0f0f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        /* --- CONTENEDOR DEL FORMATO --- */
        .form-container {
            width: 210mm; /* Ancho A4 */
            min-height: 297mm; /* Alto A4 */
            background: #fff;
            padding: 15mm 10mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #ccc;
            font-size: 10pt;
            position: relative;
            margin-bottom: 20px;
        }

        /* ========================================
           ENCABEZADO (3 COLUMNAS)
           ======================================== */
        .header-grid {
            display: flex;
            border: 1px solid #000;
            margin-bottom: 8px;
        }
        .header-grid .col-logo {
            width: 22%;
            border-right: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
            background: #fafafa;
        }
        .header-grid .col-logo img {
            max-width: 90%;
            height: auto;
            display: block;
        }
        .header-grid .col-titulo {
            width: 50%;
            border-right: 1px solid #000;
            text-align: center;
            padding: 8px 5px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #fafafa;
        }
        .header-grid .col-titulo .uua { font-size: 8pt; font-weight: 700; text-transform: uppercase; }
        .header-grid .col-titulo .rh { font-size: 10pt; font-weight: 700; text-transform: uppercase; }
        .header-grid .col-titulo .titulo { font-size: 14pt; font-weight: 700; text-transform: uppercase; margin-top: 3px; }
        
        .header-grid .col-datos {
            width: 28%;
            display: flex;
            flex-direction: column;
            font-size: 8pt;
            background: #fafafa;
        }
        .header-grid .col-datos .fila {
            display: flex;
            border-bottom: 1px solid #000;
            height: 100%;
        }
        .header-grid .col-datos .fila:last-child { border-bottom: none; }
        .header-grid .col-datos .fila .label {
            width: 30%;
            border-right: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-weight: 700;
            padding: 2px;
        }
        .header-grid .col-datos .fila .valor {
            width: 70%;
            display: flex;
            align-items: center;
            padding-left: 5px;
            font-weight: 700;
        }

        /* 👇 INPUTS EDITABLES DENTRO DE LA TABLA DEL ENCABEZADO 👇 */
        .header-grid .col-datos .fila .valor input {
            width: 100%;
            border: none;
            background: transparent;
            font-weight: 700;
            font-family: inherit;
            font-size: 8pt;
            padding: 0 5px;
            outline: none;
            color: #000;
        }
        .header-grid .col-datos .fila .valor input:focus {
            background: #eef4ff;
        }

        /* ========================================
           AVISO Y LUGAR/FECHA
           ======================================== */
        .aviso-fecha {
            display: flex;
            justify-content: space-between;
            font-size: 7pt;
            margin-bottom: 10px;
        }
        .aviso-fecha .aviso {
            width: 60%;
            text-align: justify;
            font-style: italic;
        }
        .aviso-fecha .lugar {
            width: 38%;
            text-align: right;
        }
        .aviso-fecha .lugar strong { font-weight: 700; }

        /* ========================================
           TÍTULOS DE SECCIÓN (MARCO NEGRO)
           ======================================== */
        .titulo-seccion {
            border: 1px solid #000;
            text-align: center;
            font-weight: 700;
            font-size: 10pt;
            text-transform: uppercase;
            padding: 3px 0;
            margin: 10px 0 5px 0;
        }

        /* ========================================
           FILAS DE DATOS (INPUTS EN PANTALLA)
           ======================================== */
        .fila-dato {
            display: flex;
            align-items: flex-end;
            margin-bottom: 5px;
            font-size: 10pt;
        }
        .fila-dato .label {
            font-weight: 700;
            min-width: 200px;
        }
        .fila-dato .linea-input {
            flex: 1;
            border: none;
            border-bottom: 1px solid #000;
            min-height: 18px;
            padding: 0 5px;
            font-family: inherit;
            font-size: 10pt;
            width: 100%;
            outline: none;
            background: transparent;
            color: #000;
        }
        .fila-dato .linea-input:focus {
            background: #f0f4ff;
        }

        /* ========================================
           NIP Y TELÉFONO (RECUADROS PEQUEÑOS)
           ======================================== */
        .fila-nip-tel {
            display: flex;
            align-items: flex-end;
            font-size: 10pt;
            margin-bottom: 5px;
        }
        .fila-nip-tel .label {
            font-weight: 700;
            min-width: 200px;
        }
        .fila-nip-tel .recuadro-nip-input {
            border: 1px solid #000;
            width: 100px;
            padding: 0 5px;
            margin-right: 30px;
            text-align: center;
            font-family: inherit;
            font-size: 10pt;
            background: transparent;
            outline: none;
        }
        .fila-nip-tel .recuadro-nip-input:focus { background: #f0f4ff; }
        .fila-nip-tel .label-tel { font-weight: 700; margin-right: 5px; }
        .fila-nip-tel .recuadro-tel-input {
            border: 1px solid #000;
            width: 100px;
            padding: 0 5px;
            text-align: center;
            font-family: inherit;
            font-size: 10pt;
            background: transparent;
            outline: none;
        }
        .fila-nip-tel .recuadro-tel-input:focus { background: #f0f4ff; }

        /* ========================================
           CHECKBOX DE INCIDENCIAS (MARCADORES CON CLIC)
           ======================================== */
        .check-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px 40px;
            padding: 10px 20px;
            font-size: 9pt;
        }
        .check-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
        }
        .check-item label { font-weight: 700; cursor: pointer; }
        .check-item .cuadro {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 1px solid #000;
            text-align: center;
            line-height: 18px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            background: #fff;
            transition: background 0.2s;
            user-select: none;
        }
        .check-item .cuadro.marcado {
            background: #000;
            color: #fff;
        }
        .check-item .cuadro.marcado::after {
            content: "✓";
        }

        /* ========================================
           DESCRIPCIÓN (TEXTAREA)
           ======================================== */
        .textarea-motivo {
            width: 100%;
            border: none;
            border-bottom: 1px solid #000;
            font-family: inherit;
            font-size: 10pt;
            padding: 0 5px;
            background: transparent;
            resize: vertical;
            min-height: 35px;
            outline: none;
        }
        .textarea-motivo:focus {
            background: #f0f4ff;
        }

        .textarea-fechas {
            width: 100%;
            border: none;
            border-bottom: 2px solid #000;
            font-family: inherit;
            font-size: 10pt;
            padding: 0 5px;
            background: transparent;
            outline: none;
        }
        .textarea-fechas:focus {
            background: #f0f4ff;
        }

        /* ========================================
           SECCIÓN DE FIRMAS
           ======================================== */
        .firmas-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 5px;
        }
        .col-firma {
            text-align: center;
            font-size: 9pt;
        }
        .col-firma .firma-titulo {
            font-weight: 700;
            margin-bottom: 5px;
        }
        .col-firma .firma-linea-input {
            width: 100%;
            border: none;
            border-bottom: 1px solid #000;
            min-height: 20px;
            padding: 2px 5px;
            font-weight: 700;
            font-size: 10pt;
            text-transform: uppercase;
            text-align: center;
            background: transparent;
            font-family: inherit;
            outline: none;
        }
        .col-firma .firma-linea-input:focus {
            background: #f0f4ff;
        }
        .col-firma .firma-puesto-input {
            width: 100%;
            border: none;
            font-size: 8pt;
            font-weight: 700;
            text-align: center;
            margin-top: 3px;
            background: transparent;
            font-family: inherit;
            outline: none;
        }
        .col-firma .firma-puesto-input:focus {
            background: #f0f4ff;
        }

        /* ========================================
           SELLO Y NOTA
           ======================================== */
        .sello-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            align-items: flex-start;
        }
        .sello-container .sello {
            border: 1px solid #000;
            width: 120px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 8pt;
            text-transform: uppercase;
            margin: 0 auto;
        }
        .sello-container .nota {
            width: 70%;
            font-size: 7pt;
            text-align: justify;
            line-height: 1.2;
        }
        .sello-container .nota strong { font-weight: 700; }

        .qr-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 7pt;
            align-items: center;
        }
        .qr-footer .qr-code { font-size: 8pt; font-weight: 700; }

        /* ========================================
           SELECTOR DE USUARIO (FUERA DE IMPRESIÓN)
           ======================================== */
        .selector-usuario {
            width: 210mm;
            background: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            border: 1px solid #ddd;
        }
        .selector-usuario select {
            flex: 1;
            padding: 5px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            min-width: 200px;
        }
        .btn { padding: 6px 15px; border: none; border-radius: 4px; font-size: 14px; cursor: pointer; font-weight: 600; color: #fff; }
        .btn-cargar { background: #1a3a5c; }
        .btn-limpiar { background: #dc3545; }
        .btn-cerrar { background: #6c757d; }

        /* ========================================
           CONFIGURACIÓN DE IMPRESIÓN
           ======================================== */
        @media print {
            body { padding: 0; background: white; }
            .form-container {
                box-shadow: none;
                border: none;
                padding: 10mm 5mm;
                width: 100%;
                min-height: auto;
                margin: 0;
            }
            .selector-usuario, .no-print { display: none !important; }
            .header-grid .col-logo, .header-grid .col-titulo, .header-grid .col-datos { background: white !important; }
            .check-item .cuadro.marcado { background: #000; color: #fff !important; }
            
            /* Al imprimir, los inputs pierden el borde de enfoque y fondo */
            input, textarea {
                background: transparent !important;
                border-bottom-color: #000 !important;
                box-shadow: none !important;
                -webkit-appearance: none;
            }
        }
    </style>
</head>
<body>
    <!-- SELECTOR Y BOTONES (Solo visible en pantalla) -->
    <div class="selector-usuario no-print">
        <label style="font-weight:600;">👤 Seleccionar Usuario:</label>
        <select id="usuario_select">
            <option value="">-- Seleccionar --</option>
            <?php foreach($usuarios as $u): ?>
                <option value="<?php echo $u['id']; ?>" 
                        data-nombre="<?php echo htmlspecialchars($u['nombre']); ?>"
                        data-area="<?php echo htmlspecialchars($u['area']); ?>"
                        data-puesto="<?php echo htmlspecialchars($u['puesto']); ?>"
                        data-nip="<?php echo htmlspecialchars($u['nip']); ?>"
                        data-telefono="<?php echo htmlspecialchars($u['telefono']); ?>">
                    <?php echo htmlspecialchars($u['nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-cargar" onclick="cargarUsuario()">Cargar Datos</button>
        <button class="btn btn-limpiar" onclick="limpiarFormulario()">Limpiar</button>
        <button onclick="window.print()" class="btn btn-cerrar" style="background:#28a745;">🖨️ Imprimir</button>
    </div>

    <!-- ============================================
         FORMULARIO INTERACTIVO (CONVERTIDO A INPUTS)
         ============================================ -->
    <div class="form-container" id="formContainer">

        <!-- ENCABEZADO -->
        <div class="header-grid">
            <div class="col-logo">
                <!-- LOGO VINCULADO -->
                <img src="<?php echo $ruta_logo_web; ?>" alt="Logotipo" style="max-width: 90%; height: auto; display: block;" onerror="this.style.display='none'; document.getElementById('logo-placeholder').style.display='flex';">
                <div id="logo-placeholder" style="display: none; font-weight: bold; font-size: 8pt; text-align: center; width: 100%;">
                    [LOGO]<br>CHIAPAS
                </div>
            </div>
            <div class="col-titulo">
                <div class="uua">UNIDAD DE APOYO ADMINISTRATIVO</div>
                <div class="rh">ÁREA DE RECURSOS HUMANOS</div>
                <div class="titulo">JUSTIFICACIÓN DE INCIDENCIAS</div>
            </div>
            
            <!-- COLUMNA DERECHA EDITABLE -->
            <div class="col-datos">
                <div class="fila">
                    <div class="label">CÓDIGO</div>
                    <div class="valor"><input type="text" id="campo_codigo" value="SF/UAA/005/F"></div>
                </div>
                <div class="fila">
                    <div class="label">REVISIÓN</div>
                    <div class="valor"><input type="text" id="campo_revision" value="5"></div>
                </div>
                <div class="fila">
                    <div class="label">FECHA</div>
                    <div class="valor"><input type="text" id="campo_fecha_encabezado" value="30/ABRIL/2026"></div>
                </div>
            </div>
        </div>

        <!-- AVISO Y LUGAR/FECHA -->
        <div class="aviso-fecha">
            <div class="aviso">Los datos personales proporcionados serán tratados de manera confidencial y utilizados únicamente para fines relacionados con la prestación de nuestros trámites y/o servicios y el cumplimiento de obligaciones legales.</div>
            <div class="lugar">
                <strong>Lugar de Expedición:</strong> <input type="text" id="campo_lugar" value="<?php echo htmlspecialchars($lugar_expedicion); ?>" style="border:none; border-bottom:1px solid #000; background:transparent; width:180px; font-weight:700; font-family:inherit; font-size:7pt;"><br>
                <strong>Fecha:</strong> <input type="text" id="campo_fecha" value="<?php echo $fecha_expedicion; ?>" style="border:none; border-bottom:1px solid #000; background:transparent; width:180px; font-weight:700; font-family:inherit; font-size:7pt;">
            </div>
        </div>

        <!-- DATOS PERSONALES -->
        <div class="titulo-seccion">Datos Personales</div>
        
        <div class="fila-dato">
            <span class="label">Nombre del Servidor Público:</span>
            <input type="text" class="linea-input" id="campo_nombre" value="<?php echo htmlspecialchars($nombre_servidor); ?>">
        </div>
        <div class="fila-dato">
            <span class="label">Área de Adscripción o Comisión:</span>
            <input type="text" class="linea-input" id="campo_area" value="<?php echo htmlspecialchars($area_servidor); ?>">
        </div>

        <div class="fila-nip-tel">
            <span class="label">Número de Identificación Personal (NIP):</span>
            <input type="text" class="recuadro-nip-input" id="campo_nip" value="<?php echo htmlspecialchars($nip_servidor); ?>">
            <span class="label-tel">Número de Teléfono o Extensión:</span>
            <input type="text" class="recuadro-tel-input" id="campo_telefono" value="<?php echo htmlspecialchars($telefono_servidor); ?>">
        </div>

        <!-- TIPO DE INCIDENCIA (CHECKBOX INTERACTIVOS) -->
        <div class="titulo-seccion">Tipo de Incidencia</div>
        <div class="check-grid">
            <?php 
            // Definimos los tipos de incidencia
            $tipos = [
                'Vacaciones' => 'VACACIONES',
                'Permiso Económico' => 'PERMISO_ECONOMICO',
                'Comisión' => 'COMISION',
                'Día de Cumpleaños Personal de Confianza' => 'CUMPLEAÑOS',
                'Omisión de Entrada' => 'OPOSICION_ENTRADA',
                'Omisión de Salida' => 'OPOSICION_SALIDA'
            ];
            foreach($tipos as $label => $valor):
                // Verificamos si viene marcado desde la base de datos
                $marcado = ($tipo_incidencia == $valor) ? 'marcado' : '';
            ?>
                <div class="check-item" onclick="toggleCheck(this)">
                    <label><?php echo $label; ?></label>
                    <span class="cuadro <?php echo $marcado; ?>"></span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- DESCRIPCIÓN DE LA INCIDENCIA -->
        <div class="titulo-seccion">Descripción de la Incidencia</div>
        <div class="fila-dato">
            <span class="label">Motivos de la Incidencia:</span>
            <textarea class="textarea-motivo" id="campo_motivo" rows="2"><?php echo htmlspecialchars($motivo_incidencia); ?></textarea>
        </div>
        <div class="fila-dato" style="margin-top: 10px;">
            <span class="label">Fecha(s) a Justificar:</span>
            <input type="text" class="textarea-fechas" id="campo_fechas" value="<?php echo htmlspecialchars($fechas_justificar); ?>">
        </div>

        <!-- FIRMAS -->
        <div class="titulo-seccion">Firmas de Validación</div>
        <div class="firmas-grid">
            <div class="col-firma">
                <div class="firma-titulo">Solicita</div>
                <input type="text" class="firma-linea-input" id="campo_solicita" value="<?php echo htmlspecialchars($solicita_nombre); ?>">
                <input type="text" class="firma-puesto-input" id="campo_solicita_puesto" value="<?php echo htmlspecialchars($solicita_puesto); ?>">
            </div>
            <div class="col-firma">
                <div class="firma-titulo">Autoriza</div>
                <input type="text" class="firma-linea-input" id="campo_autoriza" value="<?php echo htmlspecialchars($autoriza_nombre); ?>">
                <input type="text" class="firma-puesto-input" id="campo_autoriza_puesto" value="<?php echo htmlspecialchars($autoriza_puesto); ?>">
            </div>
            <div class="col-firma">
                <div class="firma-titulo">Visto Bueno</div>
                <input type="text" class="firma-linea-input" id="campo_visto" value="<?php echo htmlspecialchars($visto_bueno_nombre); ?>">
                <input type="text" class="firma-puesto-input" id="campo_visto_puesto" value="<?php echo htmlspecialchars($visto_bueno_puesto); ?>">
            </div>
        </div>

        <!-- SELLO Y NOTA -->
        <div class="sello-container">
            <!-- SELLO CENTRADO -->
            <div class="sello">Sello Oficial</div>
            <div class="nota">
                <strong>NOTA:</strong> El formato no debe presentar alteraciones o tachaduras y deberá cumplir con lo establecido dentro del instructivo de llenado para ser aceptado por el Área de Recursos Humanos; asimismo, no se otorgará permiso con goce de sueldo, antes o después de los periodos vacacionales y días festivos.
            </div>
        </div>

        <div class="qr-footer">
            <div class="qr-code">🔳 Consulte el aviso de privacidad escaneando el Código QR</div>
            <div style="font-weight:700;">SF/UP/001/F REV.1</div>
        </div>

    </div>

    <!-- ============================================
         SCRIPT PARA CARGAR DATOS E INTERACTUAR
         ============================================ -->
    <script>
        // Cargar datos del usuario seleccionado
        function cargarUsuario() {
            const select = document.getElementById('usuario_select');
            const selected = select.options[select.selectedIndex];
            if (!selected.value) return alert('Selecciona un usuario.');
            
            document.getElementById('campo_nombre').value = selected.dataset.nombre || '';
            document.getElementById('campo_area').value = selected.dataset.area || '';
            document.getElementById('campo_nip').value = selected.dataset.nip || '';
            document.getElementById('campo_telefono').value = selected.dataset.telefono || '';
            document.getElementById('campo_solicita').value = selected.dataset.nombre || '';
            document.getElementById('campo_solicita_puesto').value = selected.dataset.puesto || '';
        }

        // Limpiar todos los campos y checkboxes
        function limpiarFormulario() {
            if(confirm('¿Limpiar todos los campos?')) {
                document.querySelectorAll('input, textarea').forEach(el => {
                    if(el.tagName === 'INPUT' && el.type === 'text') el.value = '';
                    if(el.tagName === 'TEXTAREA') el.value = '';
                });
                document.querySelectorAll('.cuadro').forEach(el => { el.classList.remove('marcado'); el.textContent = ''; });
                document.getElementById('usuario_select').selectedIndex = 0;
                
                // Resetear encabezado
                document.getElementById('campo_codigo').value = 'SF/UAA/005/F';
                document.getElementById('campo_revision').value = '5';
                document.getElementById('campo_fecha_encabezado').value = '30/ABRIL/2026';
                document.getElementById('campo_lugar').value = 'TUXTLA GUTIÉRREZ, CHIAPAS';
                document.getElementById('campo_fecha').value = '';
            }
        }

        // Función para alternar el marcado de los checkboxes
        function toggleCheck(element) {
            const cuadro = element.querySelector('.cuadro');
            cuadro.classList.toggle('marcado');
        }
    </script>
</body>
</html>