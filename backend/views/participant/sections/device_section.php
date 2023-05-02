<?php
	
	use yii\helpers\Url;
	use yii\widgets\ActiveForm;
	use yii\helpers\Html;
	use backend\models\DeviceToggleForm;
	
	$model = new DeviceToggleForm();	
	$model->participant_id = $participant->id;
	$model->device_id = $device->id;

?>

<div id="participant-current-device">
    <p class="current-device-heading">Current Device</p>
    <div class="current-device-section">
	    <p class="font-small">
		    <?= $device->device_type; ?><br />
		    <?= $device->os_type . " " . $device->os_version; ?><br />
		    App Version <?= $device->app_version; ?>
	    </p>
    </div>
	<div class="current-device-section clearfix">
		<?php if($device->active): ?>
			<span class="pull-left">Device Enabled</span>
		<?php else: ?>
			<span class="pull-left">Device Disabled</span>
		<?php endif; ?>
		
		<?php if(Yii::$app->user->can('updateParticipants')): ?>
			<span class="pull-right button-toggle <?=($device->active ? 'active' : '')?>" data-toggle="modal" href="#device-disable-modal-<?= $participant->id; ?>"></span>
		<?php endif; ?>
	</div>
</div>
<?= $this->render('device_disable_modal', ['participant' => $participant, 'device' => $device, 'model' => $model]); ?>
