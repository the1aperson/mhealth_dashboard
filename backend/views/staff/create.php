<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\UserForm */


?>
<?php $this->beginBlock('header-content'); ?>
	<button class="back-button" id="roles-back" onclick="goBack()"><a class="icon-arrow-left_blue"></a>BACK</button>
	<h1 class='page-title'>New Staff Member</h1>
<?php $this->endBlock(); ?>

<div class="study-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
