<?php
	use yii\helpers\Url;
	use yii\helpers\Html;
	use yii\widgets\ActiveForm;
	use yii\widgets\Pjax;
	
	$this->registerJs('document.getElementById("eye-icon_'.$alert->id.'").addEventListener("click", function alert_password(){

		var followup_password = document.getElementById("followupform-password_'.$alert->id.'");
		var show_password = document.getElementById("show-hide_'.$alert->id.'");
		var eye_icon = document.getElementById("eye-icon_'.$alert->id.'");
				
		if (followup_password.type == "password") {
			(followup_password.type = "text"),
			(show_password.innerHTML = "Hide");
			(eye_icon.className = "icon-visibility-off_blue");
		} else {
			(followup_password.type = "password"),
			(show_password.innerHTML = "Show");
			(eye_icon.className = "icon-visibility-on_blue");
		}
		}, true);
				
	document.getElementById("show-hide_'.$alert->id.'").addEventListener("click", function alert_password(){

		var followup_password = document.getElementById("followupform-password_'.$alert->id.'");
		var show_password = document.getElementById("show-hide_'.$alert->id.'");
		var eye_icon = document.getElementById("eye-icon_'.$alert->id.'");
				
		if (followup_password.type == "password") {
			(followup_password.type = "text"),
			(show_password.innerHTML = "Hide");
			(eye_icon.className = "icon-visibility-off_blue");
		} else {
			(followup_password.type = "password"),
			(show_password.innerHTML = "Show");
			(eye_icon.className = "icon-visibility-on_blue");
		}
	}, true);');
?>
<div class="modal-body">
	<p class="modal-title">Log Follow Up</p>	        
	<?= $this->render("alert_item", ['alert' => $alert, 'show_close' => false, 'show_modal' => false]); ?>
	
	<?php $form = ActiveForm::begin([
	    'options' => ['data' => ['pjax' => true, 'alert-id' => $alert->id], 'class' => 'alert-follow-up-form', ],
	    'action' => Url::to(['/alert/log-followup', 'id' => $alert->id], true)
	    ]); ?>
		<p>Please select the method you used to follow up with the Participant.</p>
		
		<div class="row">
			<div class="col-sm-4">
				<?= $form->field($model, 'alert_id')->hiddenInput()->label(false); ?>
				<div class='follow-up-options'>
					<?= $form->field($model, 'email', ['template' => "<div class=\"follow-up log-modal\">\n{input}\n{label}\n{error}\n{hint}\n</div>"])->checkbox(['checked'=>false, 'id'=>"email-$alert->id"], false)->label("Email", ['class'=>'log-label']); ?>
					<?= $form->field($model, 'phone_call', ['template' => "<div class=\"follow-up log-modal\">\n{input}\n{label}\n{error}\n{hint}\n</div>"])->checkbox(['checked'=>false, 'id'=>"phone-$alert->id"], false)->label("Phone Call", ['class'=>'log-label']); ?>
					<?= $form->field($model, 'text', ['template' => "<div class=\"follow-up log-modal\">\n{input}\n{label}\n{error}\n{hint}\n</div>"])->checkbox(['checked'=>false, 'id'=>"text-$alert->id"], false)->label("Text", ['class'=>'log-label']); ?>
				</div>
                <?= $form->field($model, 'password', ['template' => "<div class='password_reveal'><p id='show-hide_".$alert->id."' class='show_password'>Show</p><span id='eye-icon_".$alert->id."' class='icon-visibility-on_blue'></span></div>{input}"])->passwordInput(['id' => 'followupform-password_'.$alert->id]); ?>
			</div>
		</div>
		<div class="form-group">
		    <?= Html::submitButton('Submit', ['class' => 'button-blue']) ?>
		    <button class="button-light" data-dismiss="modal">Cancel</button>
		</div>
	<?php ActiveForm::end(); ?>
</div>