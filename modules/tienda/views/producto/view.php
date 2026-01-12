<?php
// modules/tienda/views/producto/view.php
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Mis Productos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="producto-view">
    <div class="row">
        <div class="col-md-8">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="col-md-4 text-right">
            <?= Html::a('<i class="fas fa-edit"></i> Actualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fas fa-boxes"></i> Gestionar Stock', ['manage-stock', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
            <?= Html::a('<i class="fas fa-trash"></i> Eliminar', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '¿Estás seguro de eliminar este producto?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Información del Producto</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'nombre',
                            'descripcion:ntext',
                            [
                                'attribute' => 'precio',
                                'format' => 'currency',
                            ],
                            [
                                'attribute' => 'stock',
                            ],
                            [
                                'attribute' => 'activo',
                                'value' => $model->activo ? 'Sí' : 'No',
                            ],
                            [
                                'attribute' => 'destacado',
                                'value' => $model->destacado ? 'Sí' : 'No',
                            ],
                            [
                                'label' => 'Tienda',
                                'value' => $model->tienda->nombre,
                            ],
                            [
                                'label' => 'Categoría',
                                'value' => $model->categoria->nombre,
                            ],
                            'fecha_creacion:datetime',
                            'fecha_actualizacion:datetime',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?= Html::a(
                            $model->activo ? 
                                '<i class="fas fa-pause"></i> Desactivar Producto' : 
                                '<i class="fas fa-play"></i> Activar Producto',
                            ['toggle-status', 'id' => $model->id],
                            [
                                'class' => 'list-group-item list-group-item-action ' . ($model->activo ? 'list-group-item-warning' : 'list-group-item-success'),
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => $model->activo ? 
                                        '¿Estás seguro de desactivar este producto?' : 
                                        '¿Estás seguro de activar este producto?',
                                ],
                            ]
                        ) ?>
                        
                        <?= Html::a(
                            $model->destacado ? 
                                '<i class="fas fa-star"></i> Quitar de Destacados' : 
                                '<i class="fas fa-star"></i> Destacar Producto',
                            ['toggle-featured', 'id' => $model->id],
                            [
                                'class' => 'list-group-item list-group-item-action ' . ($model->destacado ? 'list-group-item-warning' : 'list-group-item-info'),
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => $model->destacado ? 
                                        '¿Estás seguro de quitar el producto de destacados?' : 
                                        '¿Estás seguro de destacar este producto?',
                                ],
                            ]
                        ) ?>
                        
                        <?= Html::a(
                            '<i class="fas fa-store"></i> Ver en Tienda',
                            ['marketplace/producto', 'id' => $model->id],
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
</div>