<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => false,
            'authTimeout' => YII_DEBUG ? 3600 : 900,
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'prefix' => function(){$ip = Yii::$app->ipAnonymizer->hashedIp(); return "[$ip]"; },
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        
        'study' => 'backend\components\StudySessionManager',
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
	            'alerts' => '/alert',
	            'alert/clear/<alert_id:\d+>' => '/alert/clear',
	            
	            'participants' => '/participant',
	            'studies' => '/study',
                'roles'=> '/role',
                
                'privacy-policy' => '/site/privacy-policy',
	            'login' => '/site/login',
	            'logout' => '/site/logout',
	            'select-study' => '/site/select-study',
	            'request-password-reset' => '/site/request-password-reset',
	            'reset-password' => '/site/reset-password',
	            'view-export' => '/site/view-export',
	            'export-data' => '/site/export-data',
	            'download-export' => '/site/download-export',
	            'update-available-permissions' => '/site/update-available-permissions',
	            'overview' => 'site/index',
	            
            ],
        ],
        
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        
    ],
    'params' => $params,
];
