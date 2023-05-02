<?php
use yii\helpers\Html;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\MessageInterface the message being composed */
/* @var $content string main view render result */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
	<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center" style="width:100%;background-color:#F0F4FA; font-family:Helvetica,Arial,sans-serif; color: #272D33" bgcolor="#F0F4FA">
	<tbody>
	<tr>
		<td>
			<table cellpadding="0" cellspacing="0" border="0" width="600" align="center" bgcolor="#F0F4FA" style="border-collapse:collapse;width:600px;background-color:#F0F4FA; margin-top: 30px; margin-bottom: 30px;">
				<tbody>
					<tr>
						<td width="600" bgcolor="#ffffff" style="background-color:#ffffff; padding: 30px;">
    <?php $this->beginBody() ?>
    <?= $content ?>
    <?php $this->endBody() ?>
    						</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
	</tbody>
</table>

</body>
</html>
<?php $this->endPage() ?>
