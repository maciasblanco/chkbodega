<?php
// views/tasa-dolar/historial.php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Historial de Tasas del Dólar';
$this->params['breadcrumbs'][] = ['label' => 'Tasa del Dólar', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tasa-dolar-historial">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history"></i> <?= $this->title ?>
                    </h5>
                </div>
                <div class="col-md-6 text-end">
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], [
                        'class' => 'btn btn-light btn-sm'
                    ]) ?>
                    
                    <?= Html::a('<i class="fas fa-download"></i> Exportar', ['exportar-historial'], [
                        'class' => 'btn btn-success btn-sm'
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php Pjax::begin(['timeout' => 10000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    
                    [
                        'attribute' => 'fecha_tasa',
                        'format' => 'date',
                        'header' => 'Fecha Tasa',
                        'contentOptions' => ['class' => 'text-center'],
                        'headerOptions' => ['class' => 'text-center']
                    ],
                    
                    [
                        'attribute' => 'tasa_dia',
                        'value' => function($model) {
                            return 'Bs. ' . number_format($model->tasa_dia, 2);
                        },
                        'contentOptions' => ['class' => 'fw-bold text-success text-center'],
                        'header' => 'Tasa',
                        'headerOptions' => ['class' => 'text-center']
                    ],
                    
                    [
                        'attribute' => 'd_creacion',
                        'format' => 'datetime',
                        'header' => 'Fecha Registro',
                        'contentOptions' => ['class' => 'text-center'],
                        'headerOptions' => ['class' => 'text-center']
                    ],
                    
                    [
                        'attribute' => 'u_creacion',
                        'label' => 'Usuario',
                        'header' => 'Usuario',
                        'contentOptions' => ['class' => 'text-center'],
                        'headerOptions' => ['class' => 'text-center']
                    ],
                    
                    [
                        'attribute' => 'dir_ip',
                        'label' => 'IP',
                        'header' => 'Dirección IP',
                        'contentOptions' => ['class' => 'text-center'],
                        'headerOptions' => ['class' => 'text-center']
                    ]
                ],
                'pager' => [
                    'options' => ['class' => 'pagination justify-content-center'],
                    'linkOptions' => ['class' => 'page-link'],
                    'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link'],
                ],
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'summary' => 'Mostrando <b>{begin}-{end}</b> de <b>{totalCount}</b> registros',
            ]); ?>

            <?php Pjax::end(); ?>

            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Información</h6>
                        <small>
                            Total de registros: <strong><?= $dataProvider->getTotalCount() ?></strong><br>
                            Mostrando los últimos registros de tasas del dólar.
                        </small>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <?= Html::a('<i class="fas fa-plus"></i> Nueva Tasa Manual', ['index'], [
                        'class' => 'btn btn-primary'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}
.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
}
</style>