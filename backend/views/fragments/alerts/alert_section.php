<?php

/* @var $title string required! */
/* @var $alerts Array of common\models\Alert required! */
/* @var $label string */
/* @var $view_all_link string */
/* @var $view_all_label string */
/* @var $total_count */
/* @var $require_follow_up_count */	
	
	$title = isset($title) ? $title : "Alerts";
	
	if(Yii::$app->user->can('manageAlerts') == false && Yii::$app->user->can('viewAlerts') == false)
	{
		return;
	}
?>

<div class="section alert-section">
	<div class="alert-section-top row">
		<div class="col-sm-12">
			<span class="section-header pull-left"> <?= $title; ?></span>
			<?php if(isset($label)): ?>
				<span class="alert-section-label pull-right"><?= $label; ?></span>
			<?php endif; ?>
			
			<span class="pull-right">
			<?php if(isset($require_follow_up_count) && $require_follow_up_count > 0): ?>
			<span class="font-small"><?= $require_follow_up_count; ?> Need Action, </span> 
			<?php endif; ?>
			<?php if(isset($total_count)): ?>
			<span class="font-small"><?= $total_count; ?> Total Alerts</span>
			<?php endif; ?>&nbsp;&nbsp;

			<?php if(isset($view_all_link)): ?>
				<a class="alert-section-link text-uppercase text-medium" href="<?= $view_all_link; ?>"><?= isset($view_all_label) ? $view_all_label : "View All"; ?></a>
			<?php endif; ?>
			</span>
		</div>
	</div>
	<div class="alert-section-body">
		
		<?php foreach($alerts as $alert): ?>
		<?= $this->render('alert_item', ["alert" => $alert]); ?>
		<?php endforeach; ?>
	</div>
</div>