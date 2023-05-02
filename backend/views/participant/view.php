<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;


use common\models\Participant;

/* @var $this yii\web\View */
/* @var $participant common\models\Participant */
/* @var $alerts Array of common\models\Alert */
/* @var $notes Array of common\model\ParticipantNote */
/* @var $noteModel backend\models\NoteForm */

$this->title = "Participant Details";

$this->registerJsFile(Url::base() . '/js/participant.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);
$this->registerJsFile(Url::base() . '/js/tabs.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);

?>

<?php $this->beginBlock('header-content'); ?>
	<?= $this->render('/fragments/export/export_button', ['modal' => 'participant', 'additionalParams' => ['formatterOptions[combineSessions]'=> 1,'participant_id' => $participant->id] ]); ?>
<?php $this->endBlock(); ?>

<div class="participant-tabs">
	<ul id="details-tabs" class="nav nav-tabs" role="tabList">
		<li role="presentation" class="active"><a id="status" class="tab-label active" href="#status" aria-controls="status" data-tab="status-pane" role="tab" data-toggle="tab" >Status</a></li>
		<li role="presentation"><a id="device-history" class="tab-label" href="#device-history" aria-controls="status" data-tab="device-history-pane" role="tab" data-toggle="tab" >Device History</a></li>
	</ul>
</div>
<div id="participant-view">
    

    <?php if(isset($droppedRecord) && $droppedRecord != null): ?>
    	<?= $this->render('sections/dropped_banner', ['droppedRecord' => $droppedRecord]); ?>
    <?php endif; ?>
    
	<?php if(isset($completed) && $completed == true): ?>
    	<?= $this->render('sections/completed_banner', ['final_test' => $final_test]); ?>
    <?php endif; ?>

    <div class="row">
	    
	    <div id="participant-left">
		    <div id="participant-info-column" class="section">
			    <p>App ID:</p>
			    <h3 id="participant-info-id"><?= $participant->participant_id; ?></h3>

				<div id="participant-flag-section" class="clearfix">
					<?php if(Yii::$app->user->can('flagParticipants')): ?>
						<?php if($participant->isFlagged()): ?>
							<span>Flagged as Important</span> <a href="<?= Url::to(["/participant/unflag", "id" => $participant->id], true);?>" class="pull-right icon-flag_filled"></a>
						<?php else: ?>
							<span>Not Flagged as Important</span> <a href="<?= Url::to(["/participant/flag", "id" => $participant->id], true);?>" class="pull-right icon-flag_empty"></a>
						<?php endif; ?>
					<?php else: ?>
						<?php if($participant->isFlagged()): ?>
							<span>Flagged as Important</span> <span class="pull-right icon-flag_filled"></span>
						<?php else: ?>
							<span>Not Flagged as Important</span> <span class="pull-right icon-flag_empty"></span>
						<?php endif; ?>

					<?php endif; ?>
					
				</div>
				
				<div id="participant-study-dates">
					<?php if($first_test != null && $final_test != null): ?>
						<span class="icon-calendar_gray"></span> <?= date("M Y", $first_test->session_date); ?><span class="icon-arrow-right_blue"></span> <?= date("M Y", $final_test->session_date); ?>
					<?php endif; ?>
				</div>
			    <?php if(isset($device)): ?>
				<?= $this->render('sections/device_section', ['participant' => $participant, 'device' => $device]); ?>
				<?php endif; ?>
			    <?php if(Yii::$app->user->can('hideParticipants') || Yii::$app->user->can('dropParticipants')): ?>
				    <span class="button-light button-full-width" data-toggle="modal" data-target="#study-settings-modal">Edit Study Settings</span>
				<?php endif; ?>
		    </div>
	    </div>
	    
		<div id="participant-right">
			<div class="tab-content">
				<div role="tabpanel" class="tab-pane active" id="status-pane">
					<?= $this->render("/fragments/alerts/alert_section", ["alerts" => $alerts, "title" => "Alerts", "view_all_link" => Url::to(["/alerts", "participant_id" => $participant->id], true)]); ?>
					<?= $this->render("sections/current_phase_section", ["scheduleInfo" => $scheduleInfo, 'participant' => $participant, 'latest_finished_test' => $latest_finished_test]); ?>
					<?= $this->render("sections/adherence_section", ['participant' => $participant,'adherenceRates' => $adherenceRates]); ?>
										
					<?php if(Yii::$app->user->can('noteParticipants') || Yii::$app->user->can('viewParticipantNotes')): ?>
					<?= $this->render("/fragments/notes/note_section", ["noteModel" => $noteModel, "notes" => $notes, 'participant' => $participant]); ?>
					<?php endif; ?>
				</div>
				<div role="tabpanel" class="tab-pane" id="device-history-pane">
					<?= $this->render("sections/device_history", ['dataProvider' => $dataProvider]); ?>
				<div>
			</div>
		
		</div>

    </div>
    
</div>

<?= $this->render('study_settings_modal', ['participant' => $participant]); ?>
