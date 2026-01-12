<?php
// modules/tienda/views/producto/index.php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Mis Productos';
$this->params['breadcrumbs'][] = $this->title;

// Si estamos viendo productos de una tienda específica
if (isset($tienda)) {
    $this->params['breadcrumbs'][] = ['label' => $tienda->nombre, 'url' => ['tienda/view', 'id' => $tienda->id]];
}
?>
<div class="producto-index">
    <div class="row">
        <div class="col-md-8">
            <h1><?= Html::encode($this->title) ?></h1>
            <?php if (isset($tienda)): ?>
                <p class="text-muted">Productos de la tienda: <strong><?= Html::encode($tienda->nombre) ?></strong></p>
            <?php endif; ?>
        </div>
        <div class="col-md-4 text-right">
            <?= Html::a('<i class="fas fa-plus"></i> Crear Producto', 
                isset($tienda) ? ['create', 'tienda_id' => $tienda->id] : ['create'], 
                ['class' => 'btn btn-success']
            ) ?>
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
                'attribute' => 'precio',
                'format' => 'currency',
                'contentOptions' => ['class' => 'text-right'],
            ],

            [
                'attribute' => 'stock',
                'contentOptions' => function ($model) {
                    return [
                        'class' => 'text-center ' . ($model->stock == 0 ? 'text-danger font-weight-bold' : ''),
                    ];
                },
            ],

            [
                'attribute' => 'activo',
                'format' => 'html',
                'value' => function ($model) {
                    return $model->activo ? 
                        '<span class="badge badge-success">Activo</span>' : 
                        '<span class="badge badge-danger">Inactivo</span>';
                },
                'contentOptions' => ['class' => 'text-center'],
            ],

            [
                'attribute' => 'destacado',
                'format' => 'html',
                'value' => function ($model) {
                    return $model->destacado ? 
                        '<span class="badge badge-warning"><i class="fas fa-star"></i> Destacado</span>' : '';
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
                'template' => '{view} {update} {manage-stock} {toggle-status} {delete}',
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
                    'manage-stock' => function ($url, $model) {
                        return Html::a('<i class="fas fa-boxes"></i>', $url, [
                            'title' => 'Gestionar Stock',
                            'class' => 'btn btn-sm btn-secondary',
                        ]);
                    },
                    'toggle-status' => function ($url, $model) {
                        $icon = $model->activo ? 'pause' : 'play';
                        $class = $model->activo ? 'warning' : 'success';
                        return Html::a("<i class='fas fa-$icon'></i>", $url, [
                            'title' => $model->activo ? 'Desactivar' : 'Activar',
                            'class' => "btn btn-sm btn-$class",
                            'data-method' => 'post',
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                            'title' => 'Eliminar',
                            'class' => 'btn btn-sm btn-danger',
                            'data' => [
                                'confirm' => '¿Estás seguro de eliminar este producto?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
                'contentOptions' => ['class' => 'text-center', 'style' => 'width: 200px;'],
            ],
        ],
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
    ]); ?>
    <?php Pjax::end(); ?>
</div>