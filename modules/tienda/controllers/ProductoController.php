<?php
// modules/tienda/controllers/ProductoController.php

namespace app\modules\tienda\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\data\ActiveDataProvider;
use app\models\Producto;
use app\models\Tienda;
use app\models\CategoriaProducto;

/**
 * ProductoController implements the CRUD actions for Producto model.
 */
class ProductoController extends Controller
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
                    'toggle-featured' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Producto models for a specific store.
     * @param integer $tienda_id
     * @return mixed
     */
    public function actionIndex($tienda_id = null)
    {
        $query = Producto::find()->where(['eliminado' => 0]);

        if ($tienda_id) {
            $tienda = $this->findTiendaModel($tienda_id);
            $this->checkTiendaOwnership($tienda);
            $query->andWhere(['id_tienda' => $tienda_id]);
        } else {
            // Mostrar productos de todas las tiendas del usuario
            $userTiendas = Tienda::find()
                ->select('id')
                ->where(['user_id' => Yii::$app->user->id, 'eliminado' => 0])
                ->column();
            
            if (empty($userTiendas)) {
                Yii::$app->session->setFlash('info', 'No tienes tiendas registradas. <a href="' . \yii\helpers\Url::to(['tienda/create']) . '">Crear una tienda</a>');
                return $this->redirect(['tienda/create']);
            }
            
            $query->andWhere(['id_tienda' => $userTiendas]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'fecha_creacion' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'tienda' => $tienda_id ? $this->findTiendaModel($tienda_id) : null,
        ]);
    }

    /**
     * Displays a single Producto model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $this->checkProductOwnership($model);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Producto model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param integer $tienda_id
     * @return mixed
     */
    public function actionCreate($tienda_id = null)
    {
        $model = new Producto();
        $model->activo = 1;
        $model->stock = 0;

        if ($tienda_id) {
            $tienda = $this->findTiendaModel($tienda_id);
            $this->checkTiendaOwnership($tienda);
            $model->id_tienda = $tienda_id;
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->user_id = Yii::$app->user->id;
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Producto creado exitosamente.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Error al crear el producto: ' . implode(', ', $model->firstErrors));
            }
        }

        $categorias = CategoriaProducto::find()
            ->where(['activo' => 1])
            ->orderBy(['nombre' => SORT_ASC])
            ->all();

        $tiendas = Tienda::find()
            ->where(['user_id' => Yii::$app->user->id, 'eliminado' => 0, 'activo' => 1])
            ->orderBy(['nombre' => SORT_ASC])
            ->all();

        return $this->render('create', [
            'model' => $model,
            'categorias' => $categorias,
            'tiendas' => $tiendas,
            'tienda' => $tienda_id ? $this->findTiendaModel($tienda_id) : null,
        ]);
    }

    /**
     * Updates an existing Producto model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->checkProductOwnership($model);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Producto actualizado exitosamente.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Error al actualizar el producto.');
            }
        }

        $categorias = CategoriaProducto::find()
            ->where(['activo' => 1])
            ->orderBy(['nombre' => SORT_ASC])
            ->all();

        $tiendas = Tienda::find()
            ->where(['user_id' => Yii::$app->user->id, 'eliminado' => 0, 'activo' => 1])
            ->orderBy(['nombre' => SORT_ASC])
            ->all();

        return $this->render('update', [
            'model' => $model,
            'categorias' => $categorias,
            'tiendas' => $tiendas,
        ]);
    }

    /**
     * Deletes an existing Producto model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->checkProductOwnership($model);

        $model->eliminado = 1;
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Producto eliminado exitosamente.');
        } else {
            Yii::$app->session->setFlash('error', 'Error al eliminar el producto.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Toggles the active status of a Producto model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionToggleStatus($id)
    {
        $model = $this->findModel($id);
        $this->checkProductOwnership($model);

        $model->activo = $model->activo ? 0 : 1;
        if ($model->save()) {
            $status = $model->activo ? 'activado' : 'desactivado';
            Yii::$app->session->setFlash('success', "Producto {$status} exitosamente.");
        } else {
            Yii::$app->session->setFlash('error', 'Error al cambiar el estado del producto.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Toggles the featured status of a Producto model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionToggleFeatured($id)
    {
        $model = $this->findModel($id);
        $this->checkProductOwnership($model);

        $model->destacado = $model->destacado ? 0 : 1;
        if ($model->save()) {
            $status = $model->destacado ? 'destacado' : 'quitado de destacados';
            Yii::$app->session->setFlash('success', "Producto {$status} exitosamente.");
        } else {
            Yii::$app->session->setFlash('error', 'Error al cambiar el estado de destacado.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Manages stock for a Producto model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionManageStock($id)
    {
        $model = $this->findModel($id);
        $this->checkProductOwnership($model);

        if (Yii::$app->request->isPost) {
            $operation = Yii::$app->request->post('operation');
            $quantity = (int)Yii::$app->request->post('quantity', 0);

            if ($quantity > 0) {
                if ($operation === 'add') {
                    $model->stock += $quantity;
                    $message = "Se agregaron {$quantity} unidades al stock.";
                } elseif ($operation === 'subtract' && $model->stock >= $quantity) {
                    $model->stock -= $quantity;
                    $message = "Se restaron {$quantity} unidades del stock.";
                } else {
                    Yii::$app->session->setFlash('error', 'Cantidad inválida o stock insuficiente.');
                    return $this->refresh();
                }

                if ($model->save()) {
                    Yii::$app->session->setFlash('success', $message);
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    Yii::$app->session->setFlash('error', 'Error al actualizar el stock.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'La cantidad debe ser mayor a cero.');
            }
        }

        return $this->render('manage-stock', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the Producto model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Producto the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Producto::findOne(['id' => $id, 'eliminado' => 0])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('El producto solicitado no existe.');
    }

    /**
     * Finds the Tienda model based on its primary key value.
     * @param integer $id
     * @return Tienda the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findTiendaModel($id)
    {
        if (($model = Tienda::findOne(['id' => $id, 'eliminado' => 0])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La tienda solicitada no existe.');
    }

    /**
     * Checks if the current user owns the tienda of the product
     * @param Producto $model
     * @throws NotFoundHttpException
     */
    protected function checkProductOwnership($model)
    {
        $tienda = $model->tienda;
        if (!$tienda || $tienda->user_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException('No tienes permisos para acceder a este producto.');
        }
    }

    /**
     * Checks if the current user owns the tienda
     * @param Tienda $model
     * @throws NotFoundHttpException
     */
    protected function checkTiendaOwnership($model)
    {
        if ($model->user_id !== Yii::$app->user->id && !Yii::$app->user->can('admin')) {
            throw new NotFoundHttpException('No tienes permisos para acceder a esta tienda.');
        }
    }
}