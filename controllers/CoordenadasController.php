<?php

namespace app\controllers;

use Yii;
use app\models\VistaEmprendimiento;
use yii\web\Controller;

class CoordenadasController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => 'yii\filters\AjaxFilter',
                'only' => ['get-coordenadas']
            ],
        ];
    }
        /**
     * Get Coordenaas para el maoations for Select2 dropdown
     */
    public function actionGetCoordenadasByVista($id_vista=null)
    {
        echo "<script> alert('estoy en coordenadas controller') </script>";
        $model= VistaEmprendimiento::find()
            ->where(['id_vista'=>$id_vista])
            ->one();
        //die(var_dump($model));
        return $this->render('mostrarEmprendimientoSelected', [
            'model' => $model,
        ]);

    }
}
?>