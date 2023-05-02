<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class HiddenAlertFixture extends ActiveFixture
{
    public $modelClass = 'common\models\HiddenAlert';
    public $depends = [ 'common\fixtures\AlertFixture', 'common\fixtures\UserFixture' ];
}		