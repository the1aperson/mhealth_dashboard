<?php
use common\models\Study;

/* @var $this yii\web\View */

$this->title = 'All Studies';
?>

<?php foreach($studies as $study): ?>
	<?php if($study->status == Study::STATUS_ACTIVE): ?>
		<div class="row">
			<div class="col-sm-6">
				<?= $this->render("select_study_item", ['study' => $study]); ?>
			</div>
 		</div>
	<?php endif;?>
<?php endforeach; ?>