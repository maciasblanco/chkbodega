<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\AtletasRegistro */

$this->title = 'Mi Información Personal';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="perfil-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Información Personal</h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'p_nombre',
                            's_nombre',
                            'p_apellido',
                            's_apellido',
                            'identificacion',
                            [
                                'attribute' => 'fn',
                                'format' => 'date',
                                'label' => 'Fecha de Nacimiento',
                            ],
                            [
                                'attribute' => 'edad',
                                'value' => $model->getEdad() . ' años',
                            ],
                            [
                                'attribute' => 'sexo',
                                'value' => function($model) {
                                    return $model->sexo == 1 ? 'Masculino' : 'Femenino';
                                },
                            ],
                            'cell',
                            'telf_emergencia1',
                            'telf_emergencia2',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Información Deportiva</h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'id_escuela',
                                'value' => $model->escuela ? $model->escuela->nombre : 'No asignada',
                            ],
                            [
                                'label' => 'Categoría',
                                'value' => $model->getCategoriaNombre(),
                            ],
                            'estatura',
                            'peso',
                            'talla_franela',
                            'talla_short',
                            [
                                'attribute' => 'asma',
                                'value' => $model->asma ? 'Sí' : 'No',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center">
        <?= Html::a('Ver Mis Deudas', ['mis-deudas', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        
        <?php if (Yii::$app->user->can('representante')): ?>
            <?= Html::a('Volver a Mis Representados', ['mis-representados'], ['class' => 'btn btn-default']) ?>
        <?php endif; ?>
    </div>

</div>