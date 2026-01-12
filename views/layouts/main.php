<?php
// En la sección head del layout
\yii\bootstrap5\BootstrapAsset::register($this);
\yii\web\JqueryAsset::register($this);
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="theme-color" content="#0d6efd">
    <link rel="manifest" href="/manifest.json">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php $this->beginBody() ?>
    
    <!-- Barra de estado de conexión -->
    <div class="connection-bar fixed-top" style="top: 0; z-index: 1030; display: none;">
        <div class="alert alert-warning mb-0 rounded-0 text-center py-1" id="offline-alert">
            <i class="bi bi-wifi-off"></i> Estás trabajando en modo offline
        </div>
    </div>
    
    <!-- Contenido principal -->
    <div class="container-fluid flex-grow-1">
        <?= $content ?>
    </div>
    
    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <?php
    // Registrar el core offline
    $this->registerJsFile('/js/offline-core.js', ['position' => \yii\web\View::POS_END]);
    
    // Script para inicializar offline mode
    $this->registerJs(<<<JS
    // Inicializar OfflineCore
    let offlineCore = null;
    
    $(document).ready(function() {
        if (typeof OfflineCore !== 'undefined') {
            offlineCore = new OfflineCore();
            
            // Actualizar barra de conexión
            function updateConnectionBar() {
                const isOnline = offlineCore ? offlineCore.checkConnection() : navigator.onLine;
                const bar = $('.connection-bar');
                const alert = $('#offline-alert');
                
                if (!isOnline) {
                    bar.fadeIn();
                    alert.removeClass('alert-success').addClass('alert-warning')
                         .html('<i class="bi bi-wifi-off"></i> Modo offline activado');
                } else {
                    bar.fadeOut();
                }
            }
            
            // Actualizar periódicamente
            setInterval(updateConnectionBar, 5000);
            updateConnectionBar();
            
            // Interceptar envío de formularios
            $(document).on('submit', 'form[data-offline-enabled]', function(e) {
                if (offlineCore && !offlineCore.checkConnection()) {
                    e.preventDefault();
                    const form = $(this);
                    const formData = new FormData(this);
                    const data = Object.fromEntries(formData.entries());
                    
                    // Determinar tipo de datos
                    if (form.attr('id') === 'bodega-form') {
                        offlineCore.saveBodega(data)
                            .then(result => {
                                alert('Datos guardados localmente. Se sincronizarán cuando haya conexión.');
                                form[0].reset();
                            })
                            .catch(error => {
                                alert('Error al guardar localmente: ' + error.message);
                            });
                    }
                    return false;
                }
                return true;
            });
        }
    });
    JS
    );
    
    $this->endBody() ?>
</body>
</html>