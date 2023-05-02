<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Participant */

// $this->title = 'New Participant';
$this->params['breadcrumbs'][] = ['label' => 'Participants', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
	<button class="back-button" id="roles-back" onclick="goBack()"><a class="icon-arrow-left_blue"></a>BACK</button>
	<h1 class='page-title'>New Participant</h1>

<div class="participant-create">

    <h1><?= Html::encode($this->title) ?></h1>

	<div class="row">
		<div class="col-sm-4">
		    <?= $this->render('_form', [
		        'model' => $model,
		    ]) ?>
		</div>
	</div>

</div>
