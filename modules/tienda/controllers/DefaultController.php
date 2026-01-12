<?php
// modules/tienda/controllers/DefaultController.php

namespace app\modules\tienda\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Default controller for the `tienda` module
 */
class DefaultController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Solo usuarios autenticados
                    ],
                ],
            ],
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'usuario' => Yii::$app->user->identity,
        ]);
    }

    /**
     * Registro de vendedores
     */
    public function actionRegistroVendedor()
    {
        // Por ahora solo mostramos una vista básica
        // En el futuro integrar con el sistema de roles múltiples
        
        return $this->render('registro-vendedor', [
            'usuario' => Yii::$app->user->identity,
        ]);
    }

    /**
     * Dashboard del vendedor
     */
    public function actionDashboardVendedor()
    {
        // Verificar si el usuario ya tiene rol de vendedor
        // Si no, redirigir al registro
        
        return $this->render('dashboard-vendedor', [
            'usuario' => Yii::$app->user->identity,
        ]);
    }
}