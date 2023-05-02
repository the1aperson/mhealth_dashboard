<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

use common\models\Participant;
use common\models\ParticipantDevice;
use common\models\SignatureData;
use common\models\ParticipantTestSession;
use yii\web\UploadedFile;

class SignatureDataForm extends Model
{
	public $participant_id;
	public $device_id;
	public $file;
	
	public $derp;
	
	private $participant;
	private $device;
	
	public function rules()
	{
		return [

			[['participant_id', 'device_id', 'file'], 'required'],
			[['participant_id', 'device_id'], 'string'],
			['participant_id', function($attribute, $params, $validator){
				$participant = Participant::find()->where(['participant_id' => $this->participant_id])->one();
				if($participant == null)
				{
					$this->addError('participant_id', 'Invalid Participant ID');
					return;
				}
				
				$device = ParticipantDevice::find()->where(['participant' => $participant->id, 'device_id' => $this->device_id, 'active' => 1])->one();
				
				if($device == null)
				{
					$this->addError('device_id', 'Invalid Device ID');
					return;
				}
				
				$this->participant = $participant;
				$this->device = $device;
			}],

			
 			['file', 'file', 'mimeTypes' => ['image/jpeg', 'image/png']],
		];
	}
	
	public function save()
	{
		if(!isset($this->participant))
		{
			return null;
		}
			
		$signatureData = SignatureData::createSignatureData($this->participant->id, $this->device->id, $this->file);
		if($signatureData == null)
		{
			$this->addError("file", "Unknown error with file upload");
		}
		else if($signatureData->hasErrors())
		{
			$this->addErrors($signatureData->getErrors());
		}
		
		return $signatureData;

	}
}