<?php
	
	/* @var $note common\models\ParticipantNote ?*/
	
	use yii\helpers\Url;
	use common\components\DateFormatter;	
	
?>

<?php	
	$note_id = "note-item-id-" . $note->id; 
?>

<div id="<?= $note_id; ?>" class="note-item">
	<p>
		<b><?= $note->getCreatedBy()->displayName(); ?></b><br />
		<span class="font-small"><?=  DateFormatter::abbreviatedDate($note->created_at); ?></span>
	</p>
	<p class="text-pre-line"><?= $note->note; ?></p>
</div>