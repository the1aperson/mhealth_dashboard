<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class ParticipantMetadataFixture extends ActiveFixture
{
    public $modelClass = 'common\models\ParticipantMetadata';
    public $depends = [ 'common\fixtures\ParticipantFixture' ];
}		