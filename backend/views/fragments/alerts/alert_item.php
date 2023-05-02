<?php
	
	/* @var $alert common\models\Alert ?*/
	
	use yii\helpers\Url;
	use yii\widgets\Pjax;
	use yii\widgets\Block;
	use yii\widgets\ActiveForm;
	use yii\helpers\Html;
	use backend\models\FollowupForm;
	use common\components\DateFormatter;
	
	$model = new FollowupForm;
	$model->alert_id = $alert->id;
	$alert_id = "alert-item-id-" . $alert->id; 

	$show_close = $show_close ?? true;
	$show_modal = $show_modal ?? true;
	$this->registerJsFile(Url::base() . '/js/alert.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);

	$this->registerJs(
		'$("document").ready(function(){ 
			$(document).on("change","#follow-check-'.$alert->id.'", function() {
				$("#no-followup-'.$alert->id.'").submit();				
			});
		});'
	);
?>

<?php Pjax::begin(['linkSelector' => false, 'formSelector' => false, 'clientOptions' => ['history' => false, 'push' => false], 'options' => ['data-pjax-url' => Url::to(['/alert/render-alert', 'id' => $alert->id],true)]
]); ?>
<div id="<?= $alert_id; ?>" class="alert-item alert-item-<?= $alert->alertLevelString(); ?> clearfix">
	<div class="alert-image pull-left alert-image-<?= $alert->alertLevelString(); ?>"></div>
	<div class="alert-item-content pull-left">
		<div class="alert-item-date">
			<?= DateFormatter::abbreviatedDate($alert->created_at); ?>
		</div>
		<div class="alert-item-alert-message">
			<?= $alert->getParsedMessage(); ?>
		</div>
	</div>

	<div class="alert-item-actions pull-right">
		<?php if($alert->requires_follow_up && $alert->follow_up_by == null): ?>
			<?php if($show_close): ?>
				<?php if(Yii::$app->user->can('manageAlerts')): ?>
					<?php Pjax::begin(['linkSelector' => false, 'clientOptions' => ['history' => false]]); ?>
						<?php $form = ActiveForm::begin([
							'options' => ['data' => ['pjax' => true, 'alert-id' => $alert->id], 'class' => 'no-follow-up-form', 'id' => 'no-followup-'.$alert->id ],
							'action' => Url::to(['/alert/no-followup', 'id' => $alert->id], true)
							]); ?>
						<?= $form->field($model, 'no_followup', ['template' => "<div class=\"follow-up\">\n{input}\n{label}\n</div>"])->checkbox(['class'=>'follow-up-checkbox', 'id' => "follow-check-$alert->id" ], false)->label("NO FOLLOW UP NEEDED", ['class'=>'log-label']); ?>
						<?php ActiveForm::end(); ?>
					<?php Pjax::end(); ?>

					<div class="alert-log-follow-up alert-section-link link-style cursor border-left" data-target="#alert-modal-id-<?= $alert->id; ?>" data-toggle="modal">Log Follow Up</div>
						
					<?php if($alert->follow_up_message != null): ?>
						<div class="alert-item-follow-up-msg pull-left" style="display: none;">
							<p class="alert-item-follow-up-date"><?= DateFormatter::abbreviatedDate($alert->follow_up_date); ?></p>
							<p>
								<?= $alert->follow_up_message; ?> by <?= $alert->getFollowUpBy()->one()->shortDisplayName(); ?>
							</p>
						</div>
					<?php endif; ?>
					<span class="icon-close_blue alert-item-hide pull-left" style="display: none;" data-url="<?= Url::to("/alert/clear/" . $alert->id, true); ?>" data-target="#<?= $alert_id; ?>"></span>
				<?php else: ?>
					<div class="alert-log-follow-up">Follow Up Required</div>
				<?php endif; ?>
			<?php endif; ?>
		<?php else: ?>
			<?php if($alert->follow_up_message != null): ?>
			<div class="alert-item-follow-up-msg pull-left">
				<p class="alert-item-follow-up-date"><?= DateFormatter::abbreviatedDate($alert->follow_up_date); ?></p>
				<p>
					<?= $alert->follow_up_message; ?> by <?= $alert->getFollowUpBy()->one()->shortDisplayName(); ?>
				</p>
			</div>
			<?php endif; ?>
			<?php if($show_close): ?>
				<span class="icon-close_blue alert-item-hide pull-left" data-url="<?= Url::to("/alert/clear/" . $alert->id, true); ?>" data-target="#<?= $alert_id; ?>"></span>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>
<?php Pjax::end(); ?>

<?php if( count(Block::$stack) == 0 && $show_modal && $alert->requires_follow_up && $alert->follow_up_by == null): ?>
	<?php $this->beginBlock('modal-alert-' . $alert->id); ?>
	<?= $this->render('alert_follow_up_modal', ['alert' => $alert]); ?>
	<?php $this->endBlock(); ?>
<?php endif; ?>
