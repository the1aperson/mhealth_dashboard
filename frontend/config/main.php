<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
            'cookieValidationKey' => 'c3ae7f313f2bb1de5b2be2895c8b4371',
		    'parsers' => [
		        'application/json' => 'yii\web\JsonParser',
		    ]
        ],
        'user' => [
            'identityClass' => 'common\models\Participant',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'arc-core-api',
            'useCookies' => false,
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
            'class' => 'frontend\helpers\RestErrorHandler',
            'errorAction' => 'site/error',
        ],
        'response' => [
            'formatters' => [

                \yii\web\Response::FORMAT_JSON => [
                     'class' => 'yii\web\JsonResponseFormatter',
                     'prettyPrint' => true,
                ],
            ],
        ],
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [

	            'POST device-registration' => 'site/device-registration',
 	            'POST submit-test' => 'site/submit-test',
	            
	            'POST submit-wake-sleep-schedule' => 'site/submit-wake-sleep-schedule',
	            'POST submit-test-schedule' => 'site/submit-test-schedule',
	            
	            'POST signature-data' => 'site/signature-data',
	            'POST device-heartbeat' => 'site/device-heartbeat',
	            'GET get-session-info' => 'site/get-session-info',
	            'GET get-test-schedule' => 'site/get-test-schedule',
	            'GET get-wake-sleep-schedule' => 'site/get-wake-sleep-schedule',
            ],
        ],        
    ],
    'params' => $params,
];
