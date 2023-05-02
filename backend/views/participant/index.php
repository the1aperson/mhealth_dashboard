<?php

use yii\helpers\Html;
use yii\helpers\Url;
use common\models\Participant;
use yii\grid\GridView;

use common\components\DateFormatter;


/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel backend\models\ParticipantSearch */
$this->title = 'Participants';

?>
<?php $this->beginBlock('header-content'); ?>
	<?= $this->render('/fragments/export/export_button', ['filterModel' => $searchModel, 'modal' => 'participant_tracking', 'additionalParams' => ['formatterOptions[combineParticipants]'=> 1, 'formatterOptions[combineSessions]'=> 1,]]); ?>
	<?php if(Yii::$app->user->can('createParticipants')): ?>
		<p id="add-participant">
			<?= Html::a('New Participant<span class="icon-add-outline_white" id="add-icon"></span>', ['create'], ['class' => 'button-blue pull-right', 'id' => 'new-participant']) ?>
		</p>
    <?php endif; ?>
	<?php $this->endBlock(); ?>


<div class="participant-index">

	<?= $this->render('sections/search_filter_section', ['filterModel' => $searchModel, 'dataProvider' => $dataProvider]); ?>

</div>
<?php /* closing main-content div */ ?>
</div>
	<div id="participant-list-container" class="">
		<p class="section-header">Participant Data</p>
		<div id="participant-list-wrap">
		    <?= GridView::widget([
		        'dataProvider' => $dataProvider,
		        'filterModel' => $searchModel,
				'summary' => '',
				'options' => ['class' => 'grid-view pull-left participant-list-static'],
				'layout' => '{items}',
		        'columns' => [
					
					[
		            	'attribute' => 'flagged',
		            	'filter' => $searchModel->getFilterOptions('flagged'),
		            	'content' => function($data)
		            	{
			            	$flag_class = ($data["flagged"] == 1) ? "icon-flag_filled" : "icon-flag_empty";
			            	$flag_endpoint = ($data["flagged"] == 1) ? "unflag" : "flag";
			            	
			            	if(Yii::$app->user->can('flagParticipants'))
			            	{
				            	$url = Url::to(["/participant/" . $flag_endpoint, "id" => $data["id"]], true);
				            	return '<a href="' . $url . '" class="' . $flag_class . '"></a>';
			            	}
			            	else
			            	{
				            	return '<span class="'. $flag_class . '"></span>';
			            	}
		            	}
					],
					[
						'attribute' => 'participant_id',
                        'label' => 'Participant ID',
						'content' => function($model)
						{
							$url = Url::to(["/participant/view", "id" => $model["id"]], true);
							return '<a href="'.$url.'">' . $model["participant_id"] . "</a>";
						},
					],
					]
				]); ?>
					
					<?= GridView::widget([
		        'dataProvider' => $dataProvider,
		        'filterModel' => $searchModel,
				'summary' => '',
				'options' => ['class' => 'grid-view participant-list-scroll', 'data-simplebar' => ''],
				'layout' => '{items}',
		        'columns' => [
					
					[
						'attribute' => 'adherence',
						'value' => function($data)
						{
							return $data["adherence"] . "%";
						}
					],
					
					[
						'attribute' => 'thoughts_of_death',
						'value' => function($data)
						{
							return $data["thoughts_of_death"] . "%";
						}
					],
					

		            'install_count',
		
					[
			            'attribute' => 'os_type',
						'filter' => $searchModel->getFilterOptions('os_type'),
						'value' => function($model){
							$active = $model['device_active'];
							if($active == 'No'){
								return '(not set)';
							}
							return $model['os_type'];
						}
		            ],
		
		            [
			            'attribute' => 'os_version',
                        'label' => 'OS Version',
						'filter' => $searchModel->getFilterOptions('os_version'),
						'value' => function($model){
							$active = $model['device_active'];
							if($active == 'No'){
								return '(not set)';
							}
							return $model['os_version'];
						}
		            ],
		            
		            [
			            'attribute' => 'app_version',
						'filter' => $searchModel->getFilterOptions('app_version'),
						'value' => function($model){
							$active = $model['device_active'];
							if($active == 'No'){
								return '(not set)';
							}
							return $model['app_version'];
						}
		            ],
		            
		            [
		            	'attribute' => 'device_active',
						'filter' => $searchModel->getFilterOptions('device_active'),
						'format' => 'raw',
						'value' => function($model){
							$active = $model["device_active"];
							$participant = Participant::find()->where(['participant_id' => $model['participant_id']])->one();
							$device = $participant->getMostRecentDevice();
							if($device != null){
								$modal = $this->render('sections/device_disable_modal', ['participant' => $participant, 'device' => $device]);
								if($active == 'Yes')
								{
									return $active . ' ' . '<a class="grid-link"  data-toggle="modal" href="#device-disable-modal-'.$model['id'] .'">DISABLE</a>' . $modal;
								} else{ 
									return $active . ' ' . '<a class="grid-link" data-toggle="modal" href="#device-disable-modal-'.$model['id'] .'">ENABLE</a>' . $modal;
								}
	
							};
						}
					],
					
					 [
			            	'attribute' => 'status',
			            	'filter' => $searchModel->getFilterOptions('status'),
					],
					
					[
	                    'attribute' => 'device_created',
	                    'content' => function ($model, $key, $index, $column)
	                    {
		                    $date = $model["device_created"];
		                    if($date == null)
		                    {
			                    return "(not set)";
		                    }
		                    return DateFormatter::abbreviatedDate($date);
	                    }
                    ],
                    
		            [
	                    'attribute' => 'last_seen',
	                    'content' => function ($model, $key, $index, $column)
	                    {
		                    $date = $model["last_seen"];
		                    if($date == null)
		                    {
			                    return "(not set)";
		                    }
		                    return DateFormatter::abbreviatedDate($date);
	                    }
                    ],

		            [
		            	'attribute' => 'study_phase',
		            	'filter' => $searchModel->getFilterOptions('study_phase'),
					],
					
					[
	                    'attribute' => 'last_session_date',
	                    'content' => function ($model, $key, $index, $column)
	                    {
		                    $date = $model["last_session_date"];
		                    if($date == null)
		                    {
			                    return "(not set)";
		                    }
		                    return DateFormatter::abbreviatedDate($date);
	                    }
                    ],
                                        
                    [
	                    'attribute' => 'next_session_date',
	                    'content' => function ($model, $key, $index, $column)
	                    {
		                    $date = $model["next_session_date"];
		                    if($date == null)
		                    {
			                    return "(not set)";
		                    }
		                    return DateFormatter::abbreviatedDate($date);
	                    }
                    ],
		
		        ],
		    ]); ?>
		    

		</div>
	</div>
	<div class="col-sm-12">
	    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
		'summary' => '',
		'options' => ['class' => 'grid-view participant-list-pager'],
		'layout' => '{pager}',
        'columns' => []]); ?>
	</div>
<?php /* re-open main-content div */ ?>
<div>
