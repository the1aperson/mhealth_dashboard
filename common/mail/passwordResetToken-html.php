<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
<h2><?= Yii::$app->name; ?> Password Reset Request</h2>
<p>Hello <?= Html::encode($user->first_name) ?>,</p>

<p>You recently requested to reset your password for your <?= Yii::$app->name ?> dashboard account. <br />
If you want to change your password, please click below:</p>
<p>
<a href="<?= $resetLink; ?>" style="height: 24px; min-width: 100px; padding: 8px 36px; border-radius: 40px; display: inline-block; text-align: center; font-size: 17px; line-height: 23px; text-decoration: none; cursor: pointer; background: #005F85; color: white; border: 1px solid #005F85;">Reset Password</a>
</p>
<p>If you cannot view or click on the button above, please copy and paste the following url into your browser:<br />
<?= $resetLink ?>
</p>
<p><b>The above link is only valid for the next <?= intval(Yii::$app->params['user.passwordResetTokenExpire'] / 3600); ?> hours.</b><br /> If you need to request a new one, you can do so at:</p>
<p>
<?= Yii::$app->urlManager->createAbsoluteUrl(['/request-password-reset']); ?>
</p>
<p>If you did  not request a password reset, you can ignore this email.</p>
