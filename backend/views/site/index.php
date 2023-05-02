<?php

/* @var $this yii\web\View */

use yii\helpers\Url;
use common\models\StudyUserAuth;	

$userId = Yii::$app->user->getId();
$auths = StudyUserAuth::getAssignmentsForUser($userId);
$keys = array_keys($auths);

if (Yii::$app->study->isStudySet())
{
	$study = Yii::$app->study->getStudy();
	if($study != null)
	{
		$studyName = $study->name;
	}
}

$this->title = 'Overview';
?>

<?php $this->beginBlock('header-content'); ?>
<?php foreach($auths as $auth): ?>
		<?php if(($auth->getStudyName() == $study->name) || ($auth->getStudyName() == 'All Studies')): ?>
				<div class="role-name "><p><strong>Role:</strong> <?= $auth->auth_item_name;?></p></div>
		<?php endif;?>
	<?php endforeach; ?>
	<?= $this->render('/fragments/export/export_button', ['modal' => 'site', 'permissions' => ['viewStudyHealthData']]); ?>
<?php $this->endBlock(); ?>

<div class="row">
	
	<div class="col-sm-8">
		<?= $this->render("sections/study_health", $study_health); ?>
	</div>
	
	<div class="col-sm-4 col-lg-3">
		<?php if(Yii::$app->user->can('manageAlerts') || Yii::$app->user->can('viewAlerts')): ?>
		<?= $this->render("sections/alerts_overview", ["alertCounts" => $alertCounts]); ?>
		<?php endif; ?>
	</div>
</div>
<div class="row">
	<div class="col-sm-8">
		<?= $this->render("sections/enrollment_snapshot", $enrollment_snapshot); ?>
	</div>
</div>
