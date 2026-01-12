<?php
// views/tasa-dolar/probar_fuentes.php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Probar Fuentes de Tasa del Dólar';
$this->params['breadcrumbs'][] = ['label' => 'Tasa del Dólar', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tasa-dolar-probar-fuentes">
    <div class="card">
        <div class="card-header bg-info text-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-vial"></i> <?= $this->title ?>
                    </h5>
                </div>
                <div class="col-md-6 text-end">
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], [
                        'class' => 'btn btn-light btn-sm'
                    ]) ?>
                    
                    <?= Html::a('<i class="fas fa-sync"></i> Probar Nuevamente', ['probar-fuentes'], [
                        'class' => 'btn btn-warning btn-sm'
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle"></i> Información</h6>
                <p class="mb-0">
                    Esta herramienta prueba todas las fuentes configuradas para obtener la tasa del dólar 
                    y muestra los resultados de cada una. Útil para diagnosticar problemas de conexión.
                </p>
            </div>

            <div class="row">
                <?php foreach ($resultados as $fuente => $tasa): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 <?= $tasa > 0 ? 'border-success' : 'border-danger' ?>">
                            <div class="card-header <?= $tasa > 0 ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                                <h6 class="mb-0">
                                    <i class="fas fa-<?= $tasa > 0 ? 'check' : 'times' ?>"></i>
                                    <?= Html::encode($fuente) ?>
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                <?php if ($tasa > 0): ?>
                                    <h4 class="text-success fw-bold">
                                        Bs. <?= number_format($tasa, 2) ?>
                                    </h4>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> FUNCIONA
                                    </span>
                                <?php else: ?>
                                    <h4 class="text-danger fw-bold">
                                        Bs. 0.00
                                    </h4>
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times"></i> FALLÓ
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-light">
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> 
                                    Probado: <?= date('H:i:s') ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Resumen de Resultados -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Resumen de Resultados</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $fuentesExitosas = array_filter($resultados, function($tasa) {
                                return $tasa > 0;
                            });
                            $totalFuentes = count($resultados);
                            $totalExitosas = count($fuentesExitosas);
                            $porcentajeExito = $totalFuentes > 0 ? round(($totalExitosas / $totalFuentes) * 100, 2) : 0;
                            ?>
                            
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h3 class="text-primary mb-0"><?= $totalFuentes ?></h3>
                                        <small class="text-muted">Total Fuentes</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h3 class="text-success mb-0"><?= $totalExitosas ?></h3>
                                        <small class="text-muted">Fuentes Exitosas</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h3 class="text-danger mb-0"><?= $totalFuentes - $totalExitosas ?></h3>
                                        <small class="text-muted">Fuentes Fallidas</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h3 class="<?= $porcentajeExito >= 50 ? 'text-success' : 'text-warning' ?> mb-0">
                                            <?= $porcentajeExito ?>%
                                        </h3>
                                        <small class="text-muted">Tasa de Éxito</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Barra de progreso -->
                            <div class="mt-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Progreso de Éxito</span>
                                    <span><?= $porcentajeExito ?>%</span>
                                </div>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar 
                                        <?= $porcentajeExito >= 75 ? 'bg-success' : 
                                           ($porcentajeExito >= 50 ? 'bg-warning' : 'bg-danger') ?>" 
                                         role="progressbar" 
                                         style="width: <?= $porcentajeExito ?>%;" 
                                         aria-valuenow="<?= $porcentajeExito ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <?= $porcentajeExito ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recomendaciones -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-lightbulb"></i> Recomendaciones</h6>
                        </div>
                        <div class="card-body">
                            <?php if ($porcentajeExito >= 75): ?>
                                <div class="alert alert-success">
                                    <h6><i class="fas fa-thumbs-up"></i> Estado: Excelente</h6>
                                    <p class="mb-0">
                                        La mayoría de las fuentes están funcionando correctamente. 
                                        El sistema puede obtener la tasa del dólar de manera confiable.
                                    </p>
                                </div>
                            <?php elseif ($porcentajeExito >= 50): ?>
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Estado: Regular</h6>
                                    <p class="mb-0">
                                        Algunas fuentes están fallando. El sistema aún puede obtener la tasa, 
                                        pero podría haber problemas ocasionales.
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <h6><i class="fas fa-exclamation-circle"></i> Estado: Crítico</h6>
                                    <p class="mb-0">
                                        La mayoría de las fuentes están fallando. El sistema podría tener 
                                        dificultades para obtener la tasa del dólar automáticamente.
                                    </p>
                                </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Acciones Sugeridas:</h6>
                                    <ul>
                                        <li>Verificar conexión a internet</li>
                                        <li>Revisar configuración de firewall</li>
                                        <li>Probar en diferentes horarios</li>
                                        <li>Contactar al administrador del sistema</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Fuentes Disponibles:</h6>
                                    <ul>
                                        <li><strong>BCV Directo:</strong> Sitio web oficial del BCV</li>
                                        <li><strong>BCV Alternativo:</strong> Página alternativa del BCV</li>
                                        <li><strong>En Paralelo:</strong> Yahoo Finance</li>
                                        <li><strong>API Respaldo:</strong> ExchangeRate-API</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logs de Prueba -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fas fa-list"></i> Detalles de Prueba</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Fuente</th>
                                            <th>Estado</th>
                                            <th>Tasa Obtenida</th>
                                            <th>Evaluación</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($resultados as $fuente => $tasa): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= Html::encode($fuente) ?></strong>
                                                </td>
                                                <td>
                                                    <?php if ($tasa > 0): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check"></i> Éxito
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times"></i> Fallo
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($tasa > 0): ?>
                                                        <span class="fw-bold text-success">
                                                            Bs. <?= number_format($tasa, 2) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">No disponible</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($tasa > 0): ?>
                                                        <?php if ($tasa >= 100 && $tasa <= 1000): ?>
                                                            <span class="badge bg-success">Válida</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">Fuera de rango</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Error</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12 text-center">
                    <?= Html::a('<i class="fas fa-download"></i> Ver Logs Completos', ['#'], [
                        'class' => 'btn btn-outline-info',
                        'onclick' => 'alert("Los logs detallados se encuentran en: @runtime/logs/tasa-dolar.log"); return false;'
                    ]) ?>
                    
                    <?= Html::a('<i class="fas fa-redo"></i> Ejecutar Prueba Completa', ['probar-fuentes'], [
                        'class' => 'btn btn-primary'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.progress {
    border-radius: 10px;
}

.badge {
    font-size: 0.75em;
}
</style>