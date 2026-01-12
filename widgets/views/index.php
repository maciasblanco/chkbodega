<?php
// views/tasa-dolar/index.php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = 'Tasa del Dólar';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tasa-dolar-index">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-dollar-sign"></i> Tasa Actual del Dólar
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (Yii::$app->session->hasFlash('success')): ?>
                        <div class="alert alert-success">
                            <?= Yii::$app->session->getFlash('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger">
                            <?= Yii::$app->session->getFlash('error') ?>
                        </div>
                    <?php endif; ?>

                    <div class="text-center mb-4">
                        <h2 class="text-success display-4 fw-bold">
                            Bs. <?= number_format($tasaActual, 2) ?>
                        </h2>
                        <p class="text-muted">Tasa actual por $1 USD</p>
                    </div>

                    <?php $form = ActiveForm::begin([
                        'action' => ['actualizar'],
                        'options' => ['class' => 'mt-4']
                    ]); ?>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="form-label">Actualizar Tasa Manualmente</label>
                                <div class="input-group">
                                    <span class="input-group-text">Bs.</span>
                                    <?= Html::input('number', 'tasa_dolar', $tasaActual, [
                                        'class' => 'form-control',
                                        'step' => '0.01',
                                        'min' => '0.01',
                                        'required' => true
                                    ]) ?>
                                </div>
                                <small class="form-text text-muted">
                                    Ingrese la nueva tasa del dólar
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group pt-4">
                                <?= Html::submitButton('Actualizar', [
                                    'class' => 'btn btn-success w-100',
                                    'onclick' => 'return confirm("¿Está seguro de actualizar la tasa del dólar?")'
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history"></i> Historial Reciente
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($historial)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tasa</th>
                                        <th>Actualizado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial as $tasa): ?>
                                        <tr>
                                            <td><?= Yii::$app->formatter->asDate($tasa->fecha_tasa) ?></td>
                                            <td class="fw-bold text-success">Bs. <?= number_format($tasa->tasa, 2) ?></td>
                                            <td class="text-muted small"><?= Yii::$app->formatter->asRelativeTime($tasa->fecha_actualizacion) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <?= Html::a('Ver Historial Completo', ['historial'], [
                                'class' => 'btn btn-outline-primary btn-sm'
                            ]) ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No hay historial disponible</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-sync-alt"></i> Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?= Html::a('<i class="fas fa-sync"></i> Actualizar Automáticamente', ['#'], [
                            'class' => 'btn btn-outline-warning',
                            'onclick' => 'actualizarAutomaticamente(); return false;'
                        ]) ?>
                        
                        <?= Html::a('<i class="fas fa-vial"></i> Probar Fuentes', ['probar-fuentes'], [
                            'class' => 'btn btn-outline-info'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$urlActualizar = Url::to(['api-tasa-actual']);
$this->registerJs(<<<JS
function actualizarAutomaticamente() {
    $.ajax({
        url: '$urlActualizar',
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            // Mostrar loading
            $('body').append('<div class="loading-overlay"><div class="spinner-border text-primary"></div></div>');
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error al actualizar la tasa');
            }
        },
        error: function() {
            alert('Error de conexión');
        },
        complete: function() {
            $('.loading-overlay').remove();
        }
    });
}

// Estilos para el loading
$('head').append('<style>.loading-overlay {position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center; }</style>');
JS
);
?>