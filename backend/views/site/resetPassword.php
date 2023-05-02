<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\ResetPasswordForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Reset Password';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-reset-password col-sm-8 col-sm-offset-2 section">
	
    <div class="row">
        <div class="col-sm-offset-3 col-sm-6">
		    <h1><?= Html::encode($this->title) ?></h1>
			<p>An administrator has required that you reset your password.</p>
		    <p>Please choose a new password:</p>
		

            <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>

                <?= $form->field($model, 'password')->passwordInput(['autofocus' => true]) ?>

                <div class="form-group">
                    <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
                </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
