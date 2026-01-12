<?php
// modules/tienda/views/producto/update.php
use yii\helpers\Html;

$this->title = 'Actualizar Producto: ' . $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Mis Productos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nombre, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="producto-update">
    <div class="row">
        <div class="col-md-12">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'tiendas' => $tiendas,
        'categorias' => $categorias,
    ]) ?>
</div>