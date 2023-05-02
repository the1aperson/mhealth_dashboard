<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class ParticipantTestSessionFixture extends ActiveFixture
{
    public $modelClass = 'common\models\ParticipantTestSession';
    public $depends = [ 'common\fixtures\ParticipantFixture' ];
}		