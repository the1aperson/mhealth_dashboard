<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

use common\models\ParticipantTestSession;
use common\models\ScheduleData;


class TestScheduleForm extends Model
{
	
	public $model_version;
	public $app_version;
	public $device_info;
	public $participant_id;
	public $device_id;
	public $sessions;
	
	
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
	        [['model_version', 'app_version', 'device_info', 'participant_id', 'device_id', 'sessions'], 'required'],
			[['model_version', 'app_version', 'device_info', 'participant_id', 'device_id'], 'string'],
			['sessions', function ($attribute, $params, $validator){
				//TODO: validate schedule format
				
				if(!is_array($this->sessions))
				{
					$this->addError('sessions', 'invalid session format');
				}
				
				foreach($this->sessions as $session)
				{
					$s = new ParticipantTestSession();
					$s->attributes = $session;
					if($s->validate() == false)
					{
						$this->addErrors($s->getErrors());
						return;
					}
				}
			}],
			
        ];
    }
    
    public function saveTestScheduleData($participant, $rawBody, $jsonData)
	{	
		$device = $participant->getActiveDevice();

		$scheduleData = new ScheduleData();
		$scheduleData->participant = $participant->id;
		$scheduleData->blob_data = $rawBody;
		$scheduleData->raw_type = \common\models\TestSessionData::RAW_TYPE_JSON;
		$scheduleData->device = $device->id;
		$scheduleData->schedule_type = ScheduleData::SCHEDULE_TYPE_SESSION_SCHEDULE;
		
		if($scheduleData->save())
		{
			Yii::$app->participantMetadataHandler->updateUserMetadata($participant->id);
			Yii::$app->participantMetadataHandler->updateScheduleMetadata($participant->id, $scheduleData, $jsonData);
			Yii::$app->participantMetadataHandler->updateDeviceMetadata($participant->id, $device->id, $this->app_version, $this->device_info);
						
			return $scheduleData;
		}
		else
		{
			return null;
		}
	}
        
}