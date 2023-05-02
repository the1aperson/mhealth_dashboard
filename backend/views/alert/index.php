<?php
	use common\models\Alert;
	use yii\helpers\Url;
	/* @var $alertInfoGroups associative array of alert info:
		["alerts" =>,
		"total_count" =>,
		"require_follow_up_count" => ]
	*/
	
	/* @var $showType */
	
	$this->title = "Alerts";
	$alert_levels = Alert::getAlertLevels(true);
	$this->registerJsFile(Url::base() . '/js/tabs.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);
	$redAlertInfo = isset($alertInfoGroups['30']) ? $alertInfoGroups['30'] : null;
	$host = $_SERVER['REQUEST_URI'];
?>

<div class="alerts-index">
	
	<?php if(strpos($host, '30')): ?>
		<div class="alert-tabs">
			<ul id="red-alert-tabs" class="nav nav-tabs" role="tabList">
				<li role="presentation" class="active"><a id="all-red" class="tab-label active" href="#all-red" aria-controls="all-red" data-tab="all-red-pane" role="tab" data-toggle="tab" >All (<?=$redAlertInfo["total_count"];?>)</a></li>
				<li role="presentation" ><a id="no-action" class="tab-label" href="#no-action" aria-controls="no-action" data-tab="no-action-pane" role="tab" data-toggle="tab" >No Action Taken (<?=$redAlertInfo["require_follow_up_count"];?>)</a></li>
				<li role="presentation" ><a id="followed-up" class="tab-label" href="#followed-up" aria-controls="followed-up" data-tab="followed-up-pane" role="tab" data-toggle="tab" >Followed Up (<?=$redAlertInfo["followed_up_count"];?>)</a></li>
			</ul>
		</div>
	<?php endif;?>
	<?php foreach($alert_levels as $level): ?>
	<?php
		$alertInfo = $alertInfoGroups[$level] ?? [];
		
		if($showType != null && $showType != $level)
		{
			continue;
		}
		
		$view_all_link = \yii\helpers\Url::current(["type" => $level, "all" => true], true);
		$alertInfo["view_all_link"] = $view_all_link;
		$alertInfo["title"]  = Alert::alertLevelLabel($level);
		if(strpos($host, '30') || strpos($host, '20') || strpos($host, '10')){
			$this->title = $alertInfo["title"];
		} else {
			$this->title = "Alerts";
		}
	?>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane  active" id="all-red-pane">
				<div class="row">
					<div class="col-lg-8 col-md-12">			    
						<?= $this->render("/fragments/alerts/alert_section", $alertInfo); ?>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="no-action-pane">
				<div class="row">
					<div class="col-lg-8 col-md-12">			    
						<?= $this->render("/fragments/alerts/alert_section", $alertInfo); ?>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="followed-up-pane">
				<div class="row">
					<div class="col-lg-8 col-md-12">			    
						<?= $this->render("/fragments/alerts/alert_section", $alertInfo); ?>
					</div>
				</div>
			</div>

		</div>

	<?php endforeach; ?>
	
	
</div>