<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class UserAuditTrailFixture extends ActiveFixture
{
    public $modelClass = 'common\models\UserAuditTrail';
    public $depends = [ 'common\fixtures\UserFixture' ];
}		