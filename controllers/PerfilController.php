<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use app\models\AtletasRegistro;
use app\models\AportesSemanales;

class PerfilController extends Controller
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
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Información personal del atleta
     */
    public function actionMiInformacion($id = null)
    {
        // Si no se proporciona ID, usar el del usuario actual
        if ($id === null) {
            $atleta = AtletasRegistro::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->andWhere(['eliminado' => false])
                ->one();
            
            if (!$atleta) {
                throw new NotFoundHttpException('No se encontró su información de atleta.');
            }
            
            return $this->redirect(['mi-informacion', 'id' => $atleta->id]);
        }

        $model = $this->findModel($id);

        // Verificar permisos
        if (!Yii::$app->user->can('viewOwnInfo', ['atleta' => $model]) && 
            !Yii::$app->user->can('viewRepresentedInfo', ['atleta' => $model])) {
            throw new \yii\web\ForbiddenHttpException('No tiene permiso para ver esta información.');
        }

        return $this->render('mi-informacion', [
            'model' => $model,
        ]);
    }

    /**
     * Reporte de deudas del atleta
     */
    public function actionMisDeudas($id = null)
    {
        // Si no se proporciona ID, usar el del usuario actual
        if ($id === null) {
            $atleta = AtletasRegistro::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->andWhere(['eliminado' => false])
                ->one();
            
            if (!$atleta) {
                throw new NotFoundHttpException('No se encontró su información de atleta.');
            }
            
            return $this->redirect(['mis-deudas', 'id' => $atleta->id]);
        }

        $atleta = $this->findModel($id);

        // Verificar permisos
        if (!Yii::$app->user->can('viewOwnDeudas', ['atleta' => $atleta]) && 
            !Yii::$app->user->can('viewRepresentedDeudas', ['atleta' => $atleta])) {
            throw new \yii\web\ForbiddenHttpException('No tiene permiso para ver estas deudas.');
        }

        // Obtener deudas pendientes
        $deudasPendientes = AportesSemanales::obtenerDeudasPendientes($atleta->id);
        $montoTotalDeuda = AportesSemanales::calcularMontoDeuda($atleta->id);
        $totalSemanasDeuda = count($deudasPendientes);

        // Obtener estadísticas
        $estadisticas = AportesSemanales::getEstadisticasAtleta($atleta->id);

        return $this->render('mis-deudas', [
            'atleta' => $atleta,
            'deudasPendientes' => $deudasPendientes,
            'montoTotalDeuda' => $montoTotalDeuda,
            'totalSemanasDeuda' => $totalSemanasDeuda,
            'estadisticas' => $estadisticas,
        ]);
    }

    /**
     * Lista de atletas representados (solo para representantes)
     */
    public function actionMisRepresentados()
    {
        if (!Yii::$app->user->can('representante')) {
            throw new \yii\web\ForbiddenHttpException('Solo los representantes pueden ver esta información.');
        }

        // Buscar el representante del usuario actual
        $representante = \app\models\RegistroRepresentantes::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->andWhere(['eliminado' => false])
            ->one();

        if (!$representante) {
            throw new NotFoundHttpException('No se encontró su información de representante.');
        }

        // Obtener atletas representados
        $atletas = AtletasRegistro::find()
            ->where(['id_representante' => $representante->id])
            ->andWhere(['eliminado' => false])
            ->all();

        return $this->render('mis-representados', [
            'representante' => $representante,
            'atletas' => $atletas,
        ]);
    }

    /**
     * Encuentra el modelo AtletasRegistro
     */
    protected function findModel($id)
    {
        $model = AtletasRegistro::find()
            ->where(['id' => $id])
            ->andWhere(['eliminado' => false])
            ->one();

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('El atleta solicitado no existe.');
    }
}