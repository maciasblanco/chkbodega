<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use app\models\Nacionalidad;
use yii\helpers\ArrayHelper;
\app\assets\DateRangePickerAsset::register($this);
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \mdm\admin\models\form\Signup */


$this->title = Yii::t('app', 'Registro Usuario');
$this->params['breadcrumbs'][] = $this->title;
$this->params['tituloVista'] = '<i class=" fas fa-user-plus"></i> ' . $this->title;
$this->params['subtituloVista'] = Yii::$app->params['app']['nombreCompleto']
?>
<div class="site-signup">
  <p>Por Favor llene todos los datos para el registro.</p>
  <?= Html::errorSummary($model)?>
  <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
      <div class="col-lg-8 col-lg-offset-2">
          <div class="panel panel-default">
              <div class="panel-heading">Datos de Acceso</div>
              <div class="panel-body">
                  <div class="row">
                    <div class="col-md-3">
                      <?= $form->field($model, 'username')->textinput(['id'=>'nombreUsuario'])->label('USUARIO') ?>
                    </div>
                    <div class="col-md-2">
                      <?= $form->field($model, 'id_nacionalidad')->dropDownList(ArrayHelper::map(Nacionalidad::find()->all(), 'id', 'letra'), ['prompt' => '', 'id'=>'nac'])->label("NACIONALIDAD") ?>
                    </div>
                    <div class="col-md-3">
                      <?= $form->field($model, 'cedula')->textinput(['id'=>'ci'])->label('CEDULA') ?>
                    </div>
                    <div class="col-md-4">
                      <label class="control-label" for="nombre">NOM. Y APE.</label>
                      <input type="text" id="nombre"class="form-control" readonly="true">
                    </div>

                  </div>

                  <div class="row">

                    <div class="col-md-4">
                      <?= $form->field($model, 'email')->textinput(['id'=>'correoUsuario'])->label('CORREO')  ?>
                    </div>

                    <div class="col-md-4">
                      <?= $form->field($model, 'password')->passwordInput(['id'=>'claveUsuario'])->label('CLAVE')  ?>
                    </div>
                    <div class="col-md-4">
                      <?= $form->field($model, 'retypePassword')->passwordInput(['id'=>'claveUsuario2'])->label('REPITA CLAVE')  ?>
                    </div>

                  </div>

                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('rbac-admin', 'Registro'), ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                    </div>

              </div>
          </div>
      </div>
                
  <?php ActiveForm::end(); ?> 
</div> 
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('#tabs').tab();
    });
    function soloNumeros(e){
        var key = window.Event ? e.which : e.keyCode
        return (key >= 45 && key <= 57)
    }
</script>
<?php
$select2Config = Yii::$app->params['select2BaseConfig'];
$currentYear = date('Y');
$currentDate = date('d-m-Y');
$urlDP = Yii::$app->urlManager->createUrl(["/datos-persona/get-by-ci-nac"]);
//$urlSB = Yii::$app->urlManager->createUrl(["/admin/user/signup-brigada"]);
$funcionCamposAlInicio = $model->isNewRecord ? 'restartFields' : 'blockFields';
$this->registerJs(
<<<JAVASCRIPT
  var restartFields = function(clear) {
        if (clear) {
            $("#primerNombre").val("");
            $("#primerApellido").val("");
            $("#segundoApellido").val("");
            $("#segundoNombre").val("");
            $("#idSexo").val(null).trigger("change");
            $("#paisNacimiento").val(null).trigger("change");
            $("#fn").val("");
        }

        $("#primerNombre").prop("readonly", false);
        $("#primerApellido").prop("readonly", false);
        $("#segundoApellido").prop("readonly", false);
        $("#segundoNombre").prop("readonly", false);
        $('#idSexo').prop("disabled", false).trigger("change");
        $('#paisNacimiento').prop("disabled", false).trigger("change");
        $('#fn').prop("readonly", false);

        moment.locale('es');

        $('#fn').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            autoApply: false,
            opens: "center",
            maxYear: "{$currentYear}",
            maxDate: "{$currentDate}",
            autoUpdateInput: false,
            locale: {
                format: "DD/MM/YYYY",
            }
        }).on('apply.daterangepicker', function(ev, picker) {
          $(this).val(picker.startDate.format('DD-MM-YYYY'));
        });
    }

    var blockFields = function() {
        $("#primerNombre").prop("readonly", true);
        $("#primerApellido").prop("readonly", true);
        $("#segundoApellido").prop("readonly", true);
        $("#segundoNombre").prop("readonly", true);
        $("#idSexo").prop("readonly", true).trigger("change");
        $("#paisNacimiento").prop("readonly", true).trigger("change");
        $("#fn").prop("readonly", true);

        if ($("#fn").data('daterangepicker')) {
            $("#fn").data('daterangepicker').remove();
        }
    }

    $("nac, #ci").change(function() {
        var ci = $("#ci").val();
        var nac = $("#nac").find("option:selected").val();
        
        if (ci == "" || nac == "") {
            restartFields(false);
            return false;
        }

        $.get("$urlDP",
            {"nac":nac, "ci":ci},
            function(data) {
                if (data != "") {
                    var datos = JSON.parse(data);

                    if ($.isEmptyObject(datos)) {
                        restartFields(true);
                    } else {
                        if (datos.encontrado==true){
                          datosEncontrados();
                        }
                        else{
                           $("#nombre").val(datos.primer_nombre +" "+ datos.primer_apellido ); 
                          /*$("#primerNombre").val(datos.primer_nombre);
                          $("#primerApellido").val(datos.primer_apellido);
                          $("#segundoApellido").val(datos.segundo_apellido);
                          $("#segundoNombre").val(datos.segundo_nombre);
                          $("#idSexo").prop("readonly", true).find("option[value=\"" + datos.id_sexo + "\"]").prop("selected", true);
                          $("#fn").val(datos.fecha_nac);
                          $("#paisNacimiento").prop("readonly", true).find("option[value=\"" + datos.nacOri+ "\"]").prop("selected", true);*/
                        }

                        blockFields();
                    }
                } else {
                    restartFields(true);
                }
        });
    });

  {$funcionCamposAlInicio}();
JAVASCRIPT
);
