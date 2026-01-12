<?php
// commands/ActualizarTasaCommand.php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\TasaDolar;

class ActualizarTasaCommand extends Controller
{
    public function actionIndex()
    {
        echo "🔄 Iniciando actualización programada de tasa...\n";
        
        $tasa = TasaDolar::obtenerTasaBCV();
        
        if ($tasa > 100) {
            echo "✅ Tasa actualizada: Bs. " . number_format($tasa, 2) . "\n";
            
            // Registrar en log especial para cron
            Yii::info("CRON - Tasa actualizada: Bs. {$tasa}", 'tasa-cron');
            
            return ExitCode::OK;
        } else {
            echo "❌ Error en actualización automática\n";
            Yii::error("CRON - Error actualizando tasa: {$tasa}", 'tasa-cron');
            return ExitCode::SOFTWARE;
        }
    }
    
    /**
     * Comando para forzar actualización manual via consola
     */
    public function actionForzar()
    {
        echo "🔄 Forzando actualización de tasa...\n";
        
        $resultados = TasaDolar::probarTodasLasFuentes();
        
        foreach ($resultados as $fuente => $tasa) {
            echo "  {$fuente}: " . ($tasa > 100 ? "✅ Bs. " . number_format($tasa, 2) : "❌ Falló") . "\n";
        }
        
        return ExitCode::OK;
    }
}