<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

use common\models\User;
use common\models\StudyUserAuth;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = 'Staff Members';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('header-content'); ?>
	<!-- <button class="back-button" id="roles-back" onclick="goBack()"><a class="icon-arrow-left_blue"></a>BACK</button> -->
	<div class="button-list">
		<?= Html::a('New Staff Member<span class="icon-add-outline_white" id="new-staff-icon"></span>', ['create'], ['class' => 'button-blue', 'style' => 'float:right']); ?>
		<?= $this->render('/fragments/export/export_button', ['filterModel' => $searchModel, 'modal' => 'user', 'permissions' => ['viewUsers']] ); ?>
	</div>
<?php $this->endBlock(); ?>

<div class="user-index">

	<div class="section" id="research-staff-container">
			<p class="section-header">Researchers & Administrators</p>
			<div id="research-staff-grid">

    <?= GridView::widget([
		'dataProvider' => $dataProvider,
		'filterModel' => $searchModel,
		'summary' => '',
        'columns' => [
            			
			[
				'attribute' => 'name',
				'label' => 'Staff Member',
				'filterInputOptions' => [
					'class' => 'form-control',
					'placeholder' => 'Search'
				],
				'content' => function($model)
				{
					$url = Url::to(["/staff/view", "id" => $model["user_id"]], true);
					return '<a href="'.$url.'">' . $model["name"] . "</a>";
				}
			],			
			[
				'attribute' => 'email',
				'filterInputOptions' => [
					'class' => 'form-control',
					'placeholder' => 'Search'
				],
			],
			[
				'attribute' => 'status',
				'filter' => $searchModel->getFilterOptions('status'),
				'filterInputOptions' => [
					'class' => 'form-control',
				],
				'value' => function($model)
				{
					return $model["status"] == User::STATUS_ACTIVE ? "Active" : "Inactive";
				}
			],
			[
				'label' => 'Role',
				'attribute' => 'permissions',
				'filter' => $searchModel->getFilterOptions('permissions'),
				'filterInputOptions' => [
					'class' => 'form-control'
				],
				'value' => function($model)
				{
					$auths = StudyUserAuth::getAssignmentsForUser($model["user_id"]);
					
					if(count($auths) == 0)
					{
						return "None";
					}
					
					$roles = [];
					foreach($auths as $auth)
					{
						$study_name = $auth->getStudyName();
						$role_name = $auth->auth_item_name;
						$roles []= "$study_name - $role_name";
					}
					
					$roles = implode("<br />", $roles);
					return $roles;
				},
				'format' => 'html',
			],
			[
				'label' => 'Remove',
				'content' => function($model)
				{
					$anchor = Html::a('REMOVE', ['delete', 'id' => $model["user_id"]], ['class'=> 'remove-item',
								
					'data-confirm' => 'Are you sure you want to remove this user?',
					'data-method' => 'post',
					]);
					return $anchor;
				},
				
			]

            //'status',
            //'created_at',
            //'updated_at',

        ],
    ]); ?>
</div>
</div>
		</div>
