<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class ParticipantNoteFixture extends ActiveFixture
{
    public $modelClass = 'common\models\ParticipantNote';
    public $depends = [ 'common\fixtures\ParticipantFixture', 'common\fixtures\UserFixture' ];
}		