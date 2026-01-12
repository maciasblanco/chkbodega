<?php

namespace app\assets;

use yii\web\AssetBundle;

class Select2LoadAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [];
    public $js = [
       'js/loadSelect2.js',
    ];
    public $depends = [
        'yii\bootstrap5\BootstrapPluginAsset',
        'app\assets\Select2Asset',
    ];
}
