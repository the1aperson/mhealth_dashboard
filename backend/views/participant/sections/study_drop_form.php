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
	$this->registerJsFile(Url::base() . '/js/show_password.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);
?>
<?php $form = ActiveForm::begin([
	    'options' => ['data' => ['pjax' => true, 'participant-id' => $participant->id], ],
	    'action' => Url::to(['/participant/drop', 'id' => $participant->id], true)
	    ]); ?>
<div class="modal-body">
    <p>Please <b>enter your password to confirm</b> dropping this participant.</p>
	<p>
		This will mark the participant as dropped in the dashboard and help ensure more accurate retention and adherence statistics. Their data will still be visible for display.
	</p>
	<div class="row">
		<div class="col-sm-6">		
			<?= $form->field($model, 'password', ['template' => "<div class='password_reveal'><p class='show_password' onclick='study_drop_password()'>Show</p><span id='eye-icon' class='icon-visibility-on_blue' onclick='study_drop_password()'></span></div>{input}"])->passwordInput(); ?>
			<?= $form->field($model, 'participant_id', ['options' => ['class' => 'hidden']])->hiddenInput()->label(false); ?>
			<?= $form->field($model, 'updated_at', ['options' => ['class' => 'hidden']])->hiddenInput()->label(false); ?>
		</div>
	</div>
</div>
<div class="modal-footer">
   <div class="button-list">
	    <?= Html::submitButton('Drop Participant', ['class' => 'button-blue pull-left']) ?>
	   <button type="button" class="button-light pull-left" data-dismiss="modal">Cancel</button>
   </div>
</div>
<?php ActiveForm::end(); ?>