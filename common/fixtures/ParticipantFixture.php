<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class ParticipantFixture extends ActiveFixture
{
    public $modelClass = 'common\models\Participant';
    public $depends = ['common\fixtures\StudyFixture'];
}		