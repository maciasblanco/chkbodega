<?php

namespace app\controllers;

use Yii;
use app\models\Parroquia;
use app\models\Municipio;
use yii\web\Controller;

class ParroquiaController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => 'yii\filters\AjaxFilter',
                'only' => ['get-by-muni']
            ],
        ];
    }

    /**
     * Get options for Select2 dropdown
     */
    public function actionGetByMuni($muni)
    {
        

        $results = Parroquia::find()
            ->where(['id_municipio' => $muni])
            ->orderBy(['parroquia'=>SORT_ASC])
            ->all();

        $response = [];
        
        if (!empty($results)) {

            foreach ($results as $row) {
                $response[] = [
                    'id' => $row->id,
                    'text' => $row->parroquia,
                ];
            }
          
            
        }

        echo json_encode(['results' => $response]);
        exit;
    }
    public function actionGetByMuniCod($muni)
    {

        //filtro por el codigo en municipio para obtener el id_municipo
        $cod_municipio=(string)$muni;
        

        $id_municipio=Municipio::find()
        ->where(['codigo_municipio'=> $cod_municipio])
        ->one();

        

        $results = Parroquia::find()
            ->where(['id_municipio' => $id_municipio['id']])
            ->orderBy(['parroquia'=>SORT_ASC])
            ->all();

        $response = [];
        
        if (!empty($results)) {

            foreach ($results as $row) {
                $response[] = [
                    'id' => $row->codigo_parroquia,
                    'text' => $row->parroquia,
                ];
            }            
        }

        echo json_encode(['results' => $response]);
        exit;
    }
    public function actionList($id)
      {
          $countParroquia = Parroquia::find()
                      ->where(['codigo_municipio'=>$id])
                      ->count();

          $parroquias = Parroquia::find()
                  ->where(['codigo_municipio'=>$id])
                  ->all();

          if ($countParroquia > 0)
           {
              foreach ($parroquias as $parroquia) {
                  echo "<option value='".$parroquia->id_parroquia."'>".$parroquia->parroquia."</options>";
              }
          }
          else
          {
              echo "<option> No existen Parroquias Registradas </option>";
          }
      }
}
