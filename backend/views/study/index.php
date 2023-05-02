<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use common\models\Study;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Studies';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="study-index">

    <p>
        <?= Html::a('New Study', ['create'], ['class' => 'button-blue']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
	        
	        [
				'attribute' => 'name',
				'content' => function($model)
				{
					$url = Url::to(["/study/view", "id" => $model["id"]], true);
					return '<a href="'.$url.'">' . $model["name"] . "</a>";
				},
			],
            'start_date:date',
            'end_date:date',
            'created_at:datetime',
            [
                'attribute' => 'status',
                'value' => function($model)
                {
                    return $model["status"] == Study::STATUS_ACTIVE ? "Active" : "Inactive";
                }
            ],
        ],
    ]); ?>
</div>
