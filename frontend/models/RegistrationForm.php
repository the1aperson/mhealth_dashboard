<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

use common\models\Alert;
use common\models\Participant;
use common\models\ParticipantDevice;

class RegistrationForm extends Model
{
    public $participant_id;
    public $device_id;
    public $authorization_code;
    public $override;
    public $device_info;
    public $app_version;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
			[['participant_id', 'device_id', 'authorization_code', 'device_info', 'app_version'], 'required'],
			[['participant_id', 'device_id', 'authorization_code', 'device_info', 'app_version'], 'string'],
			[['override'], 'boolean', 'message' => 'Override must be a boolean value (either true or false)'],
			['override', 'default', 'value' => false],
			['device_info', function ($attribute, $params, $validator){
				$infoParts = explode("|", $this->$attribute);
				if(count($infoParts) < 3)
				{
					$this->addError($attribute, 'device_info improperly formatted.');
				}
			}],
        ];
    }
    
    public function register()
    {
	    
		// First, is this even a valid participant id?
		
		$participant = Participant::getParticipantByParticipantId($this->participant_id);

		if($participant == null || $participant->validatePassword($this->authorization_code) == false)
		{
			if($participant == null) // if participant is null, waste some time running a hash
			{
				Yii::$app->security->generatePasswordHash(uniqid());
			}
			Yii::$app->response->statusCode = 401;
			
			$this->addError('participant_id', 'Invalid Participant ID or Authorization Code');
			return false;
		}

		// Are they already registered?
		
		$currentDevice = $participant->getActiveDevice();
		if($currentDevice != null && $this->override !== true)
		{
			Yii::$app->response->statusCode = 409;
			$this->addError('participant_id', 'Participant already has an active Device.');
			return false;	
		}
		
		
		$device = ParticipantDevice::find()->where(['participant' => $participant->id, 'device_id' => $this->device_id])->one();
		
		if($device == null)
		{
			$device = new ParticipantDevice();
		}
		
		$device->device_id = $this->device_id;
		$device->participant = $participant->id;
		$device->setDeviceInfo($this->app_version, $this->device_info);
		
		if($device->validate() == false)
		{
			Yii::$app->response->statusCode = 400;
			$errors = $device->getErrors();
			foreach($errors as $attr => $error)
			{
				$this->addErrors(['device_id' => $error]);
			}
			return false;
		}
		
		// Set the participant, so that when we continue to save models, the audit trail is correct.
		Yii::$app->user->login($participant);
		$device->save();
				
		$device->setToActive();
		
		// Update metadata stored for the user.
		// Even though they may not have taken any tests, let's update the adherence as well, just to make sure the
		// necessary entries are generated in the database.
		
		Yii::$app->participantMetadataHandler->updateUserMetadata($participant->id, true);
		Yii::$app->participantMetadataHandler->updateDeviceMetadata($participant->id, $device->id, $this->app_version, $this->device_info);
		Yii::$app->participantMetadataHandler->updateAdherence($participant->id);
		
		$message = "{{participant}} has enrolled!";
		$tag = "enrolled";

		if($participant->getInstallCount() > 1)
		{
			$message = "{{participant}} has re-installed the app!";
			$tag = "re-register";	
		}
		
		if(Alert::countAlertsByTag($participant->id, $tag, time()) == 0)
		{
			
			Alert::createAlert($participant->id, Alert::LEVEL_MESSAGE, $message, strtotime("+ 1 week"), $tag);
		}
		return true;
    }
	
	
}
