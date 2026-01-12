<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $representante app\models\RegistroRepresentantes */
/* @var $atletas app\models\AtletasRegistro[] */

$this->title = 'Mis Atletas Representados';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="representados-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Información del Representante</h3>
        </div>
        <div class="panel-body">
            <p><strong>Nombre:</strong> <?= $representante->p_nombre . ' ' . $representante->p_apellido ?></p>
            <p><strong>Identificación:</strong> <?= $representante->identificacion ?></p>
            <p><strong>Teléfono:</strong> <?= $representante->cell ?></p>
        </div>
    </div>

    <?php if (!empty($atletas)): ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Lista de Atletas Representados</h3>
            </div>
            <div class="panel-body">
                <?= GridView::widget([
                    'dataProvider' => new \yii\data\ArrayDataProvider([
                        'allModels' => $atletas,
                        'pagination' => false,
                    ]),
                    'columns' => [
                        [
                            'attribute' => 'id',
                            'label' => 'ID',
                        ],
                        [
                            'label' => 'Nombre Completo',
                            'value' => function($model) {
                                return $model->p_nombre . ' ' . $model->p_apellido;
                            },
                        ],
                        [
                            'label' => 'Identificación',
                            'attribute' => 'identificacion',
                        ],
                        [
                            'label' => 'Edad',
                            'value' => function($model) {
                                return $model->getEdad() . ' años';
                            },
                        ],
                        [
                            'label' => 'Categoría',
                            'value' => function($model) {
                                return $model->getCategoriaNombre();
                            },
                        ],
                        [
                            'label' => 'Deuda Actual',
                            'value' => function($model) {
                                $deuda = \app\models\AportesSemanales::calcularMontoDeuda($model->id);
                                return '$' . number_format($deuda, 2);
                            },
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view} {deudas}',
                            'buttons' => [
                                'view' => function($url, $model, $key) {
                                    return Html::a(
                                        '<span class="glyphicon glyphicon-eye-open"></span>',
                                        ['mi-informacion', 'id' => $model->id],
                                        [
                                            'title' => 'Ver Información',
                                            'data-pjax' => '0',
                                        ]
                                    );
                                },
                                'deudas' => function($url, $model, $key) {
                                    return Html::a(
                                        '<span class="glyphicon glyphicon-usd"></span>',
                                        ['mis-deudas', 'id' => $model->id],
                                        [
                                            'title' => 'Ver Deudas',
                                            'data-pjax' => '0',
                                        ]
                                    );
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <strong>No tiene atletas representados actualmente.</strong>
        </div>
    <?php endif; ?>

</div>