<?php
// views/tasa-dolar/probar_fuentes.php

use yii\helpers\Html;

$this->title = 'Probar Fuentes de Tasa del Dólar';
$this->params['breadcrumbs'][] = ['label' => 'Tasa del Dólar', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tasa-dolar-probar-fuentes">
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-vial"></i> <?= $this->title ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($resultados as $fuente => $tasa): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card <?= $tasa > 100 ? 'border-success' : 'border-danger' ?>">
                            <div class="card-body text-center">
                                <h6 class="card-title"><?= $fuente ?></h6>
                                <p class="card-text">
                                    <span class="badge bg-<?= $tasa > 100 ? 'success' : 'danger' ?> fs-6">
                                        Bs. <?= number_format($tasa, 2) ?>
                                    </span>
                                </p>
                                <small class="text-muted">
                                    <?= $tasa > 100 ? '✅ Fuente funcionando correctamente' : '❌ Fuente no disponible' ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver al Panel Principal', ['index'], [
                    'class' => 'btn btn-primary'
                ]) ?>
                
                <?= Html::a('<i class="fas fa-sync"></i> Probar Nuevamente', ['probar-fuentes'], [
                    'class' => 'btn btn-warning'
                ]) ?>
            </div>
        </div>
    </div>
</div>