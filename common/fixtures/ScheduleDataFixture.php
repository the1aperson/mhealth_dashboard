<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class ScheduleDataFixture extends ActiveFixture
{
    public $modelClass = 'common\models\ScheduleData';
    public $depends = [ 'common\fixtures\ParticipantDeviceFixture', 'common\fixtures\ParticipantFixture' ];
}		