<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\CambioPasswordForm */

$this->title = 'Cambio de Contraseña Obligatorio';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-cambio-password">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
                </div>
                <div class="panel-body">
                    <div class="alert alert-warning">
                        <strong>¡Atención!</strong> Por seguridad, debe cambiar su contraseña temporal antes de continuar.
                    </div>

                    <p>Por su seguridad, establezca una nueva contraseña segura.</p>

                    <ul>
                        <?php foreach ($model->getPasswordRequirements() as $requirement): ?>
                            <li><?= $requirement ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <?php $form = ActiveForm::begin(['id' => 'cambio-password-form']); ?>

                    <?= $form->field($model, 'currentPassword')->passwordInput([
                        'autofocus' => true,
                        'placeholder' => 'Ingrese su contraseña temporal: 12345-aves'
                    ]) ?>

                    <?= $form->field($model, 'newPassword')->passwordInput([
                        'placeholder' => 'Ingrese su nueva contraseña'
                    ]) ?>

                    <?= $form->field($model, 'confirmPassword')->passwordInput([
                        'placeholder' => 'Repita la nueva contraseña'
                    ]) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Cambiar Contraseña', [
                            'class' => 'btn btn-warning btn-block btn-lg',
                            'name' => 'cambio-password-button'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>