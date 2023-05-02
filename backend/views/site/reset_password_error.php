<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\ResetPasswordForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;


$this->title = 'Reset Password';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-reset-password col-sm-8 col-sm-offset-2 section">
	
    <div class="row">
        <div class="col-sm-offset-3 col-sm-6">
		    <h1><?= Html::encode($this->title) ?></h1>
			<br />
		    <p class="text-error-red">Your password reset token is either invalid, or expired.</p>
			<p><a href="<?= Url::to('/request-password-reset', true); ?>">Click here</a> to request a new one.</p>

        </div>
    </div>
</div>
