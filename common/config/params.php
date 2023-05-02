<?php
	$permission_settings = require __DIR__ . '/permissionSettings.php';
	$export_settings = require __DIR__ . "/exportSettings.php";
$params = [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 86400,
    'staff_permission_settings' => $permission_settings,
    'auditClass' => 'common\models\UserAuditTrail',
    
    'export_types' => [
		'csv',
		'json',
		'dat',
    ],
    
	'data_formatters' => [
		'cognitive' => 'common\dataFormatters\MhCognitiveFormatter',
	],
	
    'default_export_type' => 'csv',
    
    "adherence_update_rate" => 300,  // How frequently the adherence data should update when being viewed in the dashboard
    "study_adherence_update_rate" => 3600, // How frequently the study-wide adherence data is updated

];


$params = array_merge($params, $export_settings);
return $params;