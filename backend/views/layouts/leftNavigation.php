<?php
	

use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use common\models\Study;
use common\models\StudyUserAuth;




$this->registerJsFile(Url::base() . '/js/leftnav.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);


NavBar::begin([
			'renderInnerContainer' => false,
    'options' => [
        'class' => 'topnav-offset navbar-fixed-left',
    ],
    'containerOptions' => [
	    'id' => 'navbar-left-list',
		'class' => ['collapse' => '', 'widget' => ''],
    ]
]);

// If we're on  the select-study page, we don't actually want to show any of this.
$study = Yii::$app->study->getStudy();
$host = Yii::$app->request->baseUrl;
if($study == null || $host == '/select-study' || $host == '/study/create' || $host == '/study/index' || $study->status != Study::STATUS_ACTIVE): ?>
<div class="view-data">Please select a study to view available data.</div>
<?php else:

	$menuItems  = [];
	$menuItemSettings  = [
		[
			'name' => 'Overview',
			'url' => 'overview',
			'controller' => 'site',
			'icon' => 'icon-dashboard',
			'visible' => true,
		],
		
		[
			'name' => 'Alerts',
			'url' => "alerts",
			'controller' => 'alert',
			'icon' => 'icon-alerts',
			'visible' => Yii::$app->user->can('manageAlerts') || Yii::$app->user->can('viewAlerts')
		],
		
		[
			'name' => 'Participant Tracking',
			'icon' => 'icon-participant-tracking',
			'url' => "participants",
			'controller' => 'participant',
			'visible' => Yii::$app->user->can('viewParticipants')
		],
		[
			'name' => 'Staff',
			'icon' => 'icon-staff',
			'url' => "staff",
			'controller' => 'staff',
			'visible' => Yii::$app->user->can('viewUsers')
		],
		[
			'name' => 'Roles',
			'icon' => 'icon-roles',
			'url' => "roles",
			'controller' => 'role',
			'visible' => Yii::$app->user->can('modifyRoles')
		],
	
		[
			'name' => 'Site Locations',
			'icon' => 'icon-location_white',
			'url' => "site-location",
			'controller' => 'site-location',
			'visible' => Yii::$app->user->can('modifySiteLocations') || Yii::$app->user->can('viewSiteLocations')
		],
	];
		
	if(Yii::$app->study->isStudySet() && $study->status == Study::STATUS_ACTIVE)
	{
	
		$study = Yii::$app->study->getStudy();
		$menuItemSettings []= [
			'name' => 'Study Settings',
			'icon' => 'icon-admin',
			'url' => 'study/view?id='. $study->id,
			'controller' => 'study',
			'visible' => Yii::$app->user->can('modifyStudies')
		];
	}
	
	
	$currentController = Yii::$app->controller->id;
	foreach($menuItemSettings as $itemSettings)
	{
		$item = [];
		$icon = $itemSettings['icon'];
		$name = $itemSettings['name'];
		$url = $itemSettings['url'];
		$visible = $itemSettings['visible'];
		$controller = $itemSettings['controller'];
		$active = false;
	
		if($currentController == $controller)
		{
			$active = true;
			$icon .= " active";
		} else if ($currentController == 'rater'  && $controller == 'staff'){
			$active = true;
			$icon .= ' active';
		}
		
		$item['label'] = '<div><div class="nav-pill-rect"></div><div class="nav-pill-icon '.$icon.'"></div>'.$name.'</div>';
		$item['url'] = Url::to($url, true);
		$item['visible'] = $visible;
		$item['options'] = ['title' => $name, 'class' => ($active ? 'navbar-left-active' : '')];
		$menuItems[]= $item;
	}


	echo Nav::widget([
		'options' => ['class' => 'nav-pills nav-stacked'],
		'items' => $menuItems,
		'encodeLabels' => false,
	]);
endif; ?>
<hr/>
<ul class="alt-nav" style="list-style-type:none;">
	<li title="Privacy Policy">
		<a href="<?=Url::to("privacy-policy", true);?>">Privacy Policy</a>
	</li>
	<li>
		<a href="<?= Url::to("contact-us", true); ?>">Contact Us</a>
	</li>
</ul>

<div id="navbar-left-toggle">
	<span class="navbar-left-toggle-button pull-right cursor"><span class="nlt-icon"></span></span>
</div>
<?php

NavBar::end();
?>
