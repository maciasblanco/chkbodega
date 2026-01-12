<?php

namespace app\controllers;

use Yii;
use app\models\Municipio;
use yii\web\Controller;

class MunicipioController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => 'yii\filters\AjaxFilter',
                'only' => ['get-by-edo']
            ],
        ];
    }

    /**
     * Get options for Select2 dropdown
     */
    public function actionGetByEdo($edo)
    {
        $results = Municipio::find()
            ->where(['id_estado' => $edo])
            ->orderBy(['municipio'=>SORT_ASC])
            ->all();

        $response = [];

        if (!empty($results)) {
            foreach ($results as $row) {
                $response[] = [
                    'id' => $row->id,
                    'text' => $row->municipio,
                ];
            }
        }
        echo json_encode(['results' => $response]);

        exit;
    }


    public function actionList($id)
      {
          $countMunicipio = Municipio::find()
                      ->where(['codigo_estado'=>$id])
                      ->count();

          $municipios = Municipio::find()
                  ->where(['codigo_estado'=>$id])
                  ->all();

          if ($countMunicipio > 0)
           {
              foreach ($municipios as $municipio) {
                  echo "<option value='".$municipio->codigo_municipio."'>".$municipio->municipio."</options>";
              }
          }
          else
          {
              echo "<option> No existen Municipios registrados </option>";
          }
      }
}
