<?php
// widgets/TasaDolarWidget.php

namespace app\widgets;

use yii\base\Widget;
use yii\helpers\Html;

class TasaDolarWidget extends Widget
{
    public $showCalculator = true;
    public $compact = false;

    public function run()
    {
        $tasaActual = \app\models\TasaDolar::getTasaActual();
        
        return $this->render('tasaDolarWidget', [
            'tasaActual' => $tasaActual,
            'showCalculator' => $this->showCalculator,
            'compact' => $this->compact,
        ]);
    }
}