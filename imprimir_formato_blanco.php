<?php
include 'config.php';

// Obtener lista de usuarios para el select
$usuarios_result = $conn->query("SELECT id, nombre, area, puesto, nip, telefono FROM usuarios ORDER BY nombre");

// Verificar si la consulta fue exitosa
if (!$usuarios_result) {
    $usuarios_result = [];
    $usuarios = [];
} else {
    $usuarios = [];
    while($u = $usuarios_result->fetch_assoc()) {
        $usuarios[] = $u;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formato de Justificación - Blanco</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Arial, sans-serif; background: #f0f0f0; padding: 20px; display: flex; flex-direction: column; align-items: center; }
        
        .justificante {
            max-width: 210mm;
            width: 100%;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 15px 20px;
            background: white;
            font-size: 11px;
            page-break-after: avoid;
            page-break-inside: avoid;
        }
        
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 8px; }
        .header h1 { font-size: 14px; text-transform: uppercase; letter-spacing: 2px; }
        .header .sub { font-size: 12px; font-weight: bold; margin-top: 2px; }
        .header .codigo { font-size: 10px; color: #666; margin-top: 3px; }
        .header .datos { display: flex; justify-content: space-between; font-size: 10px; margin-top: 5px; padding-top: 5px; border-top: 1px solid #ccc; }
        
        .aviso { font-size: 8.5px; text-align: justify; background: #f8f9fa; padding: 5px 8px; margin-bottom: 8px; border-left: 3px solid #667eea; }
        .lugar-fecha { font-size: 10px; margin-bottom: 8px; text-align: right; }
        .lugar-fecha .espacio { display: inline-block; min-width: 150px; border-bottom: 1px solid #999; padding: 0 5px; }
        
        .seccion { margin-bottom: 6px; }
        .seccion .titulo { font-weight: bold; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #333; padding-bottom: 2px; margin-bottom: 4px; }
        
        .fila { display: flex; margin-bottom: 2px; font-size: 10px; align-items: center; }
        .fila .label { font-weight: bold; min-width: 180px; font-size: 10px; }
        .fila .valor { flex: 1; border-bottom: 1px solid #ccc; padding: 1px 5px; min-height: 18px; }
        
        .fila-check { display: flex; flex-wrap: wrap; gap: 4px 15px; font-size: 10px; padding: 2px 0; }
        .fila-check label { display: flex; align-items: center; gap: 3px; }
        .fila-check .check { display: inline-block; width: 14px; height: 14px; border: 1px solid #333; text-align: center; line-height: 14px; font-size: 11px; background: white; cursor: pointer; user-select: none; }
        .fila-check .check.marcado { background: #333; color: white; }
        .fila-check .check.marcado::after { content: "✓"; }
        
        .firmas { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 8px; border-top: 1px solid #ccc; padding-top: 8px; }
        .firma { text-align: center; font-size: 9px; }
        .firma .nombre { font-weight: bold; font-size: 10px; min-height: 18px; border-bottom: 1px solid #333; padding-bottom: 2px; }
        .firma .puesto { font-size: 9px; color: #555; min-height: 16px; border-bottom: 1px solid #ccc; padding-bottom: 2px; margin-top: 2px; }
        .firma .linea { border-top: 1px solid #333; width: 80%; margin: 4px auto 3px; }
        .firma .label-firma { font-size: 9px; color: #666; }
        
        .sello { text-align: center; font-size: 9px; margin-top: 6px; padding: 6px; border: 1px dashed #ccc; min-height: 30px; display: flex; align-items: center; justify-content: center; }
        .nota { font-size: 8px; margin-top: 6px; text-align: justify; color: #666; line-height: 1.3; }
        .nota strong { color: #333; }
        .footer { margin-top: 6px; padding-top: 6px; border-top: 2px solid #333; font-size: 8px; text-align: center; color: #666; }
        .qr { text-align: right; font-size: 8px; color: #999; margin-top: 2px; }
        .espacio { min-height: 16px; border-bottom: 1px solid #ccc; padding: 1px 5px; }
        .espacio-linea { min-height: 20px; border-bottom: 1px solid #ccc; padding: 1px 5px; margin-bottom: 2px; }
        
        /* Selector de usuario */
        .selector-usuario { max-width: 210mm; width: 100%; margin: 0 auto 15px auto; background: white; padding: 15px 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
        .selector-usuario label { font-weight: 600; font-size: 14px; }
        .selector-usuario select { padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; flex: 1; min-width: 200px; }
        .selector-usuario select:focus { outline: none; border-color: #667eea; }
        .selector-usuario .btn-cargar { padding: 8px 20px; background: #667eea; color: white; border: none; border-radius: 8px; font-size: 14px; cursor: pointer; font-weight: 600; }
        .selector-usuario .btn-cargar:hover { background: #5a6fd6; }
        .selector-usuario .btn-limpiar { padding: 8px 20px; background: #dc3545; color: white; border: none; border-radius: 8px; font-size: 14px; cursor: pointer; font-weight: 600; }
        .selector-usuario .btn-limpiar:hover { background: #c82333; }
        
        .botones { text-align: center; margin-top: 15px; max-width: 210mm; width: 100%; }
        .botones button, .botones a { padding: 10px 30px; background: #667eea; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; margin: 0 5px; text-decoration: none; display: inline-block; font-weight: 600; }
        .botones button:hover, .botones a:hover { background: #5a6fd6; }
        .botones .btn-cerrar { background: #6c757d; }
        .botones .btn-cerrar:hover { background: #5a6268; }
        
        @media print {
            .botones, .selector-usuario { display: none !important; }
            body { padding: 10px !important; background: white !important; margin: 0 !important; display: block !important; }
            .justificante { border: 1px solid #ccc !important; padding: 12px 15px !important; margin: 0 auto !important; max-width: 100% !important; font-size: 10px !important; page-break-after: avoid !important; page-break-inside: avoid !important; box-shadow: none !important; }
            .header h1 { font-size: 12px !important; }
            .header .sub { font-size: 11px !important; }
            .header .datos { font-size: 9px !important; }
            .aviso { font-size: 7.5px !important; padding: 3px 6px !important; }
            .seccion .titulo { font-size: 10px !important; }
            .fila { font-size: 9px !important; }
            .fila .label { font-size: 9px !important; min-width: 150px !important; }
            .fila-check { font-size: 9px !important; gap: 2px 10px !important; }
            .fila-check .check { width: 12px !important; height: 12px !important; line-height: 12px !important; font-size: 10px !important; }
            .firmas { gap: 8px !important; padding-top: 6px !important; margin-top: 6px !important; }
            .firma { font-size: 8px !important; }
            .firma .nombre { font-size: 9px !important; min-height: 14px !important; }
            .firma .puesto { font-size: 8px !important; min-height: 12px !important; }
            .sello { font-size: 8px !important; padding: 4px !important; min-height: 20px !important; }
            .nota { font-size: 7px !important; margin-top: 4px !important; }
            .footer { font-size: 7px !important; margin-top: 4px !important; padding-top: 4px !important; }
            .qr { font-size: 7px !important; }
            .lugar-fecha { font-size: 9px !important; margin-bottom: 4px !important; }
            .seccion { margin-bottom: 4px !important; }
            .espacio-linea { min-height: 14px !important; }
            .espacio { min-height: 12px !important; }
            .justificante { page-break-after: avoid !important; page-break-inside: avoid !important; }
        }
        
        @media (max-width: 768px) {
            .firmas { grid-template-columns: 1fr; gap: 10px; }
            .fila { flex-direction: column; align-items: flex-start; }
            .fila .label { min-width: auto; margin-bottom: 2px; }
            .fila .valor { width: 100%; }
            .header .datos { flex-direction: column; gap: 3px; }
            .fila-check { flex-direction: column; gap: 3px; }
            .selector-usuario { flex-direction: column; }
            .selector-usuario select { width: 100%; }
            .selector-usuario .btn-cargar, .selector-usuario .btn-limpiar { width: 100%; text-align: center; }
            .botones button, .botones a { width: 100%; margin: 5px 0; }
        }
    </style>
</head>
<body>
    <!-- SELECTOR DE USUARIO -->
    <div class="selector-usuario no-print">
        <label for="usuario_select">👤 Cargar datos de usuario:</label>
        <select id="usuario_select" onchange="cargarUsuario()">
            <option value="">-- Seleccionar usuario (opcional) --</option>
            <?php foreach($usuarios as $u): ?>
                <option value="<?php echo $u['id']; ?>" 
                        data-nombre="<?php echo htmlspecialchars($u['nombre']); ?>"
                        data-area="<?php echo htmlspecialchars($u['area']); ?>"
                        data-puesto="<?php echo htmlspecialchars($u['puesto']); ?>"
                        data-nip="<?php echo htmlspecialchars($u['nip']); ?>"
                        data-telefono="<?php echo htmlspecialchars($u['telefono']); ?>">
                    <?php echo htmlspecialchars($u['nombre']); ?> - <?php echo htmlspecialchars($u['area']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="btn-cargar" onclick="cargarUsuario()">📥 Cargar Datos</button>
        <button class="btn-limpiar" onclick="limpiarFormulario()">🗑️ Limpiar</button>
    </div>

    <!-- FORMATO DE JUSTIFICACIÓN EN BLANCO -->
    <div class="justificante" id="justificante">
        <div class="header">
            <h1>UNIDAD DE APOYO ADMINISTRATIVO</h1>
            <div class="sub">ÁREA DE RECURSOS HUMANOS</div>
            <div style="font-size: 13px; font-weight: bold; margin-top: 3px;">JUSTIFICACIÓN DE INCIDENCIAS</div>
            <div class="datos">
                <span><strong>CÓDIGO:</strong> SF/UAA/005/F</span>
                <span><strong>REVISIÓN:</strong> 5</span>
                <span><strong>FECHA:</strong> ___/___/______</span>
            </div>
        </div>

        <div class="aviso">
            Los datos personales proporcionados serán tratados de manera confidencial y utilizados únicamente para fines relacionados con la prestación de nuestros trámites y/o servicios y el cumplimiento de obligaciones legales.
        </div>

        <div class="lugar-fecha">
            <strong>Lugar de Expedición:</strong> <span class="espacio" style="display: inline-block; min-width: 180px;">_________________________</span><br>
            <strong>Fecha:</strong> <span class="espacio" style="display: inline-block; min-width: 120px;">____/____/______</span>
        </div>

        <div class="seccion">
            <div class="titulo">Datos Personales</div>
            <div class="fila">
                <span class="label">Nombre del Servidor Público:</span>
                <span class="valor" id="campo_nombre">_________________________</span>
            </div>
            <div class="fila">
                <span class="label">Área de Adscripción o Comisión:</span>
                <span class="valor" id="campo_area">_________________________</span>
            </div>
            <div class="fila">
                <span class="label">Número de Identificación Personal (NIP):</span>
                <span class="valor" id="campo_nip">_________________</span>
            </div>
            <div class="fila">
                <span class="label">Número de Teléfono o Extensión:</span>
                <span class="valor" id="campo_telefono">_________________</span>
            </div>
            <div class="fila">
                <span class="label">Puesto:</span>
                <span class="valor" id="campo_puesto">_________________________</span>
            </div>
        </div>

        <div class="seccion">
            <div class="titulo">Tipo de Incidencia</div>
            <div class="fila-check">
                <label><span class="check"></span> Vacaciones</label>
                <label><span class="check"></span> Permiso Económico</label>
                <label><span class="check"></span> Comisión</label>
                <label><span class="check"></span> Día de Cumpleaños</label>
                <label><span class="check"></span> Oposición de Entrada</label>
                <label><span class="check"></span> Oposición de Salida</label>
                <label><span class="check"></span> Otro: ___________</label>
            </div>
        </div>

        <div class="seccion">
            <div class="titulo">Descripción de la Incidencia</div>
            <div class="fila">
                <span class="label">Motivos de la Incidencia:</span>
                <span class="valor" style="min-height: 24px;">_________________________________________________</span>
            </div>
            <div class="fila" style="margin-top: 2px;">
                <span class="label">Fecha(s) a Justificar:</span>
                <span class="valor"><strong>____/____/______</strong></span>
            </div>
        </div>

        <div class="seccion">
            <div class="titulo">Firmas de Validación</div>
            <div class="firmas">
                <div class="firma">
                    <div class="nombre" id="firma_solicita">_________________________</div>
                    <div class="puesto" id="puesto_solicita">_________________________</div>
                    <div class="linea"></div>
                    <div class="label-firma">Solicita</div>
                </div>
                <div class="firma">
                    <div class="nombre" id="firma_autoriza">_________________________</div>
                    <div class="puesto" id="puesto_autoriza">_________________________</div>
                    <div class="linea"></div>
                    <div class="label-firma">Autoriza</div>
                </div>
                <div class="firma">
                    <div class="nombre" id="firma_visto">_________________________</div>
                    <div class="puesto" id="puesto_visto">_________________________</div>
                    <div class="linea"></div>
                    <div class="label-firma">Visto Bueno</div>
                </div>
            </div>
        </div>

        <div class="sello"><strong>Sello Oficial</strong></div>

        <div class="nota">
            <strong>NOTA:</strong> El formato no debe presentar alteraciones o tachaduras y deberá cumplir con lo establecido dentro del instructivo de llenado para ser aceptado por el Área de Recursos Humanos; asimismo, no se otorgará permiso con goce de sueldo, antes o después de los periodos vacacionales y días festivos.
        </div>

        <div class="footer">
            Consulte el aviso de privacidad escaneando el Código QR &nbsp;&nbsp;|&nbsp;&nbsp; SF/UP/001/F REV.1
        </div>
        
        <div class="qr">* Formato en Blanco - <?php echo date('d/m/Y'); ?></div>
    </div>

    <!-- BOTONES -->
    <div class="botones no-print">
        <button onclick="window.print()">🖨️ Imprimir</button>
        <button onclick="window.close()" class="btn-cerrar">❌ Cerrar</button>
        <button onclick="limpiarFormulario()" class="btn-cerrar" style="background: #dc3545;">🗑️ Limpiar Todo</button>
    </div>

    <script>
        // ===== CARGAR DATOS DEL USUARIO =====
        function cargarUsuario() {
            const select = document.getElementById('usuario_select');
            const selected = select.options[select.selectedIndex];
            
            if (!selected.value) {
                alert('⚠️ Por favor selecciona un usuario de la lista.');
                return;
            }
            
            const nombre = selected.getAttribute('data-nombre') || '';
            const area = selected.getAttribute('data-area') || '';
            const puesto = selected.getAttribute('data-puesto') || '';
            const nip = selected.getAttribute('data-nip') || '';
            const telefono = selected.getAttribute('data-telefono') || '';
            
            document.getElementById('campo_nombre').textContent = nombre;
            document.getElementById('campo_area').textContent = area;
            document.getElementById('campo_puesto').textContent = puesto;
            document.getElementById('campo_nip').textContent = nip;
            document.getElementById('campo_telefono').textContent = telefono;
            
            document.getElementById('firma_solicita').textContent = nombre;
            document.getElementById('puesto_solicita').textContent = puesto;
            
            alert('✅ Datos de "' + nombre + '" cargados correctamente.');
        }
        
        // ===== LIMPIAR TODOS LOS CAMPOS =====
        function limpiarFormulario() {
            if (!confirm('¿Estás seguro de que quieres limpiar todos los campos?')) {
                return;
            }
            
            document.getElementById('campo_nombre').textContent = '_________________________';
            document.getElementById('campo_area').textContent = '_________________________';
            document.getElementById('campo_puesto').textContent = '_________________________';
            document.getElementById('campo_nip').textContent = '_________________';
            document.getElementById('campo_telefono').textContent = '_________________';
            
            document.getElementById('firma_solicita').textContent = '_________________________';
            document.getElementById('puesto_solicita').textContent = '_________________________';
            document.getElementById('firma_autoriza').textContent = '_________________________';
            document.getElementById('puesto_autoriza').textContent = '_________________________';
            document.getElementById('firma_visto').textContent = '_________________________';
            document.getElementById('puesto_visto').textContent = '_________________________';
            
            document.querySelectorAll('.check').forEach(function(check) {
                check.classList.remove('marcado');
                check.textContent = '';
            });
            
            document.getElementById('usuario_select').selectedIndex = 0;
            
            alert('✅ Todos los campos han sido limpiados.');
        }
        
        // ===== TOGGLE PARA MARCAR/DESMARCAR CHECKS =====
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.check').forEach(function(check) {
                check.addEventListener('click', function(e) {
                    e.stopPropagation();
                    this.classList.toggle('marcado');
                    if (this.classList.contains('marcado')) {
                        this.textContent = '✓';
                    } else {
                        this.textContent = '';
                    }
                });
            });
        });
    </script>
</body>
</html>