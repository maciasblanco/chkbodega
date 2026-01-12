<?php
// modules/tienda/views/producto/create.php
use yii\helpers\Html;

$this->title = 'Crear Producto';
$this->params['breadcrumbs'][] = ['label' => 'Mis Productos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Si estamos creando un producto para una tienda específica
if (isset($tienda)) {
    $this->params['breadcrumbs'][] = ['label' => $tienda->nombre, 'url' => ['tienda/view', 'id' => $tienda->id]];
}
?>
<div class="producto-create">
    <div class="row">
        <div class="col-md-12">
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="text-muted">Completa la información para crear un nuevo producto.</p>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'tiendas' => $tiendas,
        'categorias' => $categorias,
        'tienda' => $tienda ?? null,
    ]) ?>
</div>