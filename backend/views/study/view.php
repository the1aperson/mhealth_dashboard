<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;
use common\models\Study;

/* @var $this yii\web\View */
/* @var $model common\models\Study */

$this->registerJsFile(Url::base() . '/js/change_background.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);
$this->params['breadcrumbs'][] = ['label' => 'Studies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<button class="back-button" onclick="goBack()"><a class="icon-arrow-left_blue"></a>BACK</button>
<div class="study-view">

    <h2 id="study-settings-title">Study Settings: <?= Html::encode($model->name) ?></h1>

    <p>Name:<br />
        <b><?= $model->name; ?></b>
    </p>
    <p>Start Date:<br />
        <b><?= date('M j, Y', $model->start_date); ?></b>
    </p>

    <p>End Date:<br />
        <b><?= date('M j, Y', $model->end_date); ?></b>
    </p>

    <p>Created At:<br />
        <b><?= date('M j, Y, H:i:s', $model->created_at); ?></b>
    </p>

    <p>Updated At:<br />
        <b><?= date('M j, Y, H:i:s', $model->updated_at); ?></b>
    </p>

	<p>Status:<br />
		<b><?= $model->getStatusLabel(); ?></b>
	</p>


    <p>
		<?= Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'button-light']) ?>
		<?php if($model->status == Study::STATUS_ACTIVE): ?>
			<?= Html::a('Delete Study', ['delete', 'id' => $model->id], ['class' => 'button-light',
			'data' => [
					'confirm' => 'Are you sure you want to delete this study?',
					'method' => 'post',
				],]) ?>
		<?php else:?>
			<?= Html::a('Reactivate Study', ['reactivate', 'id' => $model->id], ['class' => 'button-light',
				'data' => [
						'confirm' => 'Are you sure you want to reactivate this study?',
						'method' => 'post',
				],]) ?>
		<?php endif; ?>
    </p>

</div>
