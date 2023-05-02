<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class ParticipantUserFlagFixture extends ActiveFixture
{
    public $modelClass = 'common\models\ParticipantUserFlag';
    public $depends = [ 'common\fixtures\ParticipantFixture', 'common\fixtures\UserFixture' ];
}		