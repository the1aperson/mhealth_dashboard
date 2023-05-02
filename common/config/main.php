<?php
	$studyDefinitions = require __DIR__ . '/studyDefinitions.php';
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@top'   => dirname(dirname(__DIR__)),
        '@exports' => '@top/temp_export',
        
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'bootstrap' => ['log'],
    'components' => [
	    'studyDefinitions' => $studyDefinitions,
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'urlManager' => [
		    'enablePrettyUrl' => true,
		    'showScriptName' => false,
		    'rules' => [ ],
		],
		'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'participantMetadataHandler' => [
	        'class' => 'common\components\MHParticipantMetadataHandler'
        ],
        'studyMetadataHandler' => [
	        'class' => 'common\components\StudyMetadataHandler'
        ],
        'ipAnonymizer' => ['class' => 'common\components\IpAnonymizer'],
        
        'log' => [
            'targets' => [
                [
	                'class' => 'yii\log\FileTarget',
 	                'logFile' => '@runtime/logs/test.log',
	                'categories' => ['test'],
	                'except' => ['application'],
	                'levels' => ['info', 'error', 'warning'],
	                'logVars' => [],
                ],
            ],
        ],
    ],
];
