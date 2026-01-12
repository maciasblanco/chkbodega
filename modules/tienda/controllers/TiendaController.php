<?php
// modules/tienda/controllers/TiendaController.php

namespace app\modules\tienda\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\data\ActiveDataProvider;
use app\models\Tienda;
use app\models\Vendedor;
use app\models\Producto;

/**
 * TiendaController implements the CRUD actions for Tienda model.
 */
class TiendaController extends Controller
{
    /**
     * @inheritdoc
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'toggle-status' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Tienda models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Tienda::find()->where(['eliminado' => false]),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'd_creacion' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Tienda model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $productosDataProvider = new ActiveDataProvider([
            'query' => Producto::find()
                ->where(['id_tienda' => $id, 'eliminado' => false])
                ->orderBy(['d_creacion' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 12,
            ],
        ]);

        return $this->render('view', [
            'model' => $model,
            'productosDataProvider' => $productosDataProvider,
        ]);
    }

    /**
     * Creates a new Tienda model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Tienda();
        $model->activo = true;
        $model->tipo_propietario = Tienda::TIPO_VENDEDOR;

        // Si el usuario ya es vendedor, asociar automáticamente
        $vendedor = Vendedor::find()->where(['user_id' => Yii::$app->user->id])->one();
        if ($vendedor) {
            $model->id_vendedor = $vendedor->id;
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->user_id = Yii::$app->user->id;
            
            // Procesar logo upload
            $model->logoFile = UploadedFile::getInstance($model, 'logoFile');
            if ($model->logoFile) {
                $model->uploadLogo();
            }

            // Procesar banner upload
            $model->bannerFile = UploadedFile::getInstance($model, 'bannerFile');
            if ($model->bannerFile) {
                $model->uploadBanner();
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Tienda creada exitosamente.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Error al crear la tienda: ' . implode(', ', $model->firstErrors));
            }
        }

        return $this->render('create', [
            'model' => $model,
            'vendedor' => $vendedor,
        ]);
    }

    /**
     * Updates an existing Tienda model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->checkOwnership($model);

        if ($model->load(Yii::$app->request->post())) {
            // Procesar logo upload
            $model->logoFile = UploadedFile::getInstance($model, 'logoFile');
            if ($model->logoFile) {
                $model->uploadLogo();
            }

            // Procesar banner upload
            $model->bannerFile = UploadedFile::getInstance($model, 'bannerFile');
            if ($model->bannerFile) {
                $model->uploadBanner();
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Tienda actualizada exitosamente.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Error al actualizar la tienda.');
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Tienda model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->checkOwnership($model);

        $model->eliminado = true;
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Tienda eliminada exitosamente.');
        } else {
            Yii::$app->session->setFlash('error', 'Error al eliminar la tienda.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Toggles the active status of a Tienda model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionToggleStatus($id)
    {
        $model = $this->findModel($id);
        $this->checkOwnership($model);

        $model->activo = !$model->activo;
        if ($model->save()) {
            $status = $model->activo ? 'activada' : 'desactivada';
            Yii::$app->session->setFlash('success', "Tienda {$status} exitosamente.");
        } else {
            Yii::$app->session->setFlash('error', 'Error al cambiar el estado de la tienda.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Dashboard for store owners
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDashboard($id)
    {
        $model = $this->findModel($id);
        $this->checkOwnership($model);

        // Estadísticas
        $stats = [
            'totalProductos' => $model->getProductosActivos()->count(),
            'productosActivos' => $model->getProductos()->andWhere(['activo' => true])->count(),
            'productosInactivos' => $model->getProductos()->andWhere(['activo' => false])->count(),
            'productosSinStock' => $model->getProductos()->andWhere(['stock' => 0])->count(),
        ];

        // Productos recientes
        $productosRecientes = $model->getProductos()
            ->orderBy(['d_creacion' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('dashboard', [
            'model' => $model,
            'stats' => $stats,
            'productosRecientes' => $productosRecientes,
        ]);
    }

    /**
     * Finds the Tienda model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Tienda the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Tienda::findOne(['id' => $id, 'eliminado' => false])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La tienda solicitada no existe.');
    }

    /**
     * Checks if the current user owns the tienda
     * @param Tienda $model
     * @throws NotFoundHttpException
     */
    protected function checkOwnership($model)
    {
        if ($model->user_id !== Yii::$app->user->id && !Yii::$app->user->can('admin')) {
            throw new NotFoundHttpException('No tienes permisos para acceder a esta tienda.');
        }
    }
}