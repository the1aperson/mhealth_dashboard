<?php
	
	use yii\helpers\Url;
	use yii\helpers\Html;
	use yii\widgets\ActiveForm;
	
	use yii\bootstrap\Alert;
	
	if($model->hasErrors('updated_at'))
	{
		$error = implode(" ", $model->getErrors('updated_at'));
		$model->clearErrors('updated_at');
		
		echo Alert::widget([
		    'options' => [
		        'class' => 'alert-warning',
		    ],
		    'body' => $error,
		]);
	}

	$this->registerJs('document.getElementById("eye-icon_'.$participant->id.'").addEventListener("click", function study_remove_password(){

		var study_remove_password = document.getElementById("participantdropdform-password_'.$participant->id.'");
		var show_password = document.getElementById("show-hide_'.$participant->id.'");
		var eye_icon = document.getElementById("eye-icon_'.$participant->id.'");
				
		if (study_remove_password.type == "password") {
			(study_remove_password.type = "text"),
			(show_password.innerHTML = "Hide");
			(eye_icon.className = "icon-visibility-off_blue");
		} else {
			(study_remove_password.type = "password"),
			(show_password.innerHTML = "Show");
			(eye_icon.className = "icon-visibility-on_blue");
		}
		}, true);
				
	document.getElementById("show-hide_'.$participant->id.'").addEventListener("click", function study_remove_password(){

		var study_remove_password = document.getElementById("participantdropdform-password_'.$participant->id.'");
		var show_password = document.getElementById("show-hide_'.$participant->id.'");
		var eye_icon = document.getElementById("eye-icon_'.$participant->id.'");
				
		if (study_remove_password.type == "password") {
			(study_remove_password.type = "text"),
			(show_password.innerHTML = "Hide");
			(eye_icon.className = "icon-visibility-off_blue");
		} else {
			(study_remove_password.type = "password"),
			(show_password.innerHTML = "Show");
			(eye_icon.className = "icon-visibility-on_blue");
		}
	}, true);');	
?>
<?php $form = ActiveForm::begin([
	    'options' => ['data' => ['pjax' => true, 'participant-id' => $participant->id], ],
	    'action' => Url::to(['/participant/hide', 'id' => $participant->id], true)
	    ]); ?>
<div class="modal-body">
    <p>Please <b>enter your password to confirm</b> removing this participant.</p>
	<p>
		Removing this participant will result in PLACEHOLDER TEXT PLACEHOLDER TEXT PLACEHOLDER TEXT.
	</p>
	<div class="row">
	<div class="col-sm-6">			
			<?= $form->field($model, 'password', ['template' => "<div class='password_reveal'><p class='show_password' id='show-hide_".$participant->id."'>Show</p><span id='eye-icon_".$participant->id."' class='icon-visibility-on_blue'></span></div>{input}"])->passwordInput(['id' => 'participantdropdform-password_'.$participant->id]); ?>
			<?= $form->field($model, 'participant_id', ['options' => ['class' => 'hidden']])->hiddenInput()->label(false); ?>
			<?= $form->field($model, 'updated_at', ['options' => ['class' => 'hidden']])->hiddenInput()->label(false); ?>
		</div>
	</div>
</div>
<div class="modal-footer">
   <div class="button-list">
	    <?= Html::submitButton('Remove Participant', ['class' => 'button-blue pull-left']) ?>
	   <button type="button" class="button-light pull-left" data-dismiss="modal">Cancel</button>
   </div>
</div>
<?php ActiveForm::end(); ?>