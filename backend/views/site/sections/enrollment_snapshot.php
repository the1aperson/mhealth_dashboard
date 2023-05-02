<?php

	$enrollment_count_sections = [
		[
			"title" => "Newly Enrolled",
			"value" => $newly_enrolled,
			"sub_title" => "Within the Last 7 Days",
		],
		[
			"title" => "Total Completers",
			"value" => $total_completed,
			"sub_title" => "For Life of Study",
		],
		[
			"title" => "Total Drops",
			"value" => $dropped_count,
			"sub_title" => "For Life of Study",
		]
	];
	
	
?>
<div id="enrollment-snapshot" class="section">
	<h3 class="section-header">
		Enrollment Snapshot
	</h3>
	<div class="flex-md-row">
		<div class="flex-col enrollment-column">
			<?php foreach($enrollment_count_sections as $section): ?>
			<div class="light-blue-subsection">
				<div class="flex-row">
					<div class="flex-col-1 text-right">
						<span class="big-numbers"><?= $section["value"]; ?></span>
					</div>
					<div class="flex-col-2">
						<span class="big-number-sub">
							<?= $section["title"]; ?>
						</span>
						<p class="text-dark-gray font-small">
							<?= $section["sub_title"]; ?>
						</p>
					</div>
				</div>
		    </div>
			<?php endforeach; ?>
		</div>
		<div class="flex-col enrollment-column">

				<div id="enrollment-total-active">
					<h3 class="section-sub-header">Total Active Participants in All Phases&nbsp;<span class="icon-info-outline_blue big-number-sub"data-html="true" data-toggle="tooltip" data-placement="top" title="Total participants actively enrolled who have not yet completed."></span></h3>
					<div class="phase-count-container">
						<span class="med-numbers">
						<?= $total_active; ?>
						</span>
					</div>
				</div>
				<div id="enrollment-current-study-phases">
					<h3 class="section-sub-header">
						Current Study Phases
					</h3>
					<div class="phase-count-container">
						<div class="stacked-bar-graph vertical">
							<?php foreach($phase_counts as $name => $count): ?>
							<div class="stack-bar-item" style="flex-grow: <?= $count; ?>;"></div>
							<?php endforeach; ?>
						</div>
			
						<ul class="phase-count-list">
							<?php foreach($phase_counts as $name => $count): ?>
							<li class="phase-item">
							<span class="pull-left"><?= ucwords(str_replace('_', ' ',$name)); ?></span>
								<span class="pull-right"><?= $count; ?></span>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>

		</div>
	</div>
</div>
