<?php
// Configuración de conexión (YA CONFIGURADA PARA TU SISTEMA)
$host = 'localhost';
$dbname = 'mi_base';
$username = 'root';
$password = '';

// ============================================================
// ATENCIÓN: Asegúrate de que la tabla sea 'servicios' y no 'solicitudes'
// ============================================================
$tabla = 'servicios'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== RELLENANDO FOLIOS PARA REGISTROS EXISTENTES ===\n\n";
    
    // 1. Obtener todos los registros que tienen folio_anual NULL
    $stmt = $pdo->query("SELECT id, fecha_registro FROM $tabla WHERE folio_anual IS NULL ORDER BY id ASC");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($registros)) {
        echo "✅ No hay registros pendientes de folio.\n";
        exit;
    }
    
    echo "📊 Registros a procesar: " . count($registros) . "\n\n";
    
    // 2. Agrupar por año
    $porAnio = [];
    foreach ($registros as $reg) {
        $anio = date('Y', strtotime($reg['fecha_registro']));
        if (!isset($porAnio[$anio])) {
            $porAnio[$anio] = [];
        }
        $porAnio[$anio][] = $reg['id'];
    }
    
    // 3. Procesar cada año
    $totalActualizados = 0;
    foreach ($porAnio as $anio => $ids) {
        echo "📅 Procesando año $anio: " . count($ids) . " registros\n";
        
        // Obtener el máximo folio actual para este año
        $stmt = $pdo->prepare("SELECT MAX(folio_anual) as max_folio FROM $tabla WHERE YEAR(fecha_registro) = ?");
        $stmt->execute([$anio]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $siguienteNumero = ($result['max_folio'] ?? 0) + 1;
        
        // Actualizar cada registro con su folio
        foreach ($ids as $index => $id) {
            $numeroFolio = $siguienteNumero + $index;
            
            $update = $pdo->prepare("UPDATE $tabla SET folio_anual = ? WHERE id = ?");
            $update->execute([$numeroFolio, $id]);
            $totalActualizados++;
            
            echo "  ✅ ID $id → Folio anual: $numeroFolio (año $anio)\n";
        }
    }
    
    echo "\n✅ ¡Proceso completado! Total actualizados: $totalActualizados\n";
    echo "📝 Formato de folio: año-folio_anual (ej: 2026-0001)\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}