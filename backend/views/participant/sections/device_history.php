<?php

use yii\helpers\Html;
use yii\helpers\Url;

use yii\grid\GridView;

use common\components\DateFormatter;


/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel backend\models\ParticipantSearch */

?>


<div class="section" id="device-history-container">
    <p class="section-header">Device History</p>
    <div id="device-histroy-grid">
       <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => '',
            'options' => ['class' => 'grid-view device-list'],
            'layout' => '{items}',
            'columns' => [
                [
                    "attribute" => "updated_at",
                    'content' => function ($model, $key, $index, $column)
                    {
                        $date = date('m/d/y H:i:s', $model['updated_at']);
                        if($date == null)
                        {
                            return "(not set)";
                        }
                return $date;
                    }
                ],
                [
                    "attribute" => "device_id",
                    "label" => "Device ID"
                ],
                [
                    "attribute" => "os_version",
                    "label" => "OS Version"
                ],
                "app_version"
                ]
            ]);?>
    </div>
</div>