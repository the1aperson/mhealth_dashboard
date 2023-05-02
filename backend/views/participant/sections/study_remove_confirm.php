<?php 
	use yii\helpers\Url;
	use yii\widgets\Pjax;
	use backend\models\ParticipantDropForm;
			
	$model = new ParticipantDropForm();
	$model->participant_id = $participant->id;
?>

<div id="study-settings-remove" class="collapse no-transition">
    <div class="modal-body">
        <p>You've chosen to <b>Remove This Participant.</b>
        <p>
            Removing this participant will result in PLACEHOLDER TEXT PLACEHOLDER TEXT PLACEHOLDER TEXT.
        </p>
        <p>
            Are you sure you want to proceed?
        </p>
    </div>
	<div class="modal-footer">
		<div class="button-list">
			<button type="button" class="button-blue pull-left" data-toggle="collapse" data-target="#study-settings-remove, #study-settings-confirm-remove" >Yes, Remove Participant</button>
			<button type="button" class="button-blue pull-left" data-toggle="collapse" data-target="#study-settings-main, #study-settings-remove">No, Keep Participant</button>
			<button type="button" class="button-light pull-left" data-dismiss="modal">Cancel</button>
		</div>
	</div>
</div>

<div id="study-settings-confirm-remove" class="collapse no-transition" >

    <?php Pjax::begin(['linkSelector' => false, 'clientOptions' => ['history' => false]]); ?>
	<?= $this->render('study_remove_form', ['model' => $model, 'participant' => $participant]); ?>
    <?php Pjax::end(); ?>
</div>