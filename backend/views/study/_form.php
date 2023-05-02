<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\components\DateTimeWidget;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\Study */
/* @var $form yii\widgets\ActiveForm */
$this->registerJsFile(Url::base() . '/js/change_background.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);
?>
<div class="new-study-header">
	<button class="back-button" onclick="goBack()"><a class="icon-arrow-left_blue"></a>BACK</button>
	<h2 class='page-title'>New Study</h2>
</div>

<div class="study-form">
<br />
    <?php $form = ActiveForm::begin(); ?>
	
	<div class="row">
		<div class="col-md-8">
		    <?= $form->field($model, 'name')->textInput(['maxlength' => true])->label('Study Name') ?>
		</div>
	</div>

	<?= DateTimeWidget::widget([
        'form' => $form,
        'model' => $model,
        'name' => 'start_date',
        'datetime' => $model->start_date,
        'showTime' => false,
    ]); ?>
    
	<?= DateTimeWidget::widget([
        'form' => $form,
        'model' => $model,
        'name' => 'end_date',
        'datetime' => $model->end_date,
        'showTime' => false,
    ]); ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'button-blue']) ?>
        <a href="<?= Url::to("/select-study", true); ?>" class="button-light">Cancel</a>
    </div>

    <?php ActiveForm::end(); ?>

</div>
