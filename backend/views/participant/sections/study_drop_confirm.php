<?php 
	use yii\helpers\Url;
	use yii\widgets\Pjax;
	use backend\models\ParticipantDropForm;
	
	$model = new ParticipantDropForm();
	$model->participant_id = $participant->id;
?>

<div id="study-settings-drop" class="collapse no-transition">
    <div class="modal-body">
        <p>You've chosen to <b>Drop This Participant.</b>
        <p>
            This will mark the participant as dropped in the dashboard and help ensure more accurate retention and adherence statistics. Their data will still be visible for display.    
        </p>
        <p>
            Are you sure you want to proceed?
        </p>
    </div>
	<div class="modal-footer">
		<div class="button-list">
			<button type="button" class="button-blue pull-left" data-toggle="collapse" data-target="#study-settings-drop, #study-settings-confirm-drop" >Yes, Drop Participant</button>
			<button type="button" class="button-blue pull-left" data-toggle="collapse" data-target="#study-settings-main, #study-settings-drop">No, Keep Participant</button>
			<button type="button" class="button-light pull-left" data-dismiss="modal">Cancel</button>
		</div>
	</div>
</div>

<div id="study-settings-confirm-drop" class="collapse no-transition" >
    <?php Pjax::begin(['linkSelector' => false, 'clientOptions' => ['history' => false]]); ?>
	<?= $this->render('study_drop_form', ['model' => $model, 'participant' => $participant]); ?>
    <?php Pjax::end(); ?>
</div>