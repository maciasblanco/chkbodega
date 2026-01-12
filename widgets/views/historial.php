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
                        'header' => 'Fecha Tasa'
                    ],
                    
                    [
                        'attribute' => 'tasa',
                        'value' => function($model) {
                            return 'Bs. ' . number_format($model->tasa, 2);
                        },
                        'contentOptions' => ['class' => 'fw-bold text-success'],
                        'header' => 'Tasa'
                    ],
                    
                    [
                        'attribute' => 'fecha_actualizacion',
                        'format' => 'datetime',
                        'header' => 'Actualizado'
                    ],
                    
                    [
                        'attribute' => 'u_creacion',
                        'label' => 'Usuario',
                        'header' => 'Usuario'
                    ]
                ],
                'pager' => [
                    'options' => ['class' => 'pagination justify-content-center'],
                    'linkOptions' => ['class' => 'page-link'],
                    'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link'],
                ],
                'tableOptions' => ['class' => 'table table-striped table-bordered'],
            ]); ?>

            <?php Pjax::end(); ?>
        </div>
    </div>
</div>