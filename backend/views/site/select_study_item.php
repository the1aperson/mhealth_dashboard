<?php

use yii\helpers\Url;
use common\models\Alert;
use yii\helpers\Html;

use common\components\DateFormatter;
use common\models\StudyUserAuth;	
	/* @var $study common\models\Study */

$userId = Yii::$app->user->getId();
$auths = StudyUserAuth::getAssignmentsForUser($userId);
?>
<?php $this->beginBlock('header-content'); ?>
	<div class="pull-right">	
		<p>
			<?= Html::a('New Study<span class="icon-add-outline_white" id="new-study-icon"></span>', ['/study/create'], ['class' => 'button-blue', 'id' => 'study-create-button']) ?>
		</p>
	</div>
<?php $this->endBlock(); ?>

<div class="select-study-section">
	<h2 class="select-study-name"><?= $study->name; ?></h2>
	<div class="clearfix">
		<div class="pull-left">
			<p><span class="icon-calendar_gray calendar_align" style="padding-right: 25px; vertical-align: text-top;"></span><?= date( "F Y" ,$study->start_date); ?><span class="icon-arrow-right_blue" style="vertical-align: text-bottom; margin:0 5px 0 5px;"></span><?= date("F Y",$study->end_date); ?></p>
			<p class="study-dates">Created: <span><?= date( "F j Y, h:i:s A" ,$study->created_at); ?></span></p>
			<p class="study-dates">Updated: <span><?= date( "F j Y, h:i:s A" ,$study->updated_at); ?></span></p>
		</div>
		<div class="pull-right">
			<div class="col-sm-4 border-left">
				<div class="study-details" style="margin-left: -10px;">New Alerts</div>
				<p class="study-details-amount" style="margin-left: -10px;"><?= Yii::$app->studyMetadataHandler->getMetadata($study->id, 'daily_alerts')->value ?? 0; ?></p>
			</div>
			<div class="col-sm-4 border-left">
				<div class="study-details">Adherence</div>
				<p class="study-details-amount"><?= Yii::$app->studyMetadataHandler->getMetadata($study->id, 'adherence_percent')->value ?? 0; ?>%</p>
			</div>
			<div class="col-sm-4 border-left">
				<div class="study-details">Retention</div>
				<p class="study-details-amount"><?= Yii::$app->studyMetadataHandler->getMetadata($study->id, 'retention_percent')->value ?? 0; ?>%</p>
			</div>
		</div>
	</div>
		<hr id="linebreak">
		<div class="select-study-lower">
			<div class="pull-left">
				<?= $this->render('/fragments/export/export_button', ['modal' => 'site', 'modalId' => 'export-data-modal-' . $study->id, 'buttonStyle' => 'light', 'permissions' => ['viewStudyHealthData'], 'permissionParams' => ['study_id' => $study->id], 'additionalParams' => ['select_study_id' => $study->id]]); ?>
				<a href="<?= Url::to(["/study/view", "id" => $study->id], true);?>"><span class="icon-admin_blue" data-html="true" data-toggle="tooltip" data-placement="top" title="Edit Study Settings"></span></a>
				<?php foreach($auths as $auth): ?>
					<?php if(($auth->getStudyName() == $study->name) || ($auth->getStudyName() == 'All Studies')): ?>
						<div class="role-name" data-html="true" data-toggle="tooltip" data-placement="top" title="<b>You have access to this study as <?= $auth->auth_item_name; ?>.<b/><p style='font-weight:normal;'> The pages you can see and actions you can take within a study are determined by your role.</p>">
							<p><strong>Role:</strong> <?= $auth->auth_item_name;?></p>
						</div>
					<?php endif;?>
				<?php endforeach; ?>
			</div>
			<div  class="pull-right">
				<a href="<?= Url::to(["/select-study", "study_id" => $study->id], true); ?>" class="button-blue">View Details <span class="icon-arrow-right_white"></span></a>
			</div>
		</div>
	
	<br />
</div>
