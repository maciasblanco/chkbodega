<?php
// modules/tienda/controllers/MarketplaceController.php

namespace app\modules\tienda\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use app\models\Producto;
use app\models\Tienda;
use app\models\CategoriaProducto;

/**
 * MarketplaceController for public marketplace views
 */
class MarketplaceController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            // Puedes agregar cache aquí para mejorar performance
        ];
    }

    /**
     * Main marketplace page
     * @return string
     */
    public function actionIndex()
    {
        // Productos destacados
        $productosDestacados = Producto::getProductosDestacados(8);

        // Productos recientes
        $productosRecientes = Producto::getProductosActivos()
            ->orderBy(['d_creacion' => SORT_DESC])
            ->limit(8)
            ->all();

        // Tiendas activas
        $tiendasDestacadas = Tienda::getTiendasActivas()
            ->orderBy(['rating' => SORT_DESC, 'd_creacion' => SORT_DESC])
            ->limit(6)
            ->all();

        // Categorías principales
        $categoriasPrincipales = CategoriaProducto::getCategoriasRaiz();

        return $this->render('index', [
            'productosDestacados' => $productosDestacados,
            'productosRecientes' => $productosRecientes,
            'tiendasDestacadas' => $tiendasDestacadas,
            'categoriasPrincipales' => $categoriasPrincipales,
        ]);
    }

    /**
     * Browse all products with filters
     * @return string
     */
    public function actionProductos()
    {
        $query = Producto::getProductosActivos();

        // Filtros
        $categoriaId = Yii::$app->request->get('categoria');
        $precioMin = Yii::$app->request->get('precio_min');
        $precioMax = Yii::$app->request->get('precio_max');
        $busqueda = Yii::$app->request->get('q');
        $orden = Yii::$app->request->get('orden', 'recientes');

        // Aplicar filtros
        if ($categoriaId) {
            $query->andWhere(['id_categoria' => $categoriaId]);
        }

        if ($precioMin !== null && $precioMin !== '') {
            $query->andWhere(['>=', 'precio', $precioMin]);
        }

        if ($precioMax !== null && $precioMax !== '') {
            $query->andWhere(['<=', 'precio', $precioMax]);
        }

        if ($busqueda) {
            $query->andWhere(['or',
                ['ilike', 'nombre', $busqueda],
                ['ilike', 'descripcion', $busqueda],
                ['ilike', 'caracteristicas', $busqueda]
            ]);
        }

        // Aplicar ordenamiento
        switch ($orden) {
            case 'precio_asc':
                $query->orderBy(['precio' => SORT_ASC]);
                break;
            case 'precio_desc':
                $query->orderBy(['precio' => SORT_DESC]);
                break;
            case 'nombre':
                $query->orderBy(['nombre' => SORT_ASC]);
                break;
            case 'destacados':
                $query->orderBy(['destacado' => SORT_DESC, 'd_creacion' => SORT_DESC]);
                break;
            default: // recientes
                $query->orderBy(['d_creacion' => SORT_DESC]);
                break;
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 12,
            ],
        ]);

        $categorias = CategoriaProducto::getCategoriasRaiz();

        return $this->render('productos', [
            'dataProvider' => $dataProvider,
            'categorias' => $categorias,
            'filtros' => [
                'categoria' => $categoriaId,
                'precio_min' => $precioMin,
                'precio_max' => $precioMax,
                'q' => $busqueda,
                'orden' => $orden,
            ],
        ]);
    }

    /**
     * View a single product
     * @param integer $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionProducto($id)
    {
        $model = $this->findProductoModel($id);

        // Productos relacionados (misma categoría)
        $relacionados = Producto::getProductosActivos()
            ->andWhere(['id_categoria' => $model->id_categoria])
            ->andWhere(['!=', 'id', $model->id])
            ->limit(4)
            ->all();

        // Incrementar vistas (podrías implementar un contador)
        // $model->updateCounters(['vistas' => 1]);

        return $this->render('producto', [
            'model' => $model,
            'relacionados' => $relacionados,
        ]);
    }

    /**
     * View a store profile
     * @param string $slug
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionTienda($slug)
    {
        $model = $this->findTiendaModel($slug);

        // Productos de la tienda
        $query = $model->getProductosActivos()
            ->orderBy(['destacado' => SORT_DESC, 'd_creacion' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 12,
            ],
        ]);

        return $this->render('tienda', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Browse all stores
     * @return string
     */
    public function actionTiendas()
    {
        $query = Tienda::getTiendasActivas()
            ->orderBy(['nombre' => SORT_ASC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 12,
            ],
        ]);

        return $this->render('tiendas', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Browse products by category
     * @param integer $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionCategoria($id)
    {
        $categoria = $this->findCategoriaModel($id);

        $query = Producto::getProductosActivos()
            ->andWhere(['id_categoria' => $id])
            ->orderBy(['destacado' => SORT_DESC, 'd_creacion' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 12,
            ],
        ]);

        // Subcategorías
        $subcategorias = $categoria->getHijos()->all();

        return $this->render('categoria', [
            'categoria' => $categoria,
            'dataProvider' => $dataProvider,
            'subcategorias' => $subcategorias,
        ]);
    }

    /**
     * Search functionality
     * @return string
     */
    public function actionBuscar()
    {
        $q = Yii::$app->request->get('q', '');
        
        if (empty($q)) {
            return $this->redirect(['productos']);
        }

        $query = Producto::getProductosActivos()
            ->andWhere(['or',
                ['ilike', 'nombre', $q],
                ['ilike', 'descripcion', $q],
                ['ilike', 'caracteristicas', $q]
            ])
            ->orderBy(['d_creacion' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 12,
            ],
        ]);

        // También buscar tiendas
        $tiendasQuery = Tienda::getTiendasActivas()
            ->andWhere(['ilike', 'nombre', $q])
            ->orWhere(['ilike', 'descripcion', $q]);

        $tiendasDataProvider = new ActiveDataProvider([
            'query' => $tiendasQuery,
            'pagination' => [
                'pageSize' => 6,
            ],
        ]);

        return $this->render('buscar', [
            'dataProvider' => $dataProvider,
            'tiendasDataProvider' => $tiendasDataProvider,
            'q' => $q,
        ]);
    }

    /**
     * Finds the Producto model based on its primary key value.
     * @param integer $id
     * @return Producto the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findProductoModel($id)
    {
        $model = Producto::findOne($id);
        if ($model !== null && $model->isDisponible() && $model->tienda->isActiva()) {
            return $model;
        }

        throw new NotFoundHttpException('El producto solicitado no existe o no está disponible.');
    }

    /**
     * Finds the Tienda model based on its slug value.
     * @param string $slug
     * @return Tienda the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findTiendaModel($slug)
    {
        if (($model = Tienda::findOne(['slug' => $slug, 'activo' => true, 'eliminado' => false])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La tienda solicitada no existe.');
    }

    /**
     * Finds the CategoriaProducto model based on its primary key value.
     * @param integer $id
     * @return CategoriaProducto the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findCategoriaModel($id)
    {
        if (($model = CategoriaProducto::findOne(['id' => $id, 'activo' => true])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La categoría solicitada no existe.');
    }
}