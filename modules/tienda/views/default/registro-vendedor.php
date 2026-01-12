<?php
// modules/tienda/views/default/registro-vendedor.php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Registro de Vendedor - Planealo';
$this->params['breadcrumbs'][] = ['label' => 'Tienda', 'url' => ['/tienda']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tienda-default-registro-vendedor">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center py-4">
                        <h2 class="mb-0">
                            <i class="fas fa-store me-2"></i>
                            Registro de Vendedor
                        </h2>
                        <p class="mb-0 mt-2">Únete a nuestra comunidad de emprendedores deportivos</p>
                    </div>
                    <div class="card-body p-5">
                        <!-- Información del Usuario Actual -->
                        <div class="alert alert-info">
                            <h5><i class="fas fa-user me-2"></i> Información del Usuario</h5>
                            <p class="mb-1"><strong>Nombre:</strong> <?= Yii::$app->user->identity->username ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?= Yii::$app->user->identity->email ?></p>
                            <p class="mb-0"><strong>Estado:</strong> <span class="badge bg-success">Usuario Verificado</span></p>
                        </div>

                        <!-- Formulario de Registro (simplificado por ahora) -->
                        <div class="registration-steps">
                            <div class="step active">
                                <div class="step-header">
                                    <span class="step-number">1</span>
                                    <h5 class="step-title">Información Básica</h5>
                                </div>
                                <div class="step-content">
                                    <div class="mb-3">
                                        <label class="form-label">Nombre de la Tienda *</label>
                                        <input type="text" class="form-control" placeholder="Ej: Deportes XYZ">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Descripción de la Tienda</label>
                                        <textarea class="form-control" rows="3" placeholder="Describe los productos o servicios que ofrecerás..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Categorías Principales</label>
                                        <select class="form-select" multiple>
                                            <option value="1">Equipamiento Deportivo</option>
                                            <option value="2">Nutrición</option>
                                            <option value="3">Ropa Deportiva</option>
                                            <option value="4">Servicios de Entrenamiento</option>
                                            <option value="5">Accesorios</option>
                                        </select>
                                        <div class="form-text">Selecciona las categorías que mejor describan tus productos</div>
                                    </div>
                                </div>
                            </div>

                            <div class="step">
                                <div class="step-header">
                                    <span class="step-number">2</span>
                                    <h5 class="step-title">Información de Contacto</h5>
                                </div>
                                <div class="step-content">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Teléfono de Contacto *</label>
                                            <input type="tel" class="form-control" placeholder="+58 412-XXX-XXXX">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">WhatsApp</label>
                                            <input type="tel" class="form-control" placeholder="Opcional">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Dirección</label>
                                        <textarea class="form-control" rows="2" placeholder="Dirección para envíos o contacto..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="step">
                                <div class="step-header">
                                    <span class="step-number">3</span>
                                    <h5 class="step-title">Términos y Condiciones</h5>
                                </div>
                                <div class="step-content">
                                    <div class="alert alert-warning">
                                        <h6><i class="fas fa-exclamation-triangle me-2"></i> Términos del Servicio</h6>
                                        <p>Al registrarte como vendedor aceptas:</p>
                                        <ul>
                                            <li>Proporcionar información veraz y actualizada</li>
                                            <li>Mantener estándares de calidad en productos/servicios</li>
                                            <li>Responsabilizarte por la gestión de envíos y entregas</li>
                                            <li>Cumplir con las políticas de devolución</li>
                                            <li>Pagar la comisión del 5% sobre las ventas realizadas</li>
                                        </ul>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="acceptTerms">
                                        <label class="form-check-label" for="acceptTerms">
                                            Acepto los términos y condiciones del servicio
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <a href="<?= Url::to(['/tienda']) ?>" class="btn btn-secondary btn-lg w-100">
                                    <i class="fas fa-arrow-left me-2"></i> Cancelar
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-success btn-lg w-100" id="btn-register-vendor">
                                    <i class="fas fa-check me-2"></i> Completar Registro
                                </button>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="alert alert-light mt-4">
                            <h6><i class="fas fa-info-circle me-2 text-primary"></i> ¿Qué pasa después del registro?</h6>
                            <p class="mb-2">Una vez completado el registro:</p>
                            <ul class="mb-0">
                                <li>Recibirás acceso al dashboard de vendedor</li>
                                <li>Podrás comenzar a agregar productos inmediatamente</li>
                                <li>Tus productos estarán visibles en el marketplace</li>
                                <li>Recibirás notificaciones de ventas y consultas</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.step {
    border-left: 4px solid #dee2e6;
    padding-left: 20px;
    margin-bottom: 30px;
    position: relative;
}

.step.active {
    border-left-color: #28a745;
}

.step-header {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.step-number {
    background: #dee2e6;
    color: #6c757d;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 15px;
}

.step.active .step-number {
    background: #28a745;
    color: white;
}

.step-title {
    margin: 0;
    color: #6c757d;
}

.step.active .step-title {
    color: #28a745;
}

.step-content {
    padding-left: 45px;
}
</style>

<?php
// JavaScript simple para el registro
$this->registerJs(<<<JS
    $('#btn-register-vendor').on('click', function() {
        // Por ahora solo redirigimos al dashboard
        // En el futuro procesaremos el formulario
        window.location.href = '/tienda/default/dashboard-vendedor';
    });
JS);