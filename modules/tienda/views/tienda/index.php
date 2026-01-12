<?php
// modules/tienda/views/tienda/index.php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Mis Tiendas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tienda-index">
    <div class="row">
        <div class="col-md-8">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="col-md-4 text-right">
            <?= Html::a('<i class="fas fa-plus"></i> Crear Tienda', ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            
            [
                'attribute' => 'nombre',
                'format' => 'html',
                'value' => function ($model) {
                    return Html::a(Html::encode($model->nombre), ['view', 'id' => $model->id]);
                },
            ],
            
            [
                'attribute' => 'descripcion',
                'value' => function ($model) {
                    return \yii\helpers\StringHelper::truncateWords($model->descripcion, 10);
                },
            ],
            
            [
                'attribute' => 'activo',
                'format' => 'html',
                'value' => function ($model) {
                    return $model->activo ? 
                        '<span class="badge badge-success">Activa</span>' : 
                        '<span class="badge badge-danger">Inactiva</span>';
                },
                'contentOptions' => ['class' => 'text-center'],
            ],
            
            [
                'attribute' => 'fecha_creacion',
                'format' => 'datetime',
                'contentOptions' => ['class' => 'text-center'],
            ],
            
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                            'title' => 'Ver',
                            'class' => 'btn btn-sm btn-info',
                        ]);
                    },
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                            'title' => 'Editar',
                            'class' => 'btn btn-sm btn-primary',
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                            'title' => 'Eliminar',
                            'class' => 'btn btn-sm btn-danger',
                            'data' => [
                                'confirm' => '¿Estás seguro de eliminar esta tienda?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
                'contentOptions' => ['class' => 'text-center', 'style' => 'width: 120px;'],
            ],
        ],
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
    ]); ?>
    <?php Pjax::end(); ?>
</div>