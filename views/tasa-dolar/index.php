<?php
// views/tasa-dolar/index.php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Configurar Tasa del Dólar';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tasa-dolar-index">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-dollar-sign"></i> Tasa del Dólar Actual</h4>
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

                    <form method="post" action="<?= Url::to(['actualizar']) ?>">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        
                        <div class="form-group">
                            <label for="tasa-dolar">Tasa del Dólar (Bs. por $1)</label>
                            <input type="number" 
                                   step="0.01" 
                                   min="0.01" 
                                   class="form-control form-control-lg" 
                                   id="tasa-dolar" 
                                   name="tasa_dolar" 
                                   value="<?= $tasaActual ?>" 
                                   placeholder="Ej: 35.50"
                                   required>
                            <small class="form-text text-muted">
                                Ingrese la tasa actual del dólar en bolívares.
                            </small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('¿Está seguro de actualizar la tasa del dólar?')">
                                <i class="fas fa-save"></i> Guardar Tasa
                            </button>
                            <?= Html::a('<i class="fas fa-history"></i> Ver Historial Completo', 
                                ['historial'], 
                                ['class' => 'btn btn-info btn-lg']) ?>
                            
                            <?= Html::a('<i class="fas fa-sync"></i> Actualizar Automático', 
                                ['actualizar-automatico'], 
                                [
                                    'class' => 'btn btn-warning btn-lg',
                                    'data' => [
                                        'confirm' => '¿Está seguro de actualizar la tasa automáticamente desde el BCV?',
                                        'method' => 'post',
                                    ]
                                ]) ?>
                        </div>
                    </form>

                    <?php if ($tasaActual > 0): ?>
                    <div class="alert alert-info mt-3">
                        <h5><i class="fas fa-info-circle"></i> Información Actual</h5>
                        <p><strong>Tasa actual:</strong> Bs. <?= number_format($tasaActual, 2) ?> por $1</p>
                        <p><strong>Fecha vigente:</strong> <?= date('d/m/Y') ?></p>
                        <p><strong>Aporte semanal:</strong> 
                            $<?= number_format(\app\models\TasaDolar::MONTO_SEMANAL, 2) ?> = 
                            Bs. <?= number_format($tasaActual * \app\models\TasaDolar::MONTO_SEMANAL, 2) ?>
                        </p>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        No se ha configurado la tasa del dólar. Por favor, ingrese el valor actual.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <!-- Historial de Tasas -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="fas fa-history"></i> Historial Reciente</h4>
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
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial as $registro): ?>
                                    <tr>
                                        <td><?= Yii::$app->formatter->asDate($registro->fecha_tasa, 'dd/MM/yyyy') ?></td>
                                        <td class="font-weight-bold text-success">Bs. <?= number_format($registro->tasa_dia, 2) ?></td>
                                        <td><small class="text-muted"><?= Yii::$app->formatter->asRelativeTime($registro->d_creacion) ?></small></td>
                                        <td><small class="text-muted"><?= Html::encode($registro->u_creacion) ?></small></td>
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
            
            <!-- Calculadora -->
            <div class="card mt-3">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="fas fa-calculator"></i> Calculadora Rápida</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Monto en Dólares ($)</label>
                        <input type="number" step="0.01" class="form-control" id="monto-dolares" value="1.00">
                    </div>
                    
                    <div class="form-group">
                        <label>Tasa (Bs. por $1)</label>
                        <input type="number" step="0.01" class="form-control" id="tasa-calculadora" value="<?= $tasaActual ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Total en Bolívares (Bs.)</label>
                        <input type="text" class="form-control font-weight-bold text-success" id="total-bolivares" readonly>
                    </div>
                    
                    <button type="button" class="btn btn-outline-primary" onclick="calcularConversion()">
                        <i class="fas fa-calculator"></i> Calcular
                    </button>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card mt-3">
                <div class="card-header bg-secondary text-white">
                    <h4 class="mb-0"><i class="fas fa-rocket"></i> Acciones Rápidas</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?= Html::a('<i class="fas fa-vial"></i> Probar Fuentes de Tasa', ['probar-fuentes'], [
                            'class' => 'btn btn-outline-info'
                        ]) ?>
                        
                        <?= Html::a('<i class="fas fa-download"></i> Exportar Historial', ['exportar-historial'], [
                            'class' => 'btn btn-outline-success'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calcularConversion() {
    var montoDolares = parseFloat(document.getElementById('monto-dolares').value) || 0;
    var tasa = parseFloat(document.getElementById('tasa-calculadora').value) || 0;
    var total = montoDolares * tasa;
    
    document.getElementById('total-bolivares').value = 'Bs. ' + total.toLocaleString('es-VE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

document.addEventListener('DOMContentLoaded', function() {
    calcularConversion();
    document.getElementById('monto-dolares').addEventListener('input', calcularConversion);
    document.getElementById('tasa-calculadora').addEventListener('input', calcularConversion);
});
</script>