<?php
namespace common\fixtures;

use yii\test\ActiveFixture;

class StudyMetadataFixture extends ActiveFixture
{
    public $modelClass = 'common\models\StudyMetadata';
    public $depends = [ 'common\fixtures\StudyFixture' ];
}		