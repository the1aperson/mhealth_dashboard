<?php

use common\components\DateFormatter;
use common\models\ParticipantTestSession;

/* @var scheduleInfo \common\studyDefinitions\StudySection */
/* @var $latest_finished_test */
/* @var $participant */	
?>

<div class="section current-phase-section">
	<div class="row">
		<div class="col-md-5 border-right">
			<p class="section-header">
				During <?= $scheduleInfo->name; ?>
			</p>
			<div class="current-phase-label">
				<span class="icon-calendar_gray"></span>
				<span class="text-bold med-numbers">
					Week <?= $scheduleInfo->week; ?>
				</span>		
			</div>
			<?php
				$latest_test_date = "None";
				if(empty($latest_finished_test) == false)
				{
					$latest_test_date = DateFormatter::abbreviatedDate($latest_finished_test->session_date);
				}
				
				$isInactive = ( empty($latest_finished_test) || $participant->enabled == 0);
			?>
			<div id="current-phase-latest-test" class="<?= $isInactive ? "inactive" : "active"; ?>">
				<?= $isInactive ? "Inactive" : "Active"; ?> (Last Test: <?= $latest_test_date; ?>)
			</div>
		</div>
		<div class="col-md-7">
			<p class="section-header">
			This Week's Schedule
			</p>
			<p class="current-phase-test-schedule">
			<?php foreach($scheduleInfo->tests as $test): ?>
				<b><?= Yii::$app->studyDefinitions->testTypeLabel($test->type); ?></b> <?= $test->frequency_label; ?><br/>
				<b class="completed">Completed:</b><?= ParticipantTestSession::countCompletedTests($participant->id, $test->type) ?>/<?= ParticipantTestSession::countTests($participant->id, $completed = null, $test->type); ?><span class="icon-info-outline_blue" data-html="true" data-toggle="tooltip" data-placement="top" title="Number of surveys completed out of the total number of surveys made available to the participant to date."></span><br/>
			<?php endforeach; ?>
			</p>
		</div>
	</div>
	
</div>