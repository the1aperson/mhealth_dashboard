<?php
	/* @var $items array of StudyMetadata */	
	$col_size = count($items) > 0 ? floor(12 / count($items)) : 0;
?>
<div class="row">
	
	<?php foreach($items as $i => $item): 
		$metadata = $item["metadata"];
		$labelClass = $item["labelClass"] ?? "text-subsection-blue";
		if($metadata == null)
		{
			continue;
		}
		
	?>
		<div class="sh-small-metadata col-sm-<?= $col_size; ?> <?= $i > 0 ? "border-left" : ""; ?>">
			<p class="sh-small-metadata-label <?=$labelClass; ?>"><b><?= $metadata->value; ?></b> <?= $metadata->metadataNameLabel(); ?></p>
		</div>
	<?php endforeach; ?>
</div>
