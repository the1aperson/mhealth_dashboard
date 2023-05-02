<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class ParticipantDroppedByFixture extends ActiveFixture
{
    public $modelClass = 'common\models\ParticipantDroppedBy';
    public $depends = [ 'common\fixtures\ParticipantFixture', 'common\fixtures\UserFixture' ];
}		