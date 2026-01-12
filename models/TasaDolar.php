<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\httpclient\Client;

/**
 * Class TasaDolar
 * @package app\models
 * 
 * @property int $id
 * @property float $tasa_dia
 * @property string $fecha_tasa
 * @property bool $eliminado
 * @property string $d_creacion
 * @property string $u_creacion
 * @property string $dir_ip
 */
class TasaDolar extends ActiveRecord
{
    const MONTO_SEMANAL = 2.00;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contabilidad.tasa_dolar';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tasa_dia', 'fecha_tasa'], 'required'],
            [['tasa_dia'], 'number', 'min' => 0.01],
            [['d_creacion'], 'safe'],
            [['eliminado'], 'boolean'],
            [['u_creacion'], 'string', 'max' => 100],
            [['dir_ip'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tasa_dia' => 'Tasa del Dólar',
            'fecha_tasa' => 'Fecha de la Tasa',
            'eliminado' => 'Eliminado',
            'd_creacion' => 'Fecha de Creación',
            'u_creacion' => 'Usuario de Creación',
            'dir_ip' => 'Dirección IP',
        ];
    }

    /**
     * Obtiene la tasa actual del dólar
     */
    public static function getTasaActual()
    {
        try {
            // Primero intenta obtener de la base de datos
            $tasaModel = self::find()
                ->where(['eliminado' => false])
                ->orderBy(['d_creacion' => SORT_DESC])
                ->one();
            
            // Si no existe o está desactualizada (más de 1 día), obtiene del BCV
            if (!$tasaModel || strtotime($tasaModel->d_creacion) < strtotime('-1 day')) {
                return self::obtenerTasaBCV();
            }
            
            return (float) $tasaModel->tasa_dia;
        } catch (\Exception $e) {
            Yii::error("Error obteniendo tasa: " . $e->getMessage(), 'tasa-dolar');
            return self::obtenerTasaBCV();
        }
    }

    /**
     * Obtiene la tasa del dólar usando múltiples fuentes
     */
    public static function obtenerTasaBCV()
    {
        Yii::info("=== INICIANDO OBTENCIÓN DE TASA ===", 'tasa-dolar');

        // Intentar múltiples fuentes en orden
        $fuentes = [
            'BCV_Directo' => 'obtenerTasaBCVDirecto',
            'BCV_Alternativo' => 'obtenerTasaBCVAlternativo',
            'EnParalelo' => 'obtenerTasaEnParalelo',
            'API_Respaldo' => 'obtenerTasaAPIRespaldo',
        ];

        foreach ($fuentes as $nombre => $metodo) {
            Yii::info("Intentando fuente: {$nombre}", 'tasa-dolar');
            $tasa = self::$metodo();
            
            if ($tasa > 100 && $tasa < 1000) {
                Yii::info("✅ Tasa válida obtenida de {$nombre}: {$tasa}", 'tasa-dolar');
                self::guardarTasa($tasa);
                return $tasa;
            } else {
                Yii::warning("❌ Fuente {$nombre} falló o tasa inválida: {$tasa}", 'tasa-dolar');
            }
        }

        Yii::error("❌ Todas las fuentes fallaron", 'tasa-dolar');
        return self::obtenerUltimaTasaFallback();
    }

    /**
     * Método directo mejorado para BCV
     */
    private static function obtenerTasaBCVDirecto()
    {
        try {
            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl('https://www.bcv.org.ve/')
                ->setHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Language' => 'es-VE,es;q=0.9,en;q=0.8',
                    'Cache-Control' => 'no-cache',
                    'Connection' => 'keep-alive',
                    'Referer' => 'https://www.google.com/'
                ])
                ->setOptions([
                    'timeout' => 30,
                    'sslVerifyPeer' => false,
                    'sslVerifyHost' => false,
                    'followLocation' => true,
                    'maxRedirects' => 5
                ])
                ->send();

            if ($response->isOk) {
                $html = $response->content;
                
                // Guardar HTML para análisis
                $debugPath = Yii::getAlias('@runtime/bcv_directo_' . date('Y-m-d_His') . '.html');
                file_put_contents($debugPath, $html);
                
                return self::extraerTasaAvanzada($html);
            }
        } catch (\Exception $e) {
            Yii::error("Error BCV Directo: " . $e->getMessage(), 'tasa-dolar');
        }
        
        return 0;
    }

    /**
     * Fuente alternativa del BCV
     */
    private static function obtenerTasaBCVAlternativo()
    {
        try {
            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl('https://www.bcv.org.ve/tasas-informativas-sistema-bancario')
                ->setHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ])
                ->setOptions([
                    'timeout' => 20,
                    'sslVerifyPeer' => false
                ])
                ->send();

            if ($response->isOk) {
                $html = $response->content;
                return self::extraerTasaAvanzada($html);
            }
        } catch (\Exception $e) {
            Yii::error("Error BCV Alternativo: " . $e->getMessage(), 'tasa-dolar');
        }
        
        return 0;
    }

    /**
     * Extracción avanzada de tasa con múltiples estrategias
     */
    private static function extraerTasaAvanzada($html)
    {
        // Estrategia 1: Buscar patrones específicos del BCV
        $patrones = [
            // Patrones para formato 214,41900000
            '/USD[^>]*>.*?(\d{1,3}(?:\.\d{3})*,\d{6,})/is',
            '/<strong[^>]*>\s*(\d{1,3}(?:\.\d{3})*,\d{6,})\s*<\/strong>/',
            '/<div[^>]*id="dolar"[^>]*>.*?(\d{1,3}(?:\.\d{3})*,\d{6,})/is',
            '/<div[^>]*class="[^"]*dolar[^"]*"[^>]*>.*?(\d{1,3}(?:\.\d{3})*,\d{6,})/is',
            '/Tasa.*?USD.*?(\d{1,3}(?:\.\d{3})*,\d{6,})/is',
            
            // Patrones más generales
            '/(\d{1,3}(?:\.\d{3})*,\d{2,8})/',
            '/USD.*?(\d{1,3}[,.]\d{2,})/i',
            '/Dólar.*?(\d{1,3}[,.]\d{2,})/i',
        ];

        foreach ($patrones as $patron) {
            if (preg_match($patron, $html, $matches)) {
                $tasa = self::normalizarTasa($matches[1]);
                if ($tasa > 100 && $tasa < 1000) {
                    Yii::info("✅ Tasa encontrada con patrón: {$matches[1]} -> {$tasa}", 'tasa-dolar');
                    return $tasa;
                }
            }
        }

        // Estrategia 2: Búsqueda por líneas
        $lineas = explode("\n", $html);
        foreach ($lineas as $linea) {
            if (stripos($linea, 'USD') !== false || stripos($linea, 'dólar') !== false) {
                if (preg_match('/(\d{1,3}(?:\.\d{3})*,\d{6,})/', $linea, $matches)) {
                    $tasa = self::normalizarTasa($matches[1]);
                    if ($tasa > 100 && $tasa < 1000) {
                        Yii::info("✅ Tasa encontrada en línea: {$matches[1]} -> {$tasa}", 'tasa-dolar');
                        return $tasa;
                    }
                }
            }
        }

        return 0;
    }

    /**
     * Normaliza el formato de la tasa
     */
    private static function normalizarTasa($tasaStr)
    {
        try {
            // Limpiar y normalizar
            $tasaStr = trim($tasaStr);
            $tasaStr = str_replace('.', '', $tasaStr); // Eliminar separadores de miles
            $tasaStr = str_replace(',', '.', $tasaStr); // Convertir coma decimal a punto
            
            $tasa = floatval($tasaStr);
            
            // Redondear a 2 decimales
            return round($tasa, 2);
        } catch (\Exception $e) {
            Yii::error("Error normalizando tasa: {$tasaStr} - " . $e->getMessage(), 'tasa-dolar');
            return 0;
        }
    }

    /**
     * Obtiene tasa en paralelo de fuentes externas
     */
    private static function obtenerTasaEnParalelo()
    {
        try {
            // Intentar con Yahoo Finance (USD/VES)
            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl('https://query1.finance.yahoo.com/v8/finance/chart/USDVES=X')
                ->setOptions(['timeout' => 15])
                ->send();

            if ($response->isOk) {
                $data = $response->getData();
                if (isset($data['chart']['result'][0]['meta']['regularMarketPrice'])) {
                    $tasa = $data['chart']['result'][0]['meta']['regularMarketPrice'];
                    if ($tasa > 100) {
                        Yii::info("✅ Tasa de Yahoo Finance: {$tasa}", 'tasa-dolar');
                        return $tasa;
                    }
                }
            }
        } catch (\Exception $e) {
            Yii::error("Error Yahoo Finance: " . $e->getMessage(), 'tasa-dolar');
        }

        return 0;
    }

    /**
     * API de respaldo
     */
    private static function obtenerTasaAPIRespaldo()
    {
        try {
            // API de exchangerate-api (gratuita)
            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl('https://api.exchangerate-api.com/v4/latest/USD')
                ->setOptions(['timeout' => 15])
                ->send();

            if ($response->isOk) {
                $data = $response->getData();
                if (isset($data['rates']['VES'])) {
                    $tasa = floatval($data['rates']['VES']);
                    if ($tasa > 100) {
                        Yii::info("✅ Tasa de ExchangeRate-API: {$tasa}", 'tasa-dolar');
                        return $tasa;
                    }
                }
            }
        } catch (\Exception $e) {
            Yii::error("Error ExchangeRate-API: " . $e->getMessage(), 'tasa-dolar');
        }

        return 0;
    }

    /**
     * Obtiene la última tasa como fallback
     */
    private static function obtenerUltimaTasaFallback()
    {
        try {
            $ultimaTasa = self::find()
                ->where(['eliminado' => false])
                ->orderBy(['d_creacion' => SORT_DESC])
                ->one();
                
            $tasaFallback = $ultimaTasa ? (float) $ultimaTasa->tasa_dia : 214.42;
            Yii::info("🔄 Usando tasa fallback: {$tasaFallback}", 'tasa-dolar');
            return $tasaFallback;
        } catch (\Exception $e) {
            Yii::error("❌ Error en fallback: " . $e->getMessage(), 'tasa-dolar');
            return 214.42;
        }
    }

    /**
     * Guarda la tasa en la base de datos
     */
    private static function guardarTasa($tasa)
    {
        try {
            // Verificar si ya existe una tasa para hoy
            $hoy = date('Y-m-d');
            $tasaExistente = self::find()
                ->where(['fecha_tasa' => $hoy, 'eliminado' => false])
                ->one();

            if ($tasaExistente) {
                // Actualizar tasa existente
                $tasaExistente->tasa_dia = $tasa;
                $tasaExistente->d_creacion = date('Y-m-d H:i:s');
                $tasaExistente->u_creacion = Yii::$app->user->identity->username ?? 'sistema_auto';
                $tasaExistente->dir_ip = Yii::$app->request->userIP ?? '127.0.0.1';
                
                if ($tasaExistente->save()) {
                    Yii::info("Tasa actualizada para hoy: {$tasa}", 'tasa-dolar');
                    return true;
                } else {
                    Yii::error("Error actualizando tasa: " . print_r($tasaExistente->errors, true), 'tasa-dolar');
                    return false;
                }
            } else {
                // Crear nueva tasa
                $model = new self();
                $model->tasa_dia = $tasa;
                $model->fecha_tasa = $hoy;
                $model->eliminado = false;
                $model->d_creacion = date('Y-m-d H:i:s');
                $model->u_creacion = Yii::$app->user->identity->username ?? 'sistema_auto';
                $model->dir_ip = Yii::$app->request->userIP ?? '127.0.0.1';
                
                if ($model->save()) {
                    Yii::info("Nueva tasa guardada: {$tasa}", 'tasa-dolar');
                    return true;
                } else {
                    Yii::error("Error guardando nueva tasa: " . print_r($model->errors, true), 'tasa-dolar');
                    return false;
                }
            }
        } catch (\Exception $e) {
            Yii::error("Error en guardarTasa: " . $e->getMessage(), 'tasa-dolar');
            return false;
        }
    }

    /**
     * Obtiene el historial de tasas
     */
    public static function getHistorial($dias = 30)
    {
        try {
            return self::find()
                ->where(['eliminado' => false])
                ->andWhere(['>=', 'd_creacion', date('Y-m-d', strtotime("-$dias days"))])
                ->orderBy(['d_creacion' => SORT_DESC])
                ->all();
        } catch (\Exception $e) {
            Yii::error("Error obteniendo historial: " . $e->getMessage(), 'tasa-dolar');
            return [];
        }
    }

    /**
     * Forzar actualización manual
     */
    public static function actualizarTasaManual()
    {
        Yii::info("Solicitada actualización manual de tasa", 'tasa-dolar');
        return self::obtenerTasaBCV();
    }

    /**
     * Obtiene la última tasa registrada
     */
    public static function getUltimaTasa()
    {
        try {
            return self::find()
                ->where(['eliminado' => false])
                ->orderBy(['d_creacion' => SORT_DESC])
                ->one();
        } catch (\Exception $e) {
            Yii::error("Error obteniendo última tasa: " . $e->getMessage(), 'tasa-dolar');
            return null;
        }
    }

    /**
     * Obtiene la tasa para una fecha específica
     */
    public static function getTasaPorFecha($fecha)
    {
        try {
            return self::find()
                ->where(['eliminado' => false])
                ->andWhere(['<=', 'fecha_tasa', $fecha])
                ->orderBy(['fecha_tasa' => SORT_DESC])
                ->one();
        } catch (\Exception $e) {
            Yii::error("Error obteniendo tasa por fecha: " . $e->getMessage(), 'tasa-dolar');
            return null;
        }
    }

    /**
     * Establece una tasa manualmente
     */
    public static function setTasaManual($tasa)
    {
        try {
            $model = new self();
            $model->tasa_dia = $tasa;
            $model->fecha_tasa = date('Y-m-d');
            $model->eliminado = false;
            $model->d_creacion = date('Y-m-d H:i:s');
            $model->u_creacion = Yii::$app->user->identity->username ?? 'sistema_manual';
            $model->dir_ip = Yii::$app->request->userIP ?? '127.0.0.1';
            
            $resultado = $model->save();
            if ($resultado) {
                Yii::info("Tasa manual guardada: {$tasa}", 'tasa-dolar');
            } else {
                Yii::error("Error guardando tasa manual: " . print_r($model->errors, true), 'tasa-dolar');
            }
            return $resultado;
        } catch (\Exception $e) {
            Yii::error("Error estableciendo tasa manual: " . $e->getMessage(), 'tasa-dolar');
            return false;
        }
    }

    /**
     * Método para probar todas las fuentes
     */
    public static function probarTodasLasFuentes()
    {
        Yii::info("=== INICIO PRUEBA TODAS LAS FUENTES ===", 'tasa-dolar');
        
        $fuentes = [
            'BCV_Directo' => 'obtenerTasaBCVDirecto',
            'BCV_Alternativo' => 'obtenerTasaBCVAlternativo',
            'EnParalelo' => 'obtenerTasaEnParalelo',
            'API_Respaldo' => 'obtenerTasaAPIRespaldo',
        ];

        $resultados = [];
        foreach ($fuentes as $nombre => $metodo) {
            $tasa = self::$metodo();
            $resultados[$nombre] = $tasa;
            Yii::info("Fuente {$nombre}: {$tasa}", 'tasa-dolar');
        }

        Yii::info("=== FIN PRUEBA ===", 'tasa-dolar');
        return $resultados;
    }

    /**
     * Before Save - maneja campos automáticos
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                if (empty($this->d_creacion)) {
                    $this->d_creacion = date('Y-m-d H:i:s');
                }
                if (empty($this->u_creacion)) {
                    $this->u_creacion = Yii::$app->user->identity->username ?? 'sistema';
                }
                if (empty($this->dir_ip)) {
                    $this->dir_ip = Yii::$app->request->userIP ?? '127.0.0.1';
                }
                if (empty($this->fecha_tasa)) {
                    $this->fecha_tasa = date('Y-m-d');
                }
                if ($this->eliminado === null) {
                    $this->eliminado = false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Formatea la tasa para mostrar
     */
    public function getTasaFormateada()
    {
        return number_format($this->tasa_dia, 2, ',', '.');
    }

    /**
     * Obtiene la fecha formateada
     */
    public function getFechaActualizacionFormateada()
    {
        return Yii::$app->formatter->asDatetime($this->d_creacion);
    }
}