<?php
	
/* @var $items array of StudyMetadata */

$col_size = count($items) > 0 ? floor(12 / count($items)) : 0;

?>
<div class="sh-rate-flex-container">
	<?php foreach($items as $item):
		$metadata = $item["metadata"];
		if($item == null)
		{
			continue;
		}

		$value = round($metadata->value);
		$tooltip_message = $item["tooltip_message"] ?? "";
	?>
		<div class="sh-rate-flex-section">
			<div class="sh-rate-item">
				
				<div class="sh-rate-values">
					<span class="big-numbers text-black"><?= $value; ?></span>
					<span class="big-number-sub text-light-gray"><?= $metadata->metadataNameLabel(); ?></span>
					<span class="icon-info-outline_blue big-number-sub" style="margin-top: 3px;" data-html="true" data-toggle="tooltip" data-placement="top" title="<?= $tooltip_message; ?>"></span>
				</div>
				<div class="sh-rate-graph">
					<div class="sh-75-bar">
						75%
					</div>
					<svg class="sh-rate-mask" viewBox="0 0 960 420" preserveAspectRatio="none">
					    <polygon points="0 0,960 0,0 420" fill="white" > </polygon>
					</svg>
					<div class="sh-rate-bar" style="width: <?= $value; ?>%; ">
					</div>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
