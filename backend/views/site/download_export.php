<?php

	use common\models\ExportQueue;
	use yii\helpers\Url;
	
	$this->title = "Download Export";
	
	if($export->status == ExportQueue::STATUS_PROCESSING || $export->status == ExportQueue::STATUS_NEW)
	{
		$reloadTimeout = min(30, max(2, (time() - $export->created_at))) * 1000;
		$this->registerJs("setTimeout(function(){location.reload();}, $reloadTimeout);");
	}
?>

<div class="row">
	<div class="col-sm-8">
		<div class="section">
			
			<?php if($export->status == ExportQueue::STATUS_FINISHED): ?>
				<?php if(file_exists($export->filepath) == false): ?>
					This export file has either expired, or been deleted. <a href="<?= Url::to('/export-data', true); ?>">Click here to generate a new export</a>.
				<?php else: ?>
					Your export is ready for download! <a href="<?= Url::to(["/download-export", "id" => $export->id], true); ?>">Click here to download it.</a>
				<?php endif; ?>
			<?php elseif($export->status == ExportQueue::STATUS_ERROR): ?>
			Something went wrong with your export. Contact an administrator.
			<?php elseif($export->status == ExportQueue::STATUS_PROCESSING): ?>
			Your export is processing. Depending on the size of the export, this may take several minutes to complete. <br />
			Current progress: <?= $export->progress_msg; ?> <br />
			This page will refresh periodically, or you can <a href="<?= Url::current([], true); ?>">click here</a> to refresh.
			<?php else: ?>
			Your export is still processing. Depending on the size of the export, this may take several minutes to complete. <br />
			This page will refresh periodically, or you can <a href="<?= Url::current([], true); ?>">click here</a> to refresh.
			<?php endif; ?>
			
			
		</div>
	</div>
</div>