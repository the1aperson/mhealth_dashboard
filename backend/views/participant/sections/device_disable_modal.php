<?php
	
	use yii\helpers\Url;
	use yii\widgets\ActiveForm;
	use yii\helpers\Html;
    use backend\models\DeviceToggleForm;

	$model = new DeviceToggleForm();	
	$model->participant_id = $participant->id;
	$model->device_id = $device->id;

	$url = $device->active ? "/participant/disable-device" : "/participant/enable-device";
?>
<div class="modal fade" id="device-disable-modal-<?= $participant->id; ?>" tabindex="-1" role="dialog" aria-labelledby="device-disable-modal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
	        <div class="modal-header">
		        <p class="text-light-gray">Device Settings for</p>
		        <h2><p class="text-dark-gray">Participant <?= $participant->participant_id; ?></p></h2>
	        </div>

	        <div id="device-disable-main" class="collapse in no-transition">

                <?php $form = ActiveForm::begin(['action' => Url::to([$url, "id" => $participant->id], true)]); ?>
                        <div class="modal-body">
                        <?php if($device->active):?>
                            <p>Are you sure you want to disable this device?</p>      
                            <p>This participant <b>will be unable to submit test data</b> until this device is re-enabled, or the participant registers a new device.</p>
                            <?php else:?>
                            <p>Are you sure you want to re-enable this device?</p>      
                            <?php endif; ?>
                            <?= $form->field($model, 'participant_id', ['options' => ['class' => 'hidden']])->hiddenInput()->label(false); ?>
                            <?= $form->field($model, 'device_id', ['options' => ['class' => 'hidden']])->hiddenInput()->label(false); ?>
                            <?= $form->field($model, 'updated_at', ['options' => ['class' => 'hidden']])->hiddenInput()->label(false); ?>
                        </div>
                        <div class="modal-footer">
                            <div class="button-list">
                            <?php if($device->active):?>
                                <?= Html::submitButton("Yes, Disable Device", ['class' => 'button-blue pull-left']); ?>
                            <?php else: ?>
                                <?= Html::submitButton("Yes, Re-enable Device", ['class' => 'button-blue pull-left']); ?>
                            <?php endif; ?>
                                <button type="button" class="button-light pull-left" data-dismiss="modal">Cancel</button>
                            </div>
                        </div>                   
                <?php ActiveForm::end(); ?>   
            </div>
        </div>
    </div>
</div>