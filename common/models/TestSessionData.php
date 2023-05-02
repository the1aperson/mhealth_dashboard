<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "test_session_data".
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
class TestSessionData extends AuditableModel
{
	
	const RAW_TYPE_JSON = 10;
	const RAW_TYPE_ZIP = 20;
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'test_session_data';
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
            [['md5_hash'], 'string', 'max' => 255],
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
            'blob_data' => 'Blob Data',
            'md5_hash' => 'Md5 Hash',
            'created_at' => 'Created At',
            'raw_type' => 'Raw Type'
        ];
    }
    
	public static function isValidTestType($type)
	{
		$validTypes = Yii::$app->studyDefinitions->testTypes();
		return in_array($type, $validTypes);
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
