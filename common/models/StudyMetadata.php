<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/* NOTE:
 * Most of the data stored in this table is managed by the StudyMetadataHandler component.
 * If you're looking to get data from this table, consider doing so through Yii::$app->studyMetadataHandler.
 */

/**
 * This is the model class for table "study_metadata".
 *
 * @property int $id
 * @property int $study_id
 * @property string $name
 * @property string $value
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Study $study
 */
class StudyMetadata extends \yii\db\ActiveRecord
{
	
	const TOTAL_PARTICIPANTS = "total_participants";
	const TOTAL_ACTIVE_PARTICIPANTS = "total_active_participants";
	const ENABLED_DEVICES = "enabled_devices";
	const RETENTION_PERCENT = "retention_percent";
	const ADHERENCE_PERCENT = "adherence_percent";
	const NOT_SEEN_COUNT = "not_seen_count";
	const MISSED_TEST_COUNT = "missed_test_count";
	const UPCOMING_SCHEDULE = "upcoming_schedule";
	const COMPLETED_STUDY_COUNT = "completed_study_count";
	const RECENTLY_INSTALLED_COUNT = "recently_installed_count";
	const CURRENT_PHASE_COUNT = "current_phase_count";
	const DROPPED_COUNT = "dropped_count";
	const TESTING_COUNT = "testing_count";
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'study_metadata';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
	            'class' => TimestampBehavior::className(),
				'skipUpdateOnClean' => false,
			]
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['study_id', 'created_at', 'updated_at'], 'integer'],
            [['name', 'value'], 'string'],
            [['study_id'], 'exist', 'skipOnError' => true, 'targetClass' => Study::className(), 'targetAttribute' => ['study_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'study_id' => 'Study ID',
            'name' => 'Name',
            'value' => 'Value',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


	public function metadataNameLabel()
	{
		switch($this->name)
		{
		case StudyMetadata::TOTAL_PARTICIPANTS:
			return "Total Participants";
		case StudyMetadata::ENABLED_DEVICES:
			return "Enabled Devices";
		case StudyMetadata::RETENTION_PERCENT:
			return "% Retention";
		case StudyMetadata::ADHERENCE_PERCENT:
			return "% Adherence";
		case StudyMetadata::NOT_SEEN_COUNT:
			return "Not Seen in 7 Days";
		case StudyMetadata::MISSED_TEST_COUNT:
			return "Missed Tests";
		default:
			return $this->generateAttributeLabel($this->name);
		}
	}
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudy()
    {
        return $this->hasOne(Study::className(), ['id' => 'study_id']);
    }
        
    
    public static function updateMetadata($study_id, $name, $value)
    {
	    $metadata = StudyMetadata::find()->where(['study_id' => $study_id, 'name' => $name])->one();
	    
	    if($metadata == null)
	    {
		    $metadata = new StudyMetadata();
		    $metadata->study_id = $study_id;
		    $metadata->name = $name;
	    }
	    
	    $metadata->value = strval($value);
	    if($metadata->save())
	    {
		    return $metadata;
	    }
	    
	    return null;
    }
    
    public static function getMetadata($study_id, $name)
    {
	    return StudyMetadata::find()->where(['study_id' => $study_id, 'name' => $name])->one();
    }
    
    public static function incrementMetadata($study_id, $name, $by = 1)
    {
	    $metadata = self::getMetadata($study_id, $name);
	    if($metadata == null)
	    {
		    $metadata = new StudyMetadata();
		    $metadata->study_id = $study_id;
		    $metadata->name = $name;
		    $metadata->value = 0;
	    }
	    
	    $value = intval($metadata->value);
	    $value += $by;
	    
	    $metadata->value = strval($value);
	    return $metadata->save();
    }
    
    public static function decrementMetadata($study_id, $name, $by = 1)
    {
	    return StudyMetadata::incrementMetadata($study_id, $name, $by * -1);
    }
    
    
}
