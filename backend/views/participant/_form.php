<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $model common\models\Participant */
/* @var $form yii\widgets\ActiveForm */
$this->registerJsFile(Url::base() . '/js/change_background.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);

?>

<div class="participant-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'participant_id')->textInput(['maxlength' => true])->label('App ID') ?>
    <?= $form->field($model, 'password')->textInput(['maxlength' => true]) ?>
    
    <div class="form-group participant-form-buttons">
        <?= Html::submitButton('Save', ['class' => 'button-blue']) ?>
        <a href="<?= Url::to('/participants', true); ?>" class="button-light">Cancel</a>

    </div>

    <?php ActiveForm::end(); ?>

</div>
