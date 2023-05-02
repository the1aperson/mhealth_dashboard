<?php
namespace frontend\controllers;

use Yii;
use yii\web\Response;
use yii\helpers\Json;
use yii\web\UploadedFile;

use common\components\ZipHelper;
use common\models\Participant;
use common\models\ParticipantDevice;
use common\models\TestSessionData;
use common\models\ScheduleData;
use common\models\ParticipantTestSession;
use common\models\ParticipantAuditTrail;

use frontend\models\RegistrationForm;
use frontend\models\SubmissionValidationForm;
use frontend\models\TestSessionForm;
use frontend\models\TestScheduleForm;
use frontend\models\WakeSleepScheduleForm;
use frontend\models\SignatureDataForm;

/**
 * Site controller
 */
class SiteController extends ArcRestController
{
	
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		
		$behaviors['authenticator'] = [
			'except' => ['device-registration', 'index'],
			'class' => 'yii\filters\auth\QueryParamAuth',
			'tokenParam' => 'device_id',
		];
		
		return $behaviors;
	}
	
		
	// This is here mostly just to prevent unnecessary 404's from showing up in logs.
	public function actionIndex()
	{
		return null;
	}
	
	//! device registration
	
	public function actionDeviceRegistration()
	{
		$jsonData = Yii::$app->getRequest()->getBodyParams();
		
		$registrationForm = new RegistrationForm();
		$registrationForm->attributes = $jsonData;
		
		if($registrationForm->validate() && $registrationForm->register())
		{
			return (object)["success" => true];	
		}
		else
		{
			$this->setErrors($registrationForm->getErrors());
			if(Yii::$app->response->statusCode == 200)
			{
				Yii::$app->response->statusCode = 400;
			}
			return (object)["success" => false];
		}
	}
	
	//! Test data submission
	
	public function actionSubmitTest($device_id)
	{
		if($this->validateSubmission() == false)
		{
			return (object)["success" => false];
		}
		
		$participant = Yii::$app->user->getIdentity();
		$request = Yii::$app->getRequest();
		$rawBody = $request->getRawBody();
		$jsonData = $request->getBodyParams();
		
		$form = new TestSessionForm();
		$form->attributes = $jsonData;
		if($form->validate() == false)
		{
			Yii::$app->response->statusCode = 400;
			$this->setErrors($form->getErrors());
			return (object)["success" => false];
		}
		
		$testData = $form->saveTestSessionData($participant, $rawBody, $jsonData);
		
		if($testData != null)
		{
			return (object)["success" => true, "md5"=> $testData->md5_hash];
		}
		else
		{
			Yii::$app->response->statusCode = 400;
			$this->setErrors($form->getErrors());
			return (object)["success" => false];
		}

	}	
	
	//! Schedule data submission
	            
	public function actionSubmitWakeSleepSchedule($device_id)
	{
		if($this->validateSubmission() == false)
		{
			return (object)["success" => false];
		}
		
		$participant = Yii::$app->user->getIdentity();
		$request = Yii::$app->getRequest();
		$rawBody = $request->getRawBody();
		$jsonData = $request->getBodyParams();
			
		$form = new WakeSleepScheduleForm();
		$form->attributes = $jsonData;
		if($form->validate() == false)
		{
			Yii::$app->response->statusCode = 400;
			$this->setErrors($form->getErrors());
			return (object)["success" => false];
		}
		
		// build schedule data
		
		$scheduleData = $form->saveTestScheduleData($participant, $rawBody, $jsonData);
		
		if($scheduleData != null)
		{
			return (object) [ "success" => true, "md5" => $scheduleData->md5_hash];
		}
		else
		{	
			Yii::$app->response->statusCode = 400;
			return (object)["success" => false];
		}
	}	
	
	public function actionSubmitTestSchedule($device_id)
	{
		if($this->validateSubmission() == false)
		{
			return (object)["success" => false];
		}
		
		$participant = Yii::$app->user->getIdentity();
		$request = Yii::$app->getRequest();
		$rawBody = $request->getRawBody();
		$jsonData = $request->getBodyParams();
			
		$form = new TestScheduleForm();
		$form->attributes = $jsonData;
		if($form->validate() == false)
		{
			Yii::$app->response->statusCode = 400;
			$this->setErrors($form->getErrors());
			return (object)["success" => false];
		}
		
		// build schedule data
		
		$scheduleData = $form->saveTestScheduleData($participant, $rawBody, $jsonData);
		
		if($scheduleData != null)
		{
			return (object) [ "success" => true, "md5" => $scheduleData->md5_hash];
		}
		else
		{	
			Yii::$app->response->statusCode = 400;
			return (object)["success" => false];
		}	
	}	
	
	
	//! Device ping 
	
	public function actionDeviceHeartbeat($device_id)
	{
		if($this->validateSubmission() == false)
		{
			return (object)["success" => false];
		}
		
		$participant = Yii::$app->user->getIdentity();
		$device = $participant->getActiveDevice();
		$jsonData = Yii::$app->getRequest()->getBodyParams();
		
		Yii::$app->participantMetadataHandler->updateUserMetadata($participant->id);
		Yii::$app->participantMetadataHandler->updateDeviceMetadata($participant->id, $device->id, $jsonData['app_version'], $jsonData['device_info']);
		
		return (object)["success" => true];
	}
	
	
	
		public function actionSignatureData($device_id)
	{
		$file = UploadedFile::getInstanceByName('file');
		if(is_array($file) && count($file) > 1)
		{
			$this->addError('file', 'You cannot upload multiple files');
		}
		$participant_id = Yii::$app->getRequest()->post("participant_id");

		
		$form = new SignatureDataForm();
		$form->participant_id = $participant_id;
		$form->device_id = $device_id;
		$form->file = $file;

		if($form->validate())
		{
			$signatureData = $form->save();
			if(!$form->hasErrors())
			{
				return (object)["success" => true, "md5" => $signatureData->md5_hash];
			}
		}
		
		$this->addErrors($form->getErrors());
		Yii::$app->response->statusCode = 400;
		return (object)["success" => false];
		
	}
	
	
	//! Retrieving test session info for a given participant
	
	public function actionGetSessionInfo($device_id)
	{
		$participant = Yii::$app->user->getIdentity();
		
		$first_test = ParticipantTestSession::getFirstTest($participant->id);
		$latest_test = ParticipantTestSession::getLatestTest($participant->id);

		$latest_test_info = null;		
		if($latest_test != null)
		{
			$latest_test_info = [
				"session_date" => $latest_test->session_date,
				"week" => $latest_test->week,
				"day" => $latest_test->day,
				"session" => $latest_test->session,
				"session_id" => $latest_test->session_identifier,
			];
			
		}

		$first_test_info = null;				
		if($first_test != null)
		{
			$first_test_info = [
				"session_date" => $first_test->session_date,
				"week" => $first_test->week,
				"day" => $first_test->day,
				"session" => $first_test->session,
				"session_id" => $first_test->session_identifier,
			];	
		}
		
		
		return (object)[
			"success" => true,
			"first_test" => $first_test_info,
			"latest_test" => $latest_test_info,
		];
	}
	
	
	public function actionGetTestSchedule($device_id)
	{
		$participant = Yii::$app->user->getIdentity();
		
		$schedule = ScheduleData::getLatestSchedule($participant->id, ScheduleData::SCHEDULE_TYPE_SESSION_SCHEDULE);
		if($schedule == null)
		{
			Yii::$app->response->statusCode = 400;
			$this->addError("schedule", "No Test Schedule data found for participant.");
			return null;
		}
		
		$scheduleData = json_decode($schedule->blob_data, true);
		return $scheduleData;
	}
	
	public function actionGetWakeSleepSchedule($device_id)
	{
		$participant = Yii::$app->user->getIdentity();
		
		$schedule = ScheduleData::getLatestSchedule($participant->id, ScheduleData::SCHEDULE_TYPE_WAKE_SLEEP);
		if($schedule == null)
		{
			Yii::$app->response->statusCode = 400;
			$this->addError("schedule", "No Wake/Sleep Schedule data found for participant.");
			return null;
		}
		
		$scheduleData = json_decode($schedule->blob_data, true);
		return $scheduleData;
	}
	
	
	
	private function validateSubmission()
	{
		$participant = Yii::$app->user->getIdentity();
		$jsonData = Yii::$app->getRequest()->getBodyParams();
		
		// First, use SubmissionValidationForm to validate that the data is generally in the correct form.
		$form = new SubmissionValidationForm();
		$form->attributes = $jsonData;
		
		if($form->validate() == false)
		{
			Yii::$app->response->statusCode = 400;
			$this->setErrors($form->getErrors());
			return false;
		}
		
		// Then check to make sure the $participant matches the participant_id in the data
		
		if($jsonData["participant_id"] != $participant->participant_id)
		{
			Yii::$app->response->statusCode = 401;
			$this->addError('Unauthorized',  "Your request was made with invalid credentials.");
			return false;
		}
		return true;
	}
}
