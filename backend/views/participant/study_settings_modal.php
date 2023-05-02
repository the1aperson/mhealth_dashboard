<?php
	use yii\helpers\Url;
	
	/* @var $participant */
?>
<div class="modal fade" id="study-settings-modal" tabindex="-1" role="dialog" aria-labelledby="study-settings-modal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
	        <div class="modal-header">
		        <p class="text-light-gray">Study Settings for</p>
		        <h2><p class="text-dark-gray">Participant <?= $participant->participant_id; ?></p></h2>
	        </div>

	            <div id="study-settings-main" class="collapse in no-transition">
	                <div class="modal-body">
		                <div class="row">
			                <div class="col-sm-6">
					            <p>Study Participation:<br />
						            <b class="text-dark-gray"><?= $participant->enabled == 1 ? "Active" : "Inactive"; ?></b> <br />
					            </p>
					            <?php if(Yii::$app->user->can('dropParticipants') && $participant->enabled == 1): ?>
					            <p>
						            <span class="button-blue" data-toggle="collapse" data-target="#study-settings-main, #study-settings-drop">Drop Participant</span>
					            </p>
					            <p>
						           This will mark the participant as dropped in the dashboard and help ensure more accurate retention and adherence statistics. Their data will still be visible for display. 
					            </p>
					            <?php endif; ?>
			                </div>
			                <?php if(Yii::$app->user->can('hideParticipants')): ?>
			                <div class="col-sm-6">
				                <p>Data Collection:<br />
						            <b class="text-dark-gray">Not Collected</b> <br />
					            </p>
					            <p>
						            <span class="button-blue" data-toggle="collapse" data-target="#study-settings-main, #study-settings-remove">Remove Data</span>
					            </p>
					            <p>
						            Removing this participant will result in PLACEHOLDER TEXT PLACEHOLDER TEXT PLACEHOLDER TEXT.
					            </p>
			                </div>
			                <?php endif; ?>
		                </div>
	                </div>
					<div class="modal-footer">
						<button type="button" class="button-light pull-left" data-dismiss="modal">Cancel</button>
					</div>
	            </div>
	            
	            <?php if(Yii::$app->user->can('dropParticipants') && $participant->enabled == 1): ?>
					<?= $this->render('sections/study_drop_confirm', ['participant' => $participant]); ?>
	            <?php endif; ?>
	            <?php if(Yii::$app->user->can('hideParticipants')): ?>
					<?= $this->render('sections/study_remove_confirm', ['participant' => $participant]); ?>
				<?php endif; ?>
            </div>

        </div>
    </div>
</div>
