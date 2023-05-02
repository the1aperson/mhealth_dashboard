<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class ParticipantDeviceFixture extends ActiveFixture
{
    public $modelClass = 'common\models\ParticipantDevice';
    public $depends = [ 'common\fixtures\ParticipantFixture' ];
}		