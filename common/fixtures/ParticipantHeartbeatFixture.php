<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class ParticipantHeartbeatFixture extends ActiveFixture
{
    public $modelClass = 'common\models\ParticipantHeartbeat';
    public $depends = [ 'common\fixtures\ParticipantDeviceFixture', 'common\fixtures\ParticipantFixture' ];
}		