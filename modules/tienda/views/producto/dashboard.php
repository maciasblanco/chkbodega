<?php
// modules/tienda/views/tienda/dashboard.php
use yii\helpers\Html;

$this->title = 'Dashboard: ' . $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Mis Tiendas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nombre, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Dashboard';
?>
<div class="tienda-dashboard">
    <div class="row">
        <div class="col-md-12">
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="text-muted">Resumen y estadísticas de tu tienda</p>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Tarjetas de Estadísticas -->
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= $stats['totalProductos'] ?></h4>
                            <div>Total Productos</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-boxes fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= $stats['productosActivos'] ?></h4>
                            <div>Productos Activos</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= $stats['productosInactivos'] ?></h4>
                            <div>Productos Inactivos</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-pause-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= $stats['productosSinStock'] ?></h4>
                            <div>Sin Stock</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Productos Recientes</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($productosRecientes)): ?>
                        <div class="list-group">
                            <?php foreach ($productosRecientes as $producto): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= Html::encode($producto->nombre) ?></h6>
                                        <small><?= Yii::$app->formatter->asCurrency($producto->precio) ?></small>
                                    </div>
                                    <p class="mb-1 small text-muted">
                                        <?= \yii\helpers\StringHelper::truncateWords($producto->descripcion, 15) ?>
                                    </p>
                                    <div class="d-flex justify-content-between">
                                        <small>Stock: <strong><?= $producto->stock ?></strong></small>
                                        <small>
                                            Estado: 
                                            <?= $producto->activo ? 
                                                '<span class="text-success">Activo</span>' : 
                                                '<span class="text-danger">Inactivo</span>' ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay productos en esta tienda.</p>
                        <?= Html::a(
                            '<i class="fas fa-plus"></i> Crear Primer Producto', 
                            ['producto/create', 'tienda_id' => $model->id], 
                            ['class' => 'btn btn-success btn-sm']
                        ) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?= Html::a(
                            '<i class="fas fa-plus"></i> Agregar Producto', 
                            ['producto/create', 'tienda_id' => $model->id], 
                            ['class' => 'btn btn-success btn-block mb-2']
                        ) ?>
                        
                        <?= Html::a(
                            '<i class="fas fa-boxes"></i> Gestionar Productos', 
                            ['producto/index', 'tienda_id' => $model->id], 
                            ['class' => 'btn btn-primary btn-block mb-2']
                        ) ?>
                        
                        <?= Html::a(
                            '<i class="fas fa-edit"></i> Editar Tienda', 
                            ['update', 'id' => $model->id], 
                            ['class' => 'btn btn-warning btn-block mb-2']
                        ) ?>
                        
                        <?= Html::a(
                            '<i class="fas fa-store"></i> Ver Tienda Pública', 
                            ['marketplace/tienda', 'slug' => $model->slug], 
                            [
                                'class' => 'btn btn-info btn-block mb-2',
                                'target' => '_blank'
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>