<?php
// modules/tienda/views/tienda/_form.php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\CategoriaTienda;

$form = ActiveForm::begin([
    'options' => ['enctype' => 'multipart/form-data']
]); ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Información Básica</h5>
            </div>
            <div class="card-body">
                <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
                
                <?= $form->field($model, 'descripcion')->textarea(['rows' => 4]) ?>
                
                <?= $form->field($model, 'telefono')->textInput(['maxlength' => true]) ?>
                
                <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title">Ubicación</h5>
            </div>
            <div class="card-body">
                <?= $form->field($model, 'direccion')->textInput(['maxlength' => true]) ?>
                
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, 'ciudad')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'estado')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'codigo_postal')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Configuración</h5>
            </div>
            <div class="card-body">
                <?= $form->field($model, 'activo')->checkbox() ?>
                
                <?php if (isset($vendedor) && $vendedor): ?>
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle"></i>
                            Esta tienda será asociada a tu perfil de vendedor: <strong><?= Html::encode($vendedor->nombre_completo) ?></strong>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title">Acciones</h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <?= Html::submitButton(
                        $model->isNewRecord ? 
                            '<i class="fas fa-save"></i> Crear Tienda' : 
                            '<i class="fas fa-save"></i> Actualizar Tienda',
                        ['class' => $model->isNewRecord ? 'btn btn-success btn-block' : 'btn btn-primary btn-block']
                    ) ?>
                </div>
                
                <div class="form-group">
                    <?= Html::a(
                        '<i class="fas fa-arrow-left"></i> Cancelar', 
                        ['index'], 
                        ['class' => 'btn btn-secondary btn-block']
                    ) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>