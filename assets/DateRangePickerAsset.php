<?php

namespace app\assets;

use yii\web\AssetBundle;

class DateRangePickerAsset extends AssetBundle
{
    public $sourcePath = '@vendor/bower-asset';
    public $css = [
        'bootstrap-daterangepicker/daterangepicker.css',
    ];
    public $js = [
        'moment/moment.js',
        'moment/locale/es.js',
        'bootstrap-daterangepicker/daterangepicker.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
