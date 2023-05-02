<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class ParticipantAuditTrailFixture extends ActiveFixture
{
    public $modelClass = 'common\models\ParticipantAuditTrail';
    public $depends = [ 'common\fixtures\ParticipantFixture' ];
}		