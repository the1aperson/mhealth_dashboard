<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;

AppAsset::register($this);
	$this->registerJsFile(Url::base() . '/js/sitewide.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);
	$hide_navbar = 	($_COOKIE["hide_navbar"] ?? false) == "true";

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="<?= $hide_navbar ? "hide-navbar" : ""; ?>" >
<?php $this->beginBody() ?>

<?= $this->render("topNavigation"); ?>

<?= $this->render("leftNavigation"); ?>

<div id="content-wrapper" class="leftnav-offset topnav-offset">
	<div id="main-header">
		<div id="main-header-content">
			<div class="row">
				<div class="col-lg-11 col-sm-12">
						
					<div class="pull-left">
						<h1 class="page-title"><?= $this->title; ?></h1>
					</div>
					
					<?php
						if(isset($this->blocks["header-content"]))
						{
							echo $this->blocks["header-content"];
						}
					?>
				</div>
			</div>
		</div>

	</div>
	
	<div id="main-content">
	    <?= Alert::widget() ?>
	    <?= $content ?>
	</div>
</div>

<?php 
	if(isset($this->blocks)):
		foreach($this->blocks as $blockname => $blockContent): ?>
	<?php
		if(strstr($blockname, "modal"))
		{
			echo $blockContent;
		}
	?>
	<?php endforeach; 
		endif;
	?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
