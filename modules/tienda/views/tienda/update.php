<?php
// modules/tienda/views/tienda/update.php
use yii\helpers\Html;

$this->title = 'Actualizar Tienda: ' . $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Mis Tiendas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nombre, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="tienda-update">
    <div class="row">
        <div class="col-md-12">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>