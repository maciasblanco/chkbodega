<?php
// controllers/TasaDolarController.php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use app\models\TasaDolar;

class TasaDolarController extends Controller
{
    public function actionIndex()
    {
        $tasaActual = TasaDolar::getTasaActual();
        $historial = TasaDolar::getHistorial(5);
        
        return $this->render('index', [
            'tasaActual' => $tasaActual,
            'historial' => $historial,
        ]);
    }

    public function actionActualizar()
    {
        if (Yii::$app->request->isPost) {
            $tasa = Yii::$app->request->post('tasa_dolar');
            
            if (is_numeric($tasa) && $tasa > 0) {
                if (TasaDolar::setTasaManual((float)$tasa)) {
                    Yii::$app->session->setFlash('success', 
                        "✅ Tasa del dólar actualizada: Bs. " . number_format($tasa, 2));
                } else {
                    Yii::$app->session->setFlash('error', 
                        "❌ Error al guardar la tasa del dólar.");
                }
            } else {
                Yii::$app->session->setFlash('error', 
                    "❌ La tasa del dólar debe ser un número mayor a cero.");
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Historial completo con paginación
     */
    public function actionHistorial()
    {
        $query = TasaDolar::find()
            ->where(['eliminado' => false])
            ->orderBy(['d_creacion' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        
        return $this->render('historial', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Actualización automática desde BCV
     */
    public function actionActualizarAutomatico()
    {
        $nuevaTasa = TasaDolar::obtenerTasaBCV();
        
        if ($nuevaTasa > 100) {
            Yii::$app->session->setFlash('success', 
                "✅ Tasa actualizada automáticamente: Bs. " . number_format($nuevaTasa, 2));
        } else {
            Yii::$app->session->setFlash('error', 
                "❌ No se pudo obtener la tasa automáticamente. Intente manualmente.");
        }
        
        return $this->redirect(['index']);
    }

    /**
     * API para obtener la tasa actual (útil para AJAX)
     */
    public function actionApiTasaActual()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        return [
            'success' => true,
            'tasa' => TasaDolar::getTasaActual(),
            'fecha' => date('Y-m-d'),
            'timestamp' => time()
        ];
    }

    /**
     * Probar todas las fuentes de tasa
     */
    public function actionProbarFuentes()
    {
        $resultados = TasaDolar::probarTodasLasFuentes();
        
        return $this->render('probar_fuentes', [
            'resultados' => $resultados,
        ]);
    }

    /**
     * Exportar historial a CSV
     */
    public function actionExportarHistorial()
    {
        $historial = TasaDolar::getHistorial(365); // Último año
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=historial_tasas_dolar_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Fecha', 'Tasa (Bs)', 'Fecha Registro', 'Usuario'], ';');
        
        foreach ($historial as $tasa) {
            fputcsv($output, [
                $tasa->fecha_tasa,
                number_format($tasa->tasa_dia, 2, ',', ''),
                $tasa->d_creacion,
                $tasa->u_creacion
            ], ';');
        }
        
        fclose($output);
        exit;
    }

    /**
     * Obtener tasa actual via AJAX
     */
    public function actionGetTasaActual()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $tasa = TasaDolar::getTasaActual();
            return [
                'success' => true,
                'tasa' => $tasa,
                'tasa_formateada' => 'Bs. ' . number_format($tasa, 2),
                'fecha' => date('d/m/Y')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}