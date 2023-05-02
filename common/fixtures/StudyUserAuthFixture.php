<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class StudyUserAuthFixture extends ActiveFixture
{
    public $modelClass = 'common\models\StudyUserAuth';
    public $depends = [ 'common\fixtures\StudyFixture', 'common\fixtures\UserFixture' ];
}		