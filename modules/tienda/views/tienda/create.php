<?php
// modules/tienda/views/tienda/create.php
use yii\helpers\Html;

$this->title = 'Crear Tienda';
$this->params['breadcrumbs'][] = ['label' => 'Mis Tiendas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tienda-create">
    <div class="row">
        <div class="col-md-12">
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="text-muted">Completa la información para crear una nueva tienda.</p>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'vendedor' => $vendedor,
    ]) ?>
</div>