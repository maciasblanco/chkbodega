/**
 * Sistema Offline Core - Manejo básico de IndexedDB
 */
class OfflineCore {
    constructor() {
        this.dbName = 'BodegasOfflineDB';
        this.dbVersion = 2;
        this.db = null;
        this.deviceUuid = this.getDeviceUuid();
        this.isOnline = navigator.onLine;
        this.syncQueue = [];
        this.isSyncing = false;
        
        this.init();
    }
    
    /**
     * Generar UUID único para el dispositivo
     */
    getDeviceUuid() {
        let uuid = localStorage.getItem('device_uuid');
        if (!uuid) {
            // Generar UUID v4
            uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                const r = Math.random() * 16 | 0;
                const v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
            localStorage.setItem('device_uuid', uuid);
        }
        return uuid;
    }
    
    /**
     * Inicializar sistema
     */
    async init() {
        try {
            // Escuchar cambios de conexión
            window.addEventListener('online', () => this.handleOnline());
            window.addEventListener('offline', () => this.handleOffline());
            
            // Inicializar base de datos
            await this.initDatabase();
            
            // Verificar conexión inicial
            this.updateConnectionStatus();
            
            console.log('OfflineCore initialized:', {
                deviceUuid: this.deviceUuid,
                online: this.isOnline,
                db: this.db ? 'ready' : 'failed'
            });
            
        } catch (error) {
            console.error('Error initializing OfflineCore:', error);
        }
    }
    
    /**
     * Inicializar IndexedDB
     */
    initDatabase() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);
            
            request.onerror = (event) => {
                console.error('IndexedDB error:', event.target.error);
                reject(event.target.error);
            };
            
            request.onsuccess = (event) => {
                this.db = event.target.result;
                console.log('IndexedDB opened successfully');
                resolve(this.db);
            };
            
            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                console.log('IndexedDB upgrade needed:', event.oldVersion, '→', event.newVersion);
                
                // Crear stores si no existen
                if (!db.objectStoreNames.contains('bodegas')) {
                    const store = db.createObjectStore('bodegas', { 
                        keyPath: 'id',
                        autoIncrement: true 
                    });
                    store.createIndex('temp_id', 'temp_id', { unique: false });
                    store.createIndex('sync_status', 'sync_status', { unique: false });
                    store.createIndex('is_dirty', 'is_dirty', { unique: false });
                    store.createIndex('device_uuid', 'device_uuid', { unique: false });
                }
                
                if (!db.objectStoreNames.contains('precios')) {
                    const store = db.createObjectStore('precios', { 
                        keyPath: 'id',
                        autoIncrement: true 
                    });
                    store.createIndex('temp_id', 'temp_id', { unique: false });
                    store.createIndex('sync_status', 'sync_status', { unique: false });
                    store.createIndex('is_dirty', 'is_dirty', { unique: false });
                    store.createIndex('device_uuid', 'device_uuid', { unique: false });
                    store.createIndex('bodega_id', 'id_bodega', { unique: false });
                }
                
                if (!db.objectStoreNames.contains('productos')) {
                    const store = db.createObjectStore('productos', { keyPath: 'id' });
                    store.createIndex('en_cesta_basica', 'en_cesta_basica', { unique: false });
                }
                
                if (!db.objectStoreNames.contains('sync_queue')) {
                    const store = db.createObjectStore('sync_queue', {
                        keyPath: 'id',
                        autoIncrement: true
                    });
                    store.createIndex('status', 'status', { unique: false });
                    store.createIndex('created_at', 'created_at', { unique: false });
                }
                
                if (!db.objectStoreNames.contains('cache')) {
                    const store = db.createObjectStore('cache', { keyPath: 'key' });
                    store.createIndex('expires', 'expires', { unique: false });
                }
            };
        });
    }
    
    /**
     * Manejar conexión online
     */
    handleOnline() {
        this.isOnline = true;
        console.log('Device is online');
        this.showNotification('Conectado a Internet', 'success');
        this.triggerSync();
    }
    
    /**
     * Manejar conexión offline
     */
    handleOffline() {
        this.isOnline = false;
        console.log('Device is offline');
        this.showNotification('Modo offline activado', 'warning');
    }
    
    /**
     * Actualizar indicador de conexión en UI
     */
    updateConnectionStatus() {
        const indicator = document.getElementById('connection-status');
        if (indicator) {
            if (this.isOnline) {
                indicator.className = 'badge bg-success';
                indicator.innerHTML = '<i class="fas fa-wifi"></i> En línea';
            } else {
                indicator.className = 'badge bg-warning';
                indicator.innerHTML = '<i class="fas fa-wifi-slash"></i> Offline';
            }
        }
    }
    
    /**
     * Guardar bodega en IndexedDB
     */
    async saveBodega(bodegaData) {
        if (!this.db) {
            throw new Error('Database not initialized');
        }
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['bodegas', 'sync_queue'], 'readwrite');
            
            // Preparar datos
            const now = new Date().toISOString();
            const record = {
                ...bodegaData,
                device_uuid: this.deviceUuid,
                sync_status: 'pending',
                is_dirty: true,
                created_at: bodegaData.id ? bodegaData.created_at : now,
                updated_at: now
            };
            
            // Generar temp_id si es nuevo
            if (!record.id && !record.temp_id) {
                record.temp_id = 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            }
            
            const bodegasStore = transaction.objectStore('bodegas');
            const request = bodegasStore.put(record);
            
            // Agregar a cola de sincronización
            const queueStore = transaction.objectStore('sync_queue');
            queueStore.add({
                type: 'bodega',
                action: record.id ? 'update' : 'create',
                data: record,
                status: 'pending',
                created_at: now
            });
            
            request.onsuccess = (event) => {
                console.log('Bodega saved locally:', record.temp_id || record.id);
                this.showNotification('Bodega guardada localmente', 'info');
                
                // Intentar sincronización si hay conexión
                if (this.isOnline) {
                    setTimeout(() => this.triggerSync(), 1000);
                }
                
                resolve({
                    success: true,
                    data: record,
                    localId: event.target.result
                });
            };
            
            request.onerror = (event) => {
                console.error('Error saving bodega:', event.target.error);
                reject(event.target.error);
            };
        });
    }
    
    /**
     * Obtener bodegas locales
     */
    async getBodegas(filters = {}) {
        if (!this.db) {
            throw new Error('Database not initialized');
        }
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['bodegas'], 'readonly');
            const store = transaction.objectStore('bodegas');
            const request = store.getAll();
            
            request.onsuccess = (event) => {
                let bodegas = event.target.result;
                
                // Aplicar filtros básicos
                if (filters.nombre) {
                    bodegas = bodegas.filter(b => 
                        b.nombre_bodega && 
                        b.nombre_bodega.toLowerCase().includes(filters.nombre.toLowerCase())
                    );
                }
                
                if (filters.id_estado) {
                    bodegas = bodegas.filter(b => b.id_estado == filters.id_estado);
                }
                
                resolve(bodegas);
            };
            
            request.onerror = (event) => {
                reject(event.target.error);
            };
        });
    }
    
    /**
     * Disparar sincronización
     */
    async triggerSync() {
        if (!this.isOnline || this.isSyncing) {
            return;
        }
        
        this.isSyncing = true;
        console.log('Starting sync...');
        
        try {
            // 1. Obtener datos pendientes
            const pendingData = await this.getPendingData();
            
            if (pendingData.bodegas.length === 0 && pendingData.precios.length === 0) {
                console.log('No pending data to sync');
                this.isSyncing = false;
                return;
            }
            
            // 2. Enviar al servidor
            const result = await this.sendToServer(pendingData);
            
            if (result.success) {
                // 3. Actualizar estado local
                await this.markAsSynced(pendingData);
                console.log('Sync completed successfully');
                
                // 4. Obtener datos actualizados del servidor
                await this.pullFromServer();
            } else {
                console.error('Sync failed:', result.error);
            }
            
        } catch (error) {
            console.error('Sync error:', error);
        } finally {
            this.isSyncing = false;
        }
    }
    
    /**
     * Obtener datos pendientes de sincronización
     */
    async getPendingData() {
        const [bodegas, precios] = await Promise.all([
            this.getPendingByType('bodegas'),
            this.getPendingByType('precios')
        ]);
        
        return { bodegas, precios };
    }
    
    async getPendingByType(type) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([type], 'readonly');
            const store = transaction.objectStore(type);
            const index = store.index('is_dirty');
            const request = index.getAll(IDBKeyRange.only(true));
            
            request.onsuccess = (event) => {
                resolve(event.target.result || []);
            };
            
            request.onerror = (event) => {
                reject(event.target.error);
            };
        });
    }
    
    /**
     * Enviar datos al servidor
     */
    async sendToServer(data) {
        try {
            const response = await fetch('/api/sync/push', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Device-UUID': this.deviceUuid
                },
                body: JSON.stringify(data)
            });
            
            return await response.json();
        } catch (error) {
            console.error('Error sending to server:', error);
            throw error;
        }
    }
    
    /**
     * Marcar datos como sincronizados
     */
    async markAsSynced(data) {
        const transaction = this.db.transaction(['bodegas', 'precios', 'sync_queue'], 'readwrite');
        
        // Actualizar bodegas
        const bodegasStore = transaction.objectStore('bodegas');
        data.bodegas.forEach(bodega => {
            bodega.is_dirty = false;
            bodega.sync_status = 'synced';
            bodegasStore.put(bodega);
        });
        
        // Actualizar precios
        const preciosStore = transaction.objectStore('precios');
        data.precios.forEach(precio => {
            precio.is_dirty = false;
            precio.sync_status = 'synced';
            preciosStore.put(precio);
        });
        
        // Limpiar cola de sincronización
        const queueStore = transaction.objectStore('sync_queue');
        queueStore.clear();
        
        return new Promise((resolve, reject) => {
            transaction.oncomplete = resolve;
            transaction.onerror = (event) => reject(event.target.error);
        });
    }
    
    /**
     * Obtener datos del servidor
     */
    async pullFromServer() {
        try {
            const lastSync = localStorage.getItem('last_sync') || '0';
            
            const response = await fetch(`/api/sync/pull?last_sync=${lastSync}`, {
                headers: {
                    'X-Device-UUID': this.deviceUuid
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                await this.updateLocalData(data.data);
                localStorage.setItem('last_sync', Date.now().toString());
                console.log('Local data updated from server');
            }
            
            return data;
        } catch (error) {
            console.error('Error pulling from server:', error);
            throw error;
        }
    }
    
    /**
     * Actualizar datos locales con información del servidor
     */
    async updateLocalData(serverData) {
        const transaction = this.db.transaction(
            ['bodegas', 'precios', 'productos', 'cache'], 
            'readwrite'
        );
        
        // Actualizar bodegas
        if (serverData.bodegas) {
            const store = transaction.objectStore('bodegas');
            serverData.bodegas.forEach(bodega => {
                bodega.is_dirty = false;
                bodega.sync_status = 'synced';
                store.put(bodega);
            });
        }
        
        // Actualizar precios
        if (serverData.precios) {
            const store = transaction.objectStore('precios');
            serverData.precios.forEach(precio => {
                precio.is_dirty = false;
                precio.sync_status = 'synced';
                store.put(precio);
            });
        }
        
        // Actualizar productos
        if (serverData.productos) {
            const store = transaction.objectStore('productos');
            serverData.productos.forEach(producto => {
                store.put(producto);
            });
        }
        
        return new Promise((resolve, reject) => {
            transaction.oncomplete = resolve;
            transaction.onerror = (event) => reject(event.target.error);
        });
    }
    
    /**
     * Mostrar notificación
     */
    showNotification(message, type = 'info') {
        // Implementación básica
        const alertClass = {
            'success': 'alert-success',
            'warning': 'alert-warning',
            'error': 'alert-danger',
            'info': 'alert-info'
        }[type] || 'alert-info';
        
        console.log(`[${type.toUpperCase()}] ${message}`);
        
        // Podemos implementar un sistema más sofisticado después
        if (typeof Toast !== 'undefined') {
            // Si tenemos una librería de toasts
        }
    }
    
    /**
     * Verificar conexión
     */
    checkConnection() {
        return this.isOnline;
    }
    
    /**
     * Forzar sincronización manual
     */
    async forceSync() {
        return await this.triggerSync();
    }
}

// Exportar para uso global
if (typeof window !== 'undefined') {
    window.OfflineCore = OfflineCore;
}