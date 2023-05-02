<?php


/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
<?= Yii::$app->name; ?> Password Reset Request

Hello <?= $user->first_name ?>,

You recently requested to reset your password for your <?= Yii::$app->name ?> dashboard account.
Follow the link below to reset your password:

<?= $resetLink ?>

The above link is only valid for the next <?= intval(Yii::$app->params['user.passwordResetTokenExpire'] / 3600); ?> hours. If you need to request a new one, you can do so at:

<?= Yii::$app->urlManager->createAbsoluteUrl(['/request-password-reset']); ?>


If you did  not request a password reset, you can ignore this email.