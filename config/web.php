<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'GED',
    'name' => 'Escuela Polideportiva y Cultural San Agustín',
    'language' => 'es',
    'timeZone' => 'America/Caracas',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'layout' => 'main',
    //'defaultRoute' =>'site/login',  // Default controller when no specific one is set in the URL
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],

   'components' => [

        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'mjbvsistemas-ChkBodega-12012026',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'itemTable' => 'seguridad.auth_item',
            'itemChildTable' => 'seguridad.auth_item_child',
            'assignmentTable' => 'seguridad.auth_assignment',
            'ruleTable' => 'seguridad.auth_rule',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
                'rules' => [
                    'tasa-dolar' => 'tasa-dolar/index',
                    'tasa-dolar/actualizar' => 'tasa-dolar/actualizar',
                                'rules' => [
                    // API Routes
                    'POST api/sync/push' => 'api/sync/push',
                    'GET api/sync/pull' => 'api/sync/pull',
                    'GET api/sync/status' => 'api/sync/status',
                    'GET api/sync/status/<device_uuid:\w+>' => 'api/sync/status',
                    'POST api/sync/conflict-resolve' => 'api/sync/conflict-resolve',
                    
                    // Mantener rutas existentes
                    '<controller:\w+>/<id:\d+>' => '<controller>/view',
                    '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                    '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                ],
            ],
        ],
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'jsOptions' => [
                        'position' => \yii\web\View::POS_HEAD
                    ],
                ],
                //'dmstr\web\AdminLteAsset' => [
                //'skin' => 'skin-black',
                //],
            ],
        ],
    ],

    'modules' => [
        //rbac security
        'admin' => [
            'class' => 'mdm\admin\Module',
            //'layout' => 'left-menu',
            'mainLayout' => '@app/views/layouts/mainAdminlte.php',
        ],
        //modulo de acceso al sistema
        'acces' => [
            'class' => 'app\modules\acces\acces',
        ],
        //modulo de acceso al sistema
        
        'reportes' => [
            'class' => 'app\modules\reportes\reportes',
        ],
        
    ],

    /* ACTIVAR ERRORES PARA DIAGNOSTICAR
    */
        'on beforeRequest' => function () {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
        },
    /** 
     * aqui termina el codigo de prueba
    */
    'params' => array_merge($params, [
        'tienda' => [
            'maxProductosPorTienda' => 100,
            'comisionVenta' => 3, // 3% de comisión
            'monedaPredeterminada' => 'USD',
        ]
    ]),
        'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            'site/logout',
            //'site/index',
            //'site/error',
            //'site/sidebar',
            //'site/contact',
            //'site/about',
            'ged/*',
            'site/*',
            'tienda/*',
            'tienda/marketplace/*',
            'municipio/get-by-edo',
            'parroquia/get-by-muni',
            'admin/user/signup',
            'admin/user/request-password-reset',
            'admin/user/reset-password',
            //'*',
        ]
    ],

];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // ✅ CONFIGURACIÓN CORREGIDA - AGREGAR DOMINIO planealo.sytes.net
        'allowedIPs' => [
            '201.209.14.141', 
            '127.0.0.1', 
            '::1', 
            '192.168.1.120',
            'localhost',
            'planealo.sytes.net',
            '*.sytes.net',
            // Agregar rangos de red local adicionales
            '192.168.1.*',
            '10.0.*.*',
        ],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // ✅ MISMAS IPs PARA GII
        'allowedIPs' => [
            '201.209.14.141', 
            '127.0.0.1', 
            '::1', 
            '192.168.1.120',
            'localhost',
            'planealo.sytes.net',
            '*.sytes.net',
            '192.168.1.*',
            '10.0.*.*',
        ],
    ];
}

return $config;