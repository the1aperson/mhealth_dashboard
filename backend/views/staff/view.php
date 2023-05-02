<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile(Url::base() . '/js/change_background.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);

?>
<?php $this->beginBlock('header-content'); ?>
	<button class="back-button" id="roles-back" onclick="goBack()"><a class="icon-arrow-left_blue"></a>BACK</button>
	<h1 class='page-title'><?= $model->displayName(); ?></h1>
<?php $this->endBlock(); ?>

<div class="user-view">

	<p>Username:<br />
		<b><?= $model->username; ?></b>
	</p>
	<p>Email:<br />
		<b><?= $model->email; ?></b>
	</p>

	<p>Status:<br />
		<b><?= $model->getStatusLabel(); ?></b>
	</p>
	
	<p>Studies & Permissions:<br />
		<?php foreach($auth_roles as $auth_role): ?>
		<b><?= $auth_role->getStudyName(); ?> - <?= $auth_role->auth_item_name; ?></b><br />
		<?php endforeach; ?>
	</p>
    <p>
		<?php if(Yii::$app->user->can('modifyUsers')): ?>
		<div class='staff-buttons'>
        <?= Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'button-light']) ?>
        
        <!-- <?= Html::a('Require Password Reset', ['require-password-reset', 'id' => $model->id], [
            'class' => 'button-blue',
            'data' => [
                'confirm' => 'Require user to reset password on next login?',
            ],
        ]) ?> -->
		
			<?php //if($model->id != Yii::$app->user->getId()): // we probably shouldn't let people delete themselves. ?>
				<?php //if($model->status == User::STATUS_ACTIVE): ?>
			        <?= Html::a('Remove', ['delete', 'id' => $model->id], [
			            'class' => 'button-light',
			            'data' => [
			                'confirm' => 'Are you sure you want to remove this user?',
			                'method' => 'post',
			            ],
					]) ?>
					<hr />

			    <?php //else : ?>
					<!-- <?= Html::a('Re-activate', ['reactivate', 'id' => $model->id], [
			            'class' => 'button-light',
			            'data' => [
			                'confirm' => 'Are you sure you want to re-activate this user?',
			                'method' => 'post',
			            ],
			        ]) ?> -->
		        <?php //endif; ?>
			<?php //endif; ?>
		</div>
		<?php endif; ?>

		<?= Html::a('Done', ['index'], ['class' => 'button-blue']) ?>

    </p>
    
</div>
