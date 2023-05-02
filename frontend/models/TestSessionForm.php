<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

use common\models\TestSessionData;

class TestSessionForm extends Model
{
	public $session_id;
	public $session_date;
	public $start_time;
	public $week;
	public $day;
	public $session;
	public $finished_session;
	public $missed_session;
	public $model_version;
	public $app_version;
	public $device_info;
	public $participant_id;
	public $device_id;
	public $tests;
	
	
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
	        [['session_id', 'session_date', 'week', 'day', 'session', 'model_version', 'app_version', 'device_info', 'participant_id', 'device_id'], 'required'],
			[['session_id', 'model_version', 'app_version', 'device_info', 'participant_id', 'device_id'], 'string'],
			[['session_date', 'start_time'], 'number'],
			[['week', 'day', 'session'], 'integer'],
			[['finished_session', 'missed_session'], 'boolean'],
			
        ];
    }
    
    public function saveTestSessionData($participant, $rawBody, $jsonData)
	{	
		$device = $participant->getActiveDevice();

		$testData = new TestSessionData();
		$testData->participant = $participant->id;
		$testData->blob_data = $rawBody;
		$testData->raw_type = TestSessionData::RAW_TYPE_JSON;
		$testData->device = $device->id;
		
		if($testData->save())
		{
			Yii::$app->participantMetadataHandler->updateUserMetadata($participant->id);
			Yii::$app->participantMetadataHandler->updateTestMetadata($participant->id, $testData, $jsonData);
			Yii::$app->participantMetadataHandler->updateDeviceMetadata($participant->id, $device->id, $this->app_version, $this->device_info);
			
			return $testData;
		}
		else
		{	$this->setErrors($testData->getErrors());
			return null;
		}
	}
        
}