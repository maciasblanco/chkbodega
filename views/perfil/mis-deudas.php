<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $atleta app\models\AtletasRegistro */
/* @var $deudasPendientes array */
/* @var $montoTotalDeuda float */
/* @var $totalSemanasDeuda int */
/* @var $estadisticas array */

$this->title = 'Mis Deudas - ' . $atleta->p_nombre . ' ' . $atleta->p_apellido;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="deudas-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h3 class="panel-title">Resumen de Deuda</h3>
                </div>
                <div class="panel-body text-center">
                    <h2 style="margin: 0; color: #d9534f;">$<?= number_format($montoTotalDeuda, 2) ?></h2>
                    <p>Total adeudado</p>
                    <p><?= $totalSemanasDeuda ?> semana(s) pendiente(s)</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Estadísticas</h3>
                </div>
                <div class="panel-body">
                    <p><strong>Aportes Pagados:</strong> <?= $estadisticas['aportes_pagados'] ?></p>
                    <p><strong>Monto Total Pagado:</strong> $<?= number_format($estadisticas['monto_total_pagado'], 2) ?></p>
                    <p><strong>Semanas Equivalentes:</strong> <?= number_format($estadisticas['semanas_equivalentes'], 1) ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title">Información del Atleta</h3>
                </div>
                <div class="panel-body">
                    <p><strong>Nombre:</strong> <?= $atleta->p_nombre . ' ' . $atleta->p_apellido ?></p>
                    <p><strong>Categoría:</strong> <?= $atleta->getCategoriaNombre() ?></p>
                    <p><strong>Escuela:</strong> <?= $atleta->escuela ? $atleta->escuela->nombre : 'No asignada' ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Detalle de Semanas Pendientes</h3>
        </div>
        <div class="panel-body">
            <?php if (!empty($deudasPendientes)): ?>
                <?= GridView::widget([
                    'dataProvider' => new \yii\data\ArrayDataProvider([
                        'allModels' => $deudasPendientes,
                        'pagination' => false,
                    ]),
                    'columns' => [
                        [
                            'attribute' => 'fecha_viernes',
                            'label' => 'Semana del',
                            'format' => 'date',
                        ],
                        [
                            'attribute' => 'numero_semana',
                            'label' => 'N° Semana',
                        ],
                        [
                            'attribute' => 'monto',
                            'label' => 'Monto ($)',
                            'format' => 'currency',
                        ],
                        [
                            'attribute' => 'tipo_aporte',
                            'label' => 'Tipo',
                            'value' => function($model) {
                                return $model->getTipoAporteLabel();
                            },
                        ],
                        [
                            'label' => 'Días de Retraso',
                            'value' => function($model) {
                                $fechaViernes = new DateTime($model->fecha_viernes);
                                $hoy = new DateTime();
                                $diferencia = $hoy->diff($fechaViernes);
                                return $diferencia->days;
                            },
                        ],
                    ],
                ]); ?>
            <?php else: ?>
                <div class="alert alert-success">
                    <strong>¡Excelente!</strong> No tienes deudas pendientes.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="text-center">
        <?= Html::a('Volver a Mi Información', ['mi-informacion', 'id' => $atleta->id], ['class' => 'btn btn-default']) ?>
        
        <?php if (Yii::$app->user->can('representante')): ?>
            <?= Html::a('Volver a Mis Representados', ['mis-representados'], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
    </div>

</div>