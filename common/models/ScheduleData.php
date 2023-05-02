<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "schedule_data".
 *
 * @property int $id
 * @property int $participant
 * @property int $device
 * @property string $blob_data
 * @property string $md5_hash
 * @property int $created_at
 *
 * @property ParticipantDevice $device0
 * @property Participant $participant0
 */
class ScheduleData extends AuditableModel
{
	const SCHEDULE_TYPE_SESSION_SCHEDULE = 'session_schedule';
	const SCHEDULE_TYPE_WAKE_SLEEP = 'wake_sleep_schedule';
	const SCHEDULE_TYPE_ARC_SCHEDULE = 'arc_schedule';
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'schedule_data';
    }
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
			'timestamp'  => [
	            'class' => TimestampBehavior::className(),
	            'attributes' => [
	                ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
	            ],
	        ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['participant', 'device', 'raw_type'], 'integer'],
            [['blob_data'], 'string'],
            [['schedule_type', 'md5_hash'], 'string', 'max' => 255],
            [['device'], 'exist', 'skipOnError' => true, 'targetClass' => ParticipantDevice::className(), 'targetAttribute' => ['device' => 'id']],
            [['participant'], 'exist', 'skipOnError' => true, 'targetClass' => Participant::className(), 'targetAttribute' => ['participant' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'participant' => 'Participant',
            'device' => 'Device',
            'schedule_type' => 'Schedule Type',
            'blob_data' => 'Blob Data',
            'raw_type' => 'Raw Type',
            'md5_hash' => 'Md5 Hash',
            'created_at' => 'Created At',
        ];
    }

	
	public static function isValidScheduleType($type)
	{
		$validTypes = [self::SCHEDULE_TYPE_ARC_SCHEDULE, self::SCHEDULE_TYPE_WAKE_SLEEP, self::SCHEDULE_TYPE_SESSION_SCHEDULE];
		return in_array($type, $validTypes);
	}
	
	// getLatestSchedule()
	// Retrieves the most recent schedule of the given type for the given participant
	public static function getLatestSchedule($participant_id, $type)
	{
		return ScheduleData::find()->where(['participant' => $participant_id, 'schedule_type' => $type])->orderBy('created_at desc')->one();
	}
	
	
	// If we're saving a new record, let's generate the md5 before saving.
	// If the record is begin updated, we have to check that the md5 hasn't
	// changed. We can't let people modify the stored data.
	
	public function beforeSave($insert)
	{
	    if (!parent::beforeSave($insert)) {
	        return false;
	    }
		
		if($insert)
		{
			$md5 = md5($this->blob_data);
			$this->md5_hash = $md5;
		}
		else
		{
			$md5 = md5($this->blob_data);
			if($this->md5_hash != $md5)
			{
				return false;
			}
		}
		
	    return true;
	}
	
	public function attributesToSkip()
	{
		$skip = parent::attributesToSkip();
		$skip []= 'blob_data';
		return $skip;
	}
	
}
