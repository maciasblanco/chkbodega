<?php
// modules/tienda/views/producto/_form.php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Tienda;
use app\models\CategoriaProducto;

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
                
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'precio')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'stock')->textInput(['type' => 'number', 'min' => '0']) ?>
                    </div>
                </div>
                
                <?= $form->field($model, 'id_categoria')->dropDownList(
                    ArrayHelper::map(
                        CategoriaProducto::find()->where(['activo' => 1])->all(), 
                        'id', 
                        'nombre'
                    ),
                    ['prompt' => 'Selecciona una categoría']
                ) ?>
                
                <?= $form->field($model, 'id_tienda')->dropDownList(
                    ArrayHelper::map(
                        $tiendas, 
                        'id', 
                        'nombre'
                    ),
                    ['prompt' => 'Selecciona una tienda']
                ) ?>
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
                <?= $form->field($model, 'destacado')->checkbox() ?>
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
                            '<i class="fas fa-save"></i> Crear Producto' : 
                            '<i class="fas fa-save"></i> Actualizar Producto',
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