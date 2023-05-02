<?php
	
	$col_size = count($adherenceRates) > 0 ? floor(12 / count($adherenceRates))  : 0;	

	$study = $participant->study_id;

	$overallAdherence = Yii::$app->participantMetadataHandler->getAdherence($participant->id, "all", "all")["all"] ?? "0";
	$finished_tests = Yii::$app->participantMetadataHandler->getFinishedTestCount($participant->id, "all", "all")["all"] ?? 0;
	$missed_tests = Yii::$app->participantMetadataHandler->getMissedTestCount($participant->id, "all", "all")["all"] ?? 0;
	$total_tests = $finished_tests + $missed_tests;
	$averageAdherence = Yii::$app->studyMetadataHandler->getMetadata($study, 'adherence_percent')->value ?? 0;
	$adherenceDiff = abs(round($averageAdherence - $overallAdherence))

?>
<div class="section participant-adherence-section">
	<div class="row">
		<div class="col-sm-5">
			<p class='text-header'>
				Adherence Rate
			</p>
			<div class="adherence-section-body flex-left">
				<div>
					<span class="med-numbers">
					<?= $overallAdherence; ?>%
					</span>
				</div>
				<div class="stack-bar-adherence-container">
					<div class="stack-bar-adherence-pointer" style="left: <?= $overallAdherence; ?>%;" ></div>
					<div class="stacked-bar-graph stack-bar-adherence">
						<div class="stack-bar-item-danger" style="flex-grow:60;" ></div>
						<div class="stack-bar-item-warning" style="flex-grow:15;" ></div>
						<div class="stack-bar-item-success" style="flex-grow:25;" ></div>
					</div>
					<?php if($overallAdherence > $averageAdherence):  ?>
						<div class="rate-difference"><?= $adherenceDiff; ?>% Higher Than Overall Rate</div>
					<?php else: ?>
						<div class="rate-difference"><?= $adherenceDiff; ?>% Lower Than Overall Rate</div>
					<?php endif;?>
				</div>
			</div>
		</div>
		<div class="col-sm-7">
			<p class='text-header'>
				Adherence per Test Type
			</p>
			<?php foreach($adherenceRates as $testType => $rate): ?>
			<div class="col-sm-<?= $col_size * 2; ?>">
				<p><?= Yii::$app->studyDefinitions->testTypeLabel($testType); ?></p>
				<div class="test-rate-container">
					<p><?= $rate; ?>%</p>
					<div class="test-rate-bar">
					<?php if($rate == 100): ?>
						<div class="rate-bar-done" style="width:<?= $rate?>%; border-radius: 4px"></div>
						<div class="rate-bar-full" style="width: 0"></div>
					<?php elseif($rate == 0):  ?>
						<div class="rate-bar-done" style="width: 0"></div>
						<div class="rate-bar-full" style="width: 100%; border-radius: 4px"></div>
					<?php else: ?>
						<div class="rate-bar-done" style="width:<?= $rate?>%; border-radius: 4px 0 0 4px"></div>
						<div class="rate-bar-full" style="width:<?= 100 - $rate ?>%; border-radius: 0 4px 4px 0 "></div>
					<?php endif; ?>

					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>