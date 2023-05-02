<?php
	
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $notes Array of common\model\ParticipantNote */
/* @var $noteModel backend\models\NoteForm */

$showForm = "";
if($noteModel->hasErrors())
{
	$showForm = "in";
}
?>

<div class="section note-section">
	
	<div class="note-section-top clearfix">
		<p class="section-header pull-left">Notes</p>
		<?php if(Yii::$app->user->can('noteParticipants')): ?>
		<span class="pull-right button-light" data-toggle="collapse" data-target="#note-section-form" aria-expanded="false" >Add Note</span>
		<?php endif; ?>
	</div>
	
	<?php if(Yii::$app->user->can('noteParticipants')): ?>
		<div id="note-section-form" class=" clearfix collapse <?= $showForm; ?>">
			<div id="note-section-form-wrap">
				<div class="clearfix">
					<div class="pull-left">
						<span class="icon-warning-solid_yellow"></span> 
					</div>
					<div style="overflow: hidden; padding-left: 10px;">
						<p class="font-small text-subsection-blue"><b>REMINDER:</b> Do not include PII in the note section. Only indicate notes concerning the health of the study, and do not indicate patient health information.
						</p>
					</div>
				</div>
				
				
			    <?php $form = ActiveForm::begin(); ?>
			
			    <?= $form->field($noteModel, 'message')->textarea()->label('New Note'); ?>
				<?= $form->field($noteModel, 'updated_at')->hiddenInput()->label(false); ?>
			    <div class="form-group">
					<div class="button-list">
				        
				        <?= Html::submitButton('Save', ['class' => 'button-blue']); ?>
				        <span class="button-light" data-toggle="collapse" data-target="#note-section-form">Cancel</span>
					</div>
			    </div>
				
			    <?php ActiveForm::end(); ?>
			</div>
		</div>
	<?php endif; ?>

	
	<div class="note-section-content">
		<?php if(count($notes) > 0): ?>
			<?php foreach($notes as $i => $note):?>
			<?php if($i > 0): ?><hr class="note-section-hr" /><?php endif; ?>
				<?= $this->render("note_item", ["note" => $note]); ?>
			<?php endforeach; ?>
		<?php else: ?>
		<div class="note-item">
			<p><br />
				No Notes for Participant <?= $participant->participant_id; ?>
			</p>
		</div>
		<?php endif; ?>
	</div>
	
</div>