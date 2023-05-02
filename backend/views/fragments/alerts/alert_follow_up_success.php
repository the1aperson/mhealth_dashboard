<?php
	use yii\helpers\Url;
	use yii\helpers\Html;
	use yii\widgets\ActiveForm;
	use yii\widgets\Pjax;
	
?>
<div class="modal-body">
	<p class="modal-title">Follow Up Logged Successfully!</p>	        
	
	<?= $this->render("alert_item", ['alert' => $alert, 'show_close' => false, 'show_modal' => false]); ?>
	
	<p>Optionally, you may visit the participant’s page to leave a note.</p>
	<p>
		<a href="<?=Url::to(["/participant/view", 'id' => $participant->id], true); ?>">
			Go To Participant <?= $participant->participant_id; ?>'s Page
		</a>
	</p>
	<div class="form-group">
		<button class="button-blue" data-dismiss="modal">Close</button>
	</div>
	
</div>