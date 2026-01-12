<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class SyncLog extends ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_SYNCED = 'synced';
    const STATUS_ERROR = 'error';
    
    const OPERATION_CREATE = 'CREATE';
    const OPERATION_UPDATE = 'UPDATE';
    const OPERATION_DELETE = 'DELETE';

    public static function tableName()
    {
        return 'sync_log';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => null,
                'value' => new \yii\db\Expression('CURRENT_TIMESTAMP'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['device_uuid', 'table_name', 'operation'], 'required'],
            [['record_id'], 'integer'],
            [['temp_id', 'device_uuid', 'table_name', 'operation', 'sync_status', 'error_message'], 'string'],
            [['data_before', 'data_after'], 'safe'],
            [['sync_status'], 'default', 'value' => self::STATUS_PENDING],
            [['created_at', 'synced_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'device_uuid' => 'Dispositivo',
            'table_name' => 'Tabla',
            'record_id' => 'ID Registro',
            'operation' => 'Operación',
            'sync_status' => 'Estado Sincronización',
            'created_at' => 'Creado',
            'synced_at' => 'Sincronizado',
            'error_message' => 'Error',
        ];
    }

    public static function logOperation($deviceUuid, $tableName, $operation, $recordId = null, $tempId = null, $dataBefore = null, $dataAfter = null)
    {
        $log = new self();
        $log->device_uuid = $deviceUuid;
        $log->table_name = $tableName;
        $log->operation = $operation;
        $log->record_id = $recordId;
        $log->temp_id = $tempId;
        $log->data_before = $dataBefore;
        $log->data_after = $dataAfter;
        
        return $log->save();
    }

    public function markAsSynced()
    {
        $this->sync_status = self::STATUS_SYNCED;
        $this->synced_at = new \yii\db\Expression('CURRENT_TIMESTAMP');
        return $this->save(false);
    }

    public function markAsError($error)
    {
        $this->sync_status = self::STATUS_ERROR;
        $this->error_message = substr($error, 0, 1000);
        return $this->save(false);
    }

    public static function getPendingForDevice($deviceUuid, $limit = 100)
    {
        return self::find()
            ->where(['device_uuid' => $deviceUuid, 'sync_status' => self::STATUS_PENDING])
            ->orderBy(['created_at' => SORT_ASC])
            ->limit($limit)
            ->all();
    }
}