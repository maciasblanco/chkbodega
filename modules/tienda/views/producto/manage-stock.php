<?php
// modules/tienda/views/producto/manage-stock.php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Gestionar Stock: ' . $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Mis Productos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nombre, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Gestionar Stock';
?>
<div class="producto-manage-stock">
    <div class="row">
        <div class="col-md-8">
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="text-muted">Actualiza el stock de tu producto.</p>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Stock Actual</h5>
                </div>
                <div class="card-body text-center">
                    <h2 class="display-4"><?= $model->stock ?></h2>
                    <p class="text-muted">unidades disponibles</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Ajustar Stock</h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>
                    
                    <div class="form-group">
                        <label>Operación</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="operation" id="add" value="add" checked>
                                <label class="form-check-label" for="add">Agregar Stock</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="operation" id="subtract" value="subtract">
                                <label class="form-check-label" for="subtract">Restar Stock</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Cantidad</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                        <small class="form-text text-muted">Ingresa la cantidad a agregar o restar</small>
                    </div>
                    
                    <div class="form-group">
                        <?= Html::submitButton('<i class="fas fa-save"></i> Actualizar Stock', ['class' => 'btn btn-primary btn-block']) ?>
                    </div>
                    
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <?= Html::a('<i class="fas fa-arrow-left"></i> Volver al Producto', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>
</div>