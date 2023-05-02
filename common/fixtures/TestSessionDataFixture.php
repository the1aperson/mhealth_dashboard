<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class TestSessionDataFixture extends ActiveFixture
{
    public $modelClass = 'common\models\TestSessionData';
    public $depends = [ 'common\fixtures\ParticipantDeviceFixture', 'common\fixtures\ParticipantFixture' ];
}		