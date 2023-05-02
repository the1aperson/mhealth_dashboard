<?php

/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
<?= Yii::$app->name; ?> Account Created

Hello <?= $user->first_name ?>,
An account on <?= Yii::$app->name ?> was just created for you. To finish setting up your account, you need to set your password.
Follow the link below to set your password:

<?= $resetLink ?>

The above link is only valid for the next <?= intval(Yii::$app->params['user.passwordResetTokenExpire'] / 3600); ?> hours. If you need to request a new one, you can do so at:

<?= Yii::$app->urlManager->createAbsoluteUrl(['/request-password-reset']); ?>