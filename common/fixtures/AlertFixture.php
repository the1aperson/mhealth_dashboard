<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class AlertFixture extends ActiveFixture
{
    public $modelClass = 'common\models\Alert';
    public $depends = [ 'common\fixtures\ParticipantFixture', 'common\fixtures\UserFixture' ];
}		