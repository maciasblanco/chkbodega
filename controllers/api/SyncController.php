<?php
namespace app\controllers\api;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\filters\Cors;
use yii\helpers\Json;
use app\models\Bodega;
use app\models\PrecioProducto;
use app\models\SyncLog;
use app\models\Producto;
use app\models\TasaDolar;
use app\models\Estado;
use app\models\Municipio;
use app\models\Parroquia;

class SyncController extends Controller
{
    public $enableCsrfValidation = false;
    public $modelClass = '';

    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'corsFilter' => [
                'class' => Cors::class,
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Allow-Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => null,
                    'Access-Control-Max-Age' => 86400,
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'push' => ['POST'],
                    'pull' => ['GET'],
                    'status' => ['GET'],
                    'conflict-resolve' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * POST /api/sync/push - Dispositivo envía datos al servidor
     */
    public function actionPush()
    {
        $request = Yii::$app->request;
        $deviceUuid = $request->getHeaders()->get('X-Device-UUID');
        
        if (!$deviceUuid) {
            Yii::error('Device UUID missing in push request', 'sync');
            return $this->asJson([
                'success' => false,
                'error' => 'Device UUID required',
                'code' => 'DEVICE_UUID_REQUIRED'
            ]);
        }

        $rawData = $request->getRawBody();
        $data = Json::decode($rawData);
        
        if (!$data) {
            Yii::error('Invalid JSON in push request from device: ' . $deviceUuid, 'sync');
            return $this->asJson([
                'success' => false,
                'error' => 'Invalid JSON data',
                'code' => 'INVALID_JSON'
            ]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $results = [
                'bodegas' => ['created' => 0, 'updated' => 0, 'conflicts' => 0, 'errors' => []],
                'precios' => ['created' => 0, 'updated' => 0, 'conflicts' => 0, 'errors' => []],
                'sync_logs' => ['processed' => 0, 'errors' => []]
            ];

            // Procesar logs de sincronización del dispositivo
            if (isset($data['sync_logs'])) {
                foreach ($data['sync_logs'] as $logData) {
                    $log = SyncLog::findOne($logData['id']);
                    if ($log && $log->device_uuid === $deviceUuid) {
                        $log->markAsSynced();
                        $results['sync_logs']['processed']++;
                    }
                }
            }

            // Procesar bodegas
            if (isset($data['bodegas'])) {
                foreach ($data['bodegas'] as $bodegaData) {
                    $result = $this->processBodega($bodegaData, $deviceUuid);
                    $this->updateResults($results['bodegas'], $result);
                }
            }

            // Procesar precios
            if (isset($data['precios'])) {
                foreach ($data['precios'] as $precioData) {
                    $result = $this->processPrecio($precioData, $deviceUuid);
                    $this->updateResults($results['precios'], $result);
                }
            }

            $transaction->commit();

            Yii::info(sprintf(
                'Sync push successful for device %s: %d bodegas, %d precios',
                $deviceUuid,
                $results['bodegas']['created'] + $results['bodegas']['updated'],
                $results['precios']['created'] + $results['precios']['updated']
            ), 'sync');

            return $this->asJson([
                'success' => true,
                'message' => 'Datos sincronizados exitosamente',
                'results' => $results,
                'timestamp' => date('Y-m-d H:i:s'),
                'server_time' => time()
            ]);

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Error en sync/push para dispositivo $deviceUuid: " . $e->getMessage(), 'sync');
            return $this->asJson([
                'success' => false,
                'error' => 'Error interno del servidor',
                'code' => 'INTERNAL_ERROR',
                'debug' => YII_DEBUG ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * GET /api/sync/pull - Dispositivo obtiene datos del servidor
     */
    public function actionPull()
    {
        $request = Yii::$app->request;
        $deviceUuid = $request->getHeaders()->get('X-Device-UUID');
        $lastSync = $request->get('last_sync', 0);
        
        if (!$deviceUuid) {
            return $this->asJson([
                'success' => false,
                'error' => 'Device UUID required',
                'code' => 'DEVICE_UUID_REQUIRED'
            ]);
        }

        try {
            $timestamp = is_numeric($lastSync) ? $lastSync : strtotime($lastSync);
            $dateFrom = date('Y-m-d H:i:s', $timestamp);

            // Configuración del sistema
            $config = [
                'app_name' => Yii::$app->name,
                'version' => '1.0.0',
                'max_file_size' => 10 * 1024 * 1024, // 10MB
                'sync_interval' => 300, // 5 minutos
            ];

            // Datos maestros (cambian poco)
            $masterData = [];
            
            // Solo enviar datos maestros si es la primera sincronización o hace más de 1 día
            if ($timestamp < time() - 86400) {
                $masterData['estados'] = Estado::find()
                    ->select(['id', 'estado', 'iso_3166_2'])
                    ->where(['activo' => true])
                    ->asArray()
                    ->all();

                $masterData['municipios'] = Municipio::find()
                    ->select(['id', 'id_estado', 'municipio'])
                    ->where(['activo' => true])
                    ->asArray()
                    ->all();

                $masterData['parroquias'] = Parroquia::find()
                    ->select(['id', 'id_municipio', 'parroquia'])
                    ->where(['activo' => true])
                    ->asArray()
                    ->all();
            }

            // Datos actualizados
            $updatedData = [
                'bodegas' => Bodega::find()
                    ->where(['>=', 'updated_at', $dateFrom])
                    ->andWhere(['eliminado' => false])
                    ->asArray()
                    ->all(),

                'precios' => PrecioProducto::find()
                    ->where(['>=', 'updated_at', $dateFrom])
                    ->andWhere(['eliminado' => false])
                    ->asArray()
                    ->all(),

                'productos' => Producto::find()
                    ->select(['id', 'nombre', 'codigo', 'unidad_medida', 'en_cesta_basica', 'activo'])
                    ->where(['activo' => true])
                    ->asArray()
                    ->all(),

                'tasas_dolar' => TasaDolar::find()
                    ->where(['>=', 'fecha', date('Y-m-d', strtotime('-30 days'))])
                    ->andWhere(['eliminado' => false])
                    ->orderBy(['fecha' => SORT_DESC])
                    ->asArray()
                    ->all(),
            ];

            // Datos eliminados (soft delete)
            $deletedData = [
                'bodegas_deleted' => Bodega::find()
                    ->select(['id', 'deleted_at'])
                    ->where(['eliminado' => true])
                    ->andWhere(['>=', 'deleted_at', $dateFrom])
                    ->asArray()
                    ->all(),

                'precios_deleted' => PrecioProducto::find()
                    ->select(['id', 'deleted_at'])
                    ->where(['eliminado' => true])
                    ->andWhere(['>=', 'deleted_at', $dateFrom])
                    ->asArray()
                    ->all(),
            ];

            return $this->asJson([
                'success' => true,
                'data' => array_merge($masterData, $updatedData),
                'deleted' => $deletedData,
                'config' => $config,
                'metadata' => [
                    'server_time' => time(),
                    'sync_timestamp' => date('Y-m-d H:i:s'),
                    'data_version' => md5(Json::encode($updatedData)),
                    'total_records' => array_sum(array_map('count', $updatedData))
                ]
            ]);

        } catch (\Exception $e) {
            Yii::error("Error en sync/pull para dispositivo $deviceUuid: " . $e->getMessage(), 'sync');
            return $this->asJson([
                'success' => false,
                'error' => 'Error al obtener datos',
                'code' => 'PULL_ERROR'
            ]);
        }
    }

    /**
     * GET /api/sync/status - Estado de sincronización
     */
    public function actionStatus($device_uuid = null)
    {
        try {
            $deviceUuid = $device_uuid ?: Yii::$app->request->getHeaders()->get('X-Device-UUID');
            
            if (!$deviceUuid) {
                return $this->asJson([
                    'success' => false,
                    'error' => 'Device UUID required'
                ]);
            }

            $pending = SyncLog::find()
                ->where(['device_uuid' => $deviceUuid, 'sync_status' => SyncLog::STATUS_PENDING])
                ->count();

            $lastSyncLog = SyncLog::find()
                ->where(['device_uuid' => $deviceUuid, 'sync_status' => SyncLog::STATUS_SYNCED])
                ->orderBy(['synced_at' => SORT_DESC])
                ->one();

            // Estadísticas de datos
            $stats = [
                'bodegas' => Bodega::find()
                    ->where(['device_uuid' => $deviceUuid])
                    ->count(),
                'precios' => PrecioProducto::find()
                    ->where(['device_uuid' => $deviceUuid])
                    ->count(),
            ];

            return $this->asJson([
                'success' => true,
                'status' => [
                    'device_uuid' => $deviceUuid,
                    'pending_sync' => $pending,
                    'last_sync' => $lastSyncLog ? $lastSyncLog->synced_at : null,
                    'server_time' => date('Y-m-d H:i:s'),
                    'server_timestamp' => time(),
                    'stats' => $stats,
                    'storage' => [
                        'available' => disk_free_space(Yii::getAlias('@app')),
                        'total' => disk_total_space(Yii::getAlias('@app')),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Yii::error("Error en sync/status: " . $e->getMessage(), 'sync');
            return $this->asJson(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/sync/conflict-resolve - Resolver conflictos
     */
    public function actionConflictResolve()
    {
        $request = Yii::$app->request;
        $deviceUuid = $request->getHeaders()->get('X-Device-UUID');
        $data = $request->post();

        if (!$deviceUuid || !isset($data['conflicts'])) {
            return $this->asJson(['success' => false, 'error' => 'Datos inválidos']);
        }

        $results = [];
        foreach ($data['conflicts'] as $conflict) {
            try {
                switch ($conflict['table']) {
                    case 'bodega':
                        $result = $this->resolveBodegaConflict($conflict, $deviceUuid);
                        break;
                    case 'precio_producto':
                        $result = $this->resolvePrecioConflict($conflict, $deviceUuid);
                        break;
                    default:
                        $result = ['success' => false, 'error' => 'Tabla no soportada'];
                }
                $results[] = array_merge(['id' => $conflict['id']], $result);
            } catch (\Exception $e) {
                $results[] = [
                    'id' => $conflict['id'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $this->asJson([
            'success' => true,
            'results' => $results
        ]);
    }

    /**
     * Procesar una bodega recibida del dispositivo
     */
    private function processBodega($data, $deviceUuid)
    {
        // Buscar por ID real o temp_id
        if (!empty($data['id']) && is_numeric($data['id'])) {
            $bodega = Bodega::findOne($data['id']);
            $isNew = false;
        } elseif (!empty($data['temp_id'])) {
            // Buscar por temp_id asignado por el dispositivo
            $bodega = Bodega::find()
                ->where(['device_uuid' => $deviceUuid, 'temp_id' => $data['temp_id']])
                ->one();
            $isNew = !$bodega;
        } else {
            $bodega = null;
            $isNew = true;
        }

        if ($isNew) {
            $bodega = new Bodega();
            // Guardar temp_id para referencia futura
            if (!empty($data['temp_id'])) {
                $bodega->temp_id = $data['temp_id'];
            }
            $operation = 'created';
        } else {
            // Verificar conflicto de versión
            if ($bodega && isset($data['version_hash'])) {
                $currentHash = md5(Json::encode($bodega->attributes));
                if ($data['version_hash'] !== $currentHash) {
                    return [
                        'success' => false,
                        'conflict' => true,
                        'operation' => 'conflict',
                        'server_version' => $bodega->attributes,
                        'client_version' => $data
                    ];
                }
            }
            $operation = 'updated';
        }

        if (!$bodega) {
            return ['success' => false, 'error' => 'No se pudo identificar la bodega'];
        }

        // Filtrar campos permitidos
        $allowedFields = array_merge($bodega->attributes(), ['temp_id']);
        $filteredData = array_intersect_key($data, array_flip($allowedFields));
        
        $bodega->attributes = $filteredData;
        $bodega->device_uuid = $deviceUuid;
        $bodega->sync_status = 'synced';
        $bodega->last_sync = new \yii\db\Expression('CURRENT_TIMESTAMP');
        $bodega->is_dirty = false;

        if ($bodega->save()) {
            // Log de sincronización
            SyncLog::logOperation(
                $deviceUuid,
                'bodega',
                $isNew ? SyncLog::OPERATION_CREATE : SyncLog::OPERATION_UPDATE,
                $bodega->id,
                $data['temp_id'] ?? null,
                $isNew ? null : $bodega->getOldAttributes(),
                $bodega->attributes
            );

            return [
                'success' => true,
                'operation' => $operation,
                'id' => $bodega->id,
                'temp_id' => $data['temp_id'] ?? null,
                'version_hash' => md5(Json::encode($bodega->attributes))
            ];
        } else {
            return [
                'success' => false,
                'error' => $bodega->getErrors(),
                'operation' => 'error'
            ];
        }
    }

    /**
     * Procesar un precio recibido del dispositivo
     */
    private function processPrecio($data, $deviceUuid)
    {
        // Lógica similar a processBodega
        if (!empty($data['id']) && is_numeric($data['id'])) {
            $precio = PrecioProducto::findOne($data['id']);
            $isNew = false;
        } elseif (!empty($data['temp_id'])) {
            $precio = PrecioProducto::find()
                ->where(['device_uuid' => $deviceUuid, 'temp_id' => $data['temp_id']])
                ->one();
            $isNew = !$precio;
        } else {
            $precio = null;
            $isNew = true;
        }

        if ($isNew) {
            $precio = new PrecioProducto();
            if (!empty($data['temp_id'])) {
                $precio->temp_id = $data['temp_id'];
            }
            $operation = 'created';
        } else {
            if ($precio && isset($data['version_hash'])) {
                $currentHash = md5(Json::encode($precio->attributes));
                if ($data['version_hash'] !== $currentHash) {
                    return [
                        'success' => false,
                        'conflict' => true,
                        'operation' => 'conflict',
                        'server_version' => $precio->attributes,
                        'client_version' => $data
                    ];
                }
            }
            $operation = 'updated';
        }

        if (!$precio) {
            return ['success' => false, 'error' => 'No se pudo identificar el precio'];
        }

        $allowedFields = array_merge($precio->attributes(), ['temp_id']);
        $filteredData = array_intersect_key($data, array_flip($allowedFields));
        
        $precio->attributes = $filteredData;
        $precio->device_uuid = $deviceUuid;
        $precio->sync_status = 'synced';
        $precio->last_sync = new \yii\db\Expression('CURRENT_TIMESTAMP');
        $precio->is_dirty = false;

        if ($precio->save()) {
            SyncLog::logOperation(
                $deviceUuid,
                'precio_producto',
                $isNew ? SyncLog::OPERATION_CREATE : SyncLog::OPERATION_UPDATE,
                $precio->id,
                $data['temp_id'] ?? null,
                $isNew ? null : $precio->getOldAttributes(),
                $precio->attributes
            );

            return [
                'success' => true,
                'operation' => $operation,
                'id' => $precio->id,
                'temp_id' => $data['temp_id'] ?? null,
                'version_hash' => md5(Json::encode($precio->attributes))
            ];
        } else {
            return [
                'success' => false,
                'error' => $precio->getErrors(),
                'operation' => 'error'
            ];
        }
    }

    /**
     * Actualizar resultados
     */
    private function updateResults(&$results, $result)
    {
        if ($result['success']) {
            $results[$result['operation']]++;
            if (isset($result['id']) && isset($result['temp_id'])) {
                $results['id_mapping'][] = [
                    'temp_id' => $result['temp_id'],
                    'real_id' => $result['id']
                ];
            }
        } elseif (isset($result['conflict']) && $result['conflict']) {
            $results['conflicts']++;
            $results['conflict_details'][] = $result;
        } else {
            $results['errors'][] = $result['error'];
        }
    }

    /**
     * Resolver conflicto de bodega
     */
    private function resolveBodegaConflict($conflict, $deviceUuid)
    {
        $bodega = Bodega::findOne($conflict['id']);
        if (!$bodega) {
            throw new \Exception('Bodega no encontrada');
        }

        // Estrategia: mantener cambios del servidor, registrar los del cliente
        $resolution = $conflict['resolution'] ?? 'server_wins';
        
        if ($resolution === 'client_wins') {
            $bodega->attributes = $conflict['client_version'];
            $bodega->device_uuid = $deviceUuid;
            $bodega->sync_status = 'synced';
            $bodega->last_sync = new \yii\db\Expression('CURRENT_TIMESTAMP');
            
            if ($bodega->save()) {
                return ['success' => true, 'resolution' => 'client_wins'];
            }
        } else {
            // server_wins o merge
            return ['success' => true, 'resolution' => 'server_wins'];
        }

        return ['success' => false, 'error' => 'No se pudo resolver el conflicto'];
    }

    private function resolvePrecioConflict($conflict, $deviceUuid)
    {
        // Implementación similar para precios
        return ['success' => true, 'resolution' => 'server_wins'];
    }
}