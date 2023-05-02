<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class ExportQueueFixture extends ActiveFixture
{
    public $modelClass = 'common\models\ExportQueue';
    public $depends = [ 'common\fixtures\UserFixture' ];
}		