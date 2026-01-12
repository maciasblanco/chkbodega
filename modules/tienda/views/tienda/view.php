<?php
// modules/tienda/views/tienda/view.php
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

$this->title = $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Mis Tiendas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tienda-view">
    <div class="row">
        <div class="col-md-8">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="col-md-4 text-right">
            <?= Html::a('<i class="fas fa-edit"></i> Actualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fas fa-tachometer-alt"></i> Dashboard', ['dashboard', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
            <?= Html::a('<i class="fas fa-plus"></i> Agregar Producto', ['producto/create', 'tienda_id' => $model->id], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Información de la Tienda</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'nombre',
                            'descripcion:ntext',
                            [
                                'attribute' => 'activo',
                                'value' => $model->activo ? 'Sí' : 'No',
                            ],
                            'telefono',
                            'email:email',
                            'direccion',
                            'ciudad',
                            'estado',
                            'codigo_postal',
                            'fecha_creacion:datetime',
                            'fecha_actualizacion:datetime',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?= Html::a(
                            '<i class="fas fa-boxes"></i> Gestionar Productos',
                            ['producto/index', 'tienda_id' => $model->id],
                            ['class' => 'list-group-item list-group-item-action']
                        ) ?>
                        
                        <?= Html::a(
                            $model->activo ? 
                                '<i class="fas fa-pause"></i> Desactivar Tienda' : 
                                '<i class="fas fa-play"></i> Activar Tienda',
                            ['toggle-status', 'id' => $model->id],
                            [
                                'class' => 'list-group-item list-group-item-action ' . ($model->activo ? 'list-group-item-warning' : 'list-group-item-success'),
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => $model->activo ? 
                                        '¿Estás seguro de desactivar esta tienda?' : 
                                        '¿Estás seguro de activar esta tienda?',
                                ],
                            ]
                        ) ?>
                        
                        <?= Html::a(
                            '<i class="fas fa-store"></i> Ver Tienda Pública',
                            ['marketplace/tienda', 'slug' => $model->slug],
                            [
                                'class' => 'list-group-item list-group-item-action',
                                'target' => '_blank'
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Productos de la Tienda</h5>
                </div>
                <div class="card-body">
                    <?= GridView::widget([
                        'dataProvider' => $productosDataProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            
                            'nombre',
                            
                            [
                                'attribute' => 'precio',
                                'format' => 'currency',
                                'contentOptions' => ['class' => 'text-right'],
                            ],
                            
                            [
                                'attribute' => 'stock',
                                'contentOptions' => ['class' => 'text-center'],
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
                                'class' => 'yii\grid\ActionColumn',
                                'controller' => 'producto',
                                'template' => '{view} {update}',
                                'buttons' => [
                                    'view' => function ($url, $model) {
                                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                                            'title' => 'Ver Producto',
                                            'class' => 'btn btn-sm btn-info',
                                        ]);
                                    },
                                    'update' => function ($url, $model) {
                                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                                            'title' => 'Editar Producto',
                                            'class' => 'btn btn-sm btn-primary',
                                        ]);
                                    },
                                ],
                                'contentOptions' => ['class' => 'text-center', 'style' => 'width: 80px;'],
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>