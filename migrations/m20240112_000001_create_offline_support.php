<?php
use yii\db\Migration;
use yii\db\Expression;

class m20240112_000001_create_offline_support extends Migration
{
    public function safeUp()
    {
        // Tabla de registro de sincronización
        $this->createTable('sync_log', [
            'id' => $this->bigPrimaryKey(),
            'device_uuid' => $this->string(255)->notNull(),
            'table_name' => $this->string(100)->notNull(),
            'record_id' => $this->bigInteger(),
            'temp_id' => $this->string(100),
            'operation' => $this->string(10)->notNull(), // CREATE, UPDATE, DELETE
            'data_before' => $this->json(),
            'data_after' => $this->json(),
            'sync_status' => $this->string(20)->defaultValue('pending'),
            'created_at' => $this->timestamp()->defaultValue(new Expression('CURRENT_TIMESTAMP')),
            'synced_at' => $this->timestamp()->null(),
            'error_message' => $this->text(),
        ]);

        $this->createIndex('idx_sync_device_status', 'sync_log', ['device_uuid', 'sync_status']);
        $this->createIndex('idx_sync_table_record', 'sync_log', ['table_name', 'record_id']);
        $this->createIndex('idx_sync_created', 'sync_log', ['created_at']);

        // Campos para bodegas
        if ($this->getDb()->getSchema()->getTableSchema('bodega')) {
            $this->addColumn('bodega', 'device_uuid', $this->string(255));
            $this->addColumn('bodega', 'sync_status', $this->string(20)->defaultValue('synced'));
            $this->addColumn('bodega', 'last_sync', $this->timestamp()->null());
            $this->addColumn('bodega', 'is_dirty', $this->boolean()->defaultValue(false));
            $this->addColumn('bodega', 'temp_id', $this->string(100));
            
            $this->createIndex('idx_bodega_device', 'bodega', ['device_uuid']);
            $this->createIndex('idx_bodega_dirty', 'bodega', ['is_dirty']);
        }

        // Campos para precio_producto
        if ($this->getDb()->getSchema()->getTableSchema('precio_producto')) {
            $this->addColumn('precio_producto', 'device_uuid', $this->string(255));
            $this->addColumn('precio_producto', 'sync_status', $this->string(20)->defaultValue('synced'));
            $this->addColumn('precio_producto', 'last_sync', $this->timestamp()->null());
            $this->addColumn('precio_producto', 'is_dirty', $this->boolean()->defaultValue(false));
            $this->addColumn('precio_producto', 'temp_id', $this->string(100));
            
            $this->createIndex('idx_precio_device', 'precio_producto', ['device_uuid']);
            $this->createIndex('idx_precio_dirty', 'precio_producto', ['is_dirty']);
        }

        // Tabla para caché de dispositivos
        $this->createTable('device_cache', [
            'id' => $this->bigPrimaryKey(),
            'device_uuid' => $this->string(255)->notNull(),
            'cache_key' => $this->string(255)->notNull(),
            'cache_data' => $this->json()->notNull(),
            'expires_at' => $this->timestamp()->null(),
            'created_at' => $this->timestamp()->defaultValue(new Expression('CURRENT_TIMESTAMP')),
            'updated_at' => $this->timestamp()->defaultValue(new Expression('CURRENT_TIMESTAMP')),
        ]);

        $this->createIndex('idx_device_cache_key', 'device_cache', ['device_uuid', 'cache_key'], true);
        $this->createIndex('idx_device_cache_expires', 'device_cache', ['expires_at']);
    }

    public function safeDown()
    {
        $this->dropTable('device_cache');
        
        if ($this->getDb()->getSchema()->getTableSchema('precio_producto')) {
            $this->dropIndex('idx_precio_dirty', 'precio_producto');
            $this->dropIndex('idx_precio_device', 'precio_producto');
            $this->dropColumn('precio_producto', 'temp_id');
            $this->dropColumn('precio_producto', 'is_dirty');
            $this->dropColumn('precio_producto', 'last_sync');
            $this->dropColumn('precio_producto', 'sync_status');
            $this->dropColumn('precio_producto', 'device_uuid');
        }
        
        if ($this->getDb()->getSchema()->getTableSchema('bodega')) {
            $this->dropIndex('idx_bodega_dirty', 'bodega');
            $this->dropIndex('idx_bodega_device', 'bodega');
            $this->dropColumn('bodega', 'temp_id');
            $this->dropColumn('bodega', 'is_dirty');
            $this->dropColumn('bodega', 'last_sync');
            $this->dropColumn('bodega', 'sync_status');
            $this->dropColumn('bodega', 'device_uuid');
        }
        
        $this->dropTable('sync_log');
    }
}