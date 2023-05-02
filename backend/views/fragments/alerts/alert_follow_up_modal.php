<?php
	use yii\helpers\Url;
	use yii\helpers\Html;
	use yii\widgets\ActiveForm;
	use yii\widgets\Pjax;
	
	use backend\models\FollowupForm;
	/* @var $alert */
	
	$alert_id = "alert-modal-id-" . $alert->id;
	
	$model = new FollowupForm();
	$model->alert_id = $alert->id;
	
?>
<div class="modal fade alert-follow-up-modal" id="<?= $alert_id; ?>" data-alert-id="<?= $alert->id; ?>" tabindex="-1" role="dialog" aria-labelledby="alert-follow-up-modal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
	        <?php Pjax::begin(['linkSelector' => false, 'clientOptions' => ['history' => false]]); ?>
				<?= $this->render('alert_follow_up_form', ['model' => $model, 'alert' => $alert]); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
