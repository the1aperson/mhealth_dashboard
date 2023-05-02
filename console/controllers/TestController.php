<?php

namespace console\controllers;

use yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;
use yii\helpers\ArrayHelper;
use yii\db\Expression;
use yii\db\Query;



class TestController extends \yii\console\Controller
{

	public $verbose = false;
	public $app_version = "0.0.0";
	public $device_info = "iOS|iPhone8,4|10.1.1";
	
	public $useFrontendForms = false; // if true, this will use the forms used by the frontend api, which is like a million times slower
	
	private $baseTestData = null;
	
	public function beforeAction($action)
	{
		if(YII_DEBUG == false || YII_ENV != 'dev')
		{
			Console::stdout(Console::renderColoredString("%F%G%5You can't run these on a non-dev environment!%n\n"));
			return false;
		}
		return parent::beforeAction($action);
	}
	
	
	public function actionGenerateFakeTestData($count = 10, $spread = 10, $study_length = 100, $test_run_length = 100, $beginning_date = null)
	{		
		if(function_exists('timecop_freeze') == false)
		{
			echo "You need to install TIME COP to use this action: https://github.com/hnw/php-timecop \n";
			return;
		}
		
		$start = time();
		
		if($beginning_date == null)
		{
			$beginning_date = time();
		}
		else
		{
			$beginning_date = intval($beginning_date);
		}
		timecop_freeze($beginning_date);
				
		$study_length = intval($study_length);	// length of the study itself
		$test_run_length = intval($test_run_length); // number of days to simulate the study
		$count = intval($count);	// number of participants
		$spread = intval($spread); // number of days over which participants will sign up for study

		
		$study_schedule = $this->getStudySection();
		
		$study = $this->createNewStudy($study_length);
		$this->stdout("Created new study called ".$study->name.".\n", Console::FG_GREEN);
		$participant_ids = $this->createParticipants($study, $count);
		$this->stdout("Created $count participants.\n", Console::FG_GREEN);
		
		$initial_date = strtotime("midnight", $study->start_date); // 0:00:00 on first day of study.

		$enrolledParticipants = [];
		
		$statHeaders = ['Day', 'Enrolled', 'Tests Taken', 'Tests Missed', 'enabled_devices', 'missed_test_count', 'not_seen_count', 'total_participants', 'Date'];
		$studyStats = [];

		
		Console::startProgress(0, $test_run_length);
		for($day = 0; $day < $test_run_length; $day++)
		{
			Console::updateProgress($day, $test_run_length);
			// set time to beginning of current day
			timecop_freeze(strtotime("+ $day days", $initial_date));
			timecop_freeze(strtotime("6:00:00"));
			
			$todaysDate = date("d M Y", time());
			
			$enrolledTodayCount = 0;
			$testsTodayCount = 0;
			$testsMissedTodayCount = 0;
			// First, see if we need to keep enrolling people
			if($day < $spread)
			{
				$toEnroll = min(count($participant_ids), ceil($count / $spread));
								
				$newPids = array_slice($participant_ids, 0, $toEnroll);
				$newlyEnrolled = $this->handleEnrollment($newPids);
				
				$enrolledParticipants = array_merge($enrolledParticipants, $newlyEnrolled);
				$enrolledTodayCount += $toEnroll;
				
				if($toEnroll < count($participant_ids))
				{
					$participant_ids = array_slice($participant_ids, $toEnroll);
				}
				else
				{
					$participant_ids = [];
				}
			}
			
			// Now, let's check each participant and see if they have any test sessions today
			
			$start_of_day = strtotime("midnight");
			$end_of_day = strtotime("23:59:59");

			
			foreach($enrolledParticipants as $i => $participant)
			{

				
				
				$sessionQuery = (new Query())->select('*')
				->from('participant_test_session')
				->where(['>', 'session_date', $start_of_day])
				->andWhere(['<', 'session_date', $end_of_day])
				->andWhere(['participant' => 	$participant->participant_db_id]);
				
				$sessions = $sessionQuery->all();
				

				foreach($sessions as $session)
				{
					if($participant->adherence >= rand(1,100))
					{
						$testData = $this->getTestData($participant->participant_id, $participant->device_id, $session, $day);
						timecop_freeze($testData["start_time"]);
						$this->submitTest($participant, $testData);
						$testsTodayCount += 1;
					}
					else
					{
						$testsMissedTodayCount += 1;
					}
				}				

			
			}
			
			// At the end of the "day", run stats for study-wide metadata
			
			timecop_freeze($start_of_day);
			timecop_freeze(strtotime("midnight + 1 day"));
						
		}
		
		Console::endProgress();
		
		timecop_return();

		$end = time();
		echo "Elapsed time: " . ($end - $start) . "\n";
		
		
	}
	
	
	public function actionDeleteTestStudy($study_name_or_id)
	{
		if($this->confirm("Really delete study $study_name_or_id ? This can't be undone."))
		{
			Yii::$app->getDb()->createCommand("SET FOREIGN_KEY_CHECKS=0;")->execute();
			$study = \common\models\Study::find()->where(['id' => $study_name_or_id])->one();
			if($study == null)
			{
				$study = \common\models\Study::find()->where(['name' => $study_name_or_id])->one();	
			}
			
			if($study == null)
			{
				$this->stdout("No study with name or ID $study_name_or_id found!\n");
				return;
			}
			
			$this->deleteStudy($study);

			$this->stdout("Everything's been deleted for $study_name_or_id , hope you don't regret that.\n", Console::FG_GREEN);
		}
		else
		{
			$this->stdout("Delete cancelled\n");
		}
	}
	
	public function actionReallyDeleteAllStudies()
	{
		if($this->confirm("Do you for real want to delete ALL OF THE STUDIES IN THIS DATABASE? This can't be undone."))
		{
			Yii::$app->getDb()->createCommand("SET FOREIGN_KEY_CHECKS=0;")->execute();
			
			$studies = \common\models\Study::find()->where("name like 'Test Study%'")->all();
			foreach($studies as $study)
			{
				$this->deleteStudy($study);
			}
		}
		else
		{
			$this->stdout("Delete cancelled\n");
		}
	}
	
	private function deleteStudy($study)
	{
		$participant_ids = (new \yii\db\Query())->select('id')->from("participant")->where(['study_id' => $study->id])->column();
		$participant_id_string = "(" . implode(",", $participant_ids) . ")";
		
		$alert_ids = (new \yii\db\Query())->select('id')->from("alert")->where(['participant' => $participant_ids])->column();
		if(count($alert_ids) > 0)
		{
			$alert_id_string = "(" . implode(",", $alert_ids) . ")";				
			$this->stdout("Deleting from hidden_alert...\n", Console::FG_RED);
			Yii::$app->getDb()->createCommand("DELETE FROM hidden_alert WHERE alert_id IN $alert_id_string")->execute();
		}
				
		$pTablesToDelete = [
			'alert',
			'schedule_data',
			'test_session_data',
			'participant_device',
			'participant_metadata',
			'participant_note',
			'participant_test_session',
		];
		
		if(count($participant_ids) > 0)
		{
			foreach($pTablesToDelete as $p)
			{
				$this->stdout("Deleting from $p...\n", Console::FG_RED);
				Yii::$app->getDb()->createCommand("DELETE FROM $p WHERE participant IN $participant_id_string")->execute();
			}
			
			$this->stdout("Deleting from  participants...\n", Console::FG_RED);
			Yii::$app->getDb()->createCommand("DELETE FROM participant WHERE id IN $participant_id_string")->execute();
		}
		else
		{
			$this->stdout("No participants to delete...\n", Console::FG_YELLOW);				
		}
		
		$sTablesToDelete = [
			'study_metadata',
			'study_user_auth',
			
		];
		
		
		foreach($sTablesToDelete as $s)
		{
			$this->stdout("Deleting from $s...\n", Console::FG_RED);
			Yii::$app->getDb()->createCommand("DELETE FROM $s WHERE study_id = :study_id", [":study_id" => $study->id])->execute();
		}
		
		$this->stdout("Deleting study...\n", Console::FG_RED);
		$study->delete();
	}
	
	
	/*******/
	
	// returns \console\models\TestParticipant
	
	private function handleEnrollment($participant_ids)
	{
		$enrolled = [];
		foreach($participant_ids as $participant_id)
		{
			timecop_freeze(strtotime("+1 second"));
			$device_id = $this->uuid();
			$adherence = rand(50,100);
			
			$enrolled_participant = $this->enrollParticipant($participant_id, $device_id);
			if($enrolled_participant != null)
			{
				$enrolled_participant->adherence = $adherence;
				
				$sessions = $this->generateTestSessionSchedule($this->getStudySection(), time());
				$this->submitSchedule($enrolled_participant, $sessions);

				$enrolled []= $enrolled_participant;
			}
		}
		
		return $enrolled;
	}
	
	private function getTestData($participant_id, $device_id, $session, $day_of_study)
	{
		$week = floor($day_of_study / 7);
		$day = $day_of_study % 7;
		$type = $session["type"];
		$session_id = str_replace("-" . $type, "", $session["session_identifier"]);
		$session_date = $session["session_date"];
		$start_time = $session_date + rand(30,500);
		
		if($this->baseTestData == null)
		{
			$testStr = file_get_contents(Yii::getAlias("@app/config/test-format.json"));
			$this->baseTestData = json_decode($testStr, true);
		}
		
		$testData = $this->baseTestData;
		
		$testData["week"] = $week;
		$testData["day"] = $day;
		$testData["session_id"] = $session_id;
		$testData["session_date"] = $session_date;
		$testData["start_time"] = $start_time;
		$testData["device_id"] = $device_id;
		$testData["participant_id"] = $participant_id;
		
		foreach($testData["tests"] as $i => $test)
		{
			if($test["type"] != $type)
			{
				unset($testData["tests"][$i]);
				continue;
			}
			
			if($test["type"] == "ema" && isset($test["questions"]))
			{
				for($q = 0; $q < count($test["questions"]); $q++)
				{
					$testData["tests"][$i]["questions"][$q]["response_time"] = $start_time + ($q * 15);
					$testData["tests"][$i]["questions"][$q]["response"] = strval(rand(0,100));
				}
			}
		}
		
		return $testData;
	}
	
	
	
	private function createNewStudy($days = 168, $name = null, $start_date = null)
	{
		if($name == null)
		{
			$name = "Test Study " . uniqid();
		}
		
		if($start_date == null)
		{
			$start_date = strtotime("midnight");
		}
		
		$end_date = strtotime("+" . $days . " days 23:59:59", $start_date);
		
		$study = new \common\models\Study();
		$study->name = $name;
		$study->start_date = $start_date;
		$study->end_date = $end_date;
		
		$study->save();
		
		return $study;
	}
	
	
	private function createParticipants($study, $count = 10)
	{
		$participant_ids = [];
		if($this->useFrontendForms)
		{
		
			\yii\helpers\Console::startProgress(0, $count, "Creating participants");
			for($i = 0; $i < $count; $i++)
			{
				$pid = $this->createParticipant($study);
				if($pid !== false)
				{
					$participant_ids[]= $pid;
				}
				else
				{
					echo "ERROR Trying to create participant!\n";
					die;
				}
				
				\yii\helpers\Console::updateProgress($i, $count);
			}
			\yii\helpers\Console::endProgress();
		}
		else
		{
			$rows = [];
			for($i = 0; $i < $count; $i++)
			{
				$participant_id = sprintf("%06d", rand(0,999999));
				$participant_ids []= $participant_id;
				
				$newRow = [$participant_id, $study->id, Yii::$app->security->generatePasswordHash($participant_id, 4), 1, time(), time()];
				$rows []= $newRow;
			}
			Yii::$app->getDb()->createCommand()->batchInsert('participant', ['participant_id', 'study_id', 'password_hash', 'enabled', 'created_at', 'updated_at'],
		    $rows)->execute(); 

		}
		
		return $participant_ids;
	}
	
	// Generates a Participant with a matching participant_id and password,
	// sets it to the given study, and returns the participant_id
	
	private function createParticipant($study)
	{
		$form = new \backend\models\ParticipantForm();
		
		$participant_id = sprintf("%06d", rand(0,999999));
		
		$pData = [
			"study" => $study->id,
			"participant_id" => $participant_id,
			"password" => $participant_id,
		];
		
		$form->attributes = $pData;
		$participant = $form->createParticipant();
		
		if($participant != null)
		{
			return $participant_id;
		}
		else
		{
			return false;
		}
	}
	
	private function generateTestSessionSchedule($study_schedule, $start_date)
	{
		
		$start_date = strtotime("midnight", $start_date);
		$current_day = 0;	// # of days since start, not to be confused with $current_date
		$sessions = [];
		$session_id = 0;
		/*
			    {
      "week" : 0,
      "session_id" : "0",
      "session" : 0,
      "day" : 0,
      "session_date" : 1541084329.424432,
      "types" : [
        "cognitive",
        "ema"
      ]
    },*/
		
		foreach($study_schedule as $phase)
		{
			$current_day += $phase->start;
			
			for($i = 0; $i < $phase->length; $i++)
			{
				foreach($phase->tests as $aSession)
				{
					$current_date = strtotime("+ $current_day days", $start_date);
					$wake = strtotime("+8 hours", $current_date);
					$sleep = strtotime("+20 hours", $current_date);
					
					for($s = 0; $s < $aSession->frequency; $s++)
					{

						$session_date = rand($wake, $sleep);
						$type = $aSession->type;
						
						$session = [];
						$session["session_id"] = strval($session_id);
						$session["session_date"] = $session_date;
						$session["week"] = intval($current_day / 7);
						$session["day"] = intval($current_day % 7);
						$session["session"] = $s;
						$session["types"] = [$type];
						
						$sessions[]= $session;
						$session_id += 1;
					}
				}
				
				$current_day ++;
			}
		}
		
		return $sessions;
		
	}
	
	private function enrollParticipant($participant_id, $device_id)
	{
		$jsonData = [
			"participant_id" => $participant_id,
			"device_id" =>	$device_id,
			"authorization_code" => $participant_id,
			"device_info" => $this->device_info,
			"app_version" => $this->app_version
		];
		
		$registrationForm = new \frontend\models\RegistrationForm();
		$registrationForm->attributes = $jsonData;
		
		if($registrationForm->validate() && $registrationForm->register())
		{
			$p = \common\models\Participant::getParticipantByParticipantId($participant_id);
			$d = $p->getActiveDevice();
			
			$enrolled_participant = new TestParticipant();			
			$enrolled_participant->participant_id = $participant_id;
			$enrolled_participant->device_id = $device_id;
			$enrolled_participant->participant_db_id = $p->id;
			$enrolled_participant->device_db_id = $d->id;
			return $enrolled_participant;
		}
		else
		{
			$this->setErrors($registrationForm->getErrors());
			return null;
		}
	}
	
	/*! Submitting data */
	
	private function submitSchedule($participant, $sessions)
	{
		$jsonData = [
			"participant_id" => $participant->participant_id,
			"sessions" => $sessions,
			"device_id" => $participant->device_id,
			"device_info" => $this->device_info,
			"app_version"=> $this->app_version,
			"model_version"=> "1",
		];
		
		$rawBody = json_encode($jsonData);

		$form = new \frontend\models\TestScheduleForm();
		$form->attributes = $jsonData;
		if($form->validate() == false)
		{
			echo "something went wrong with scheduling\n";
			die;
		}
		
		$p = \common\models\Participant::findOne($participant->participant_db_id);
		
		$scheduleData = $form->saveTestScheduleData($p, $rawBody, $jsonData);

		if($scheduleData == null)
		{
			echo "something went wrong with scheduling\n";
			die;
		}
	}
	
	private function submitTest($participant, $jsonData)
	{
		$rawBody = json_encode($jsonData);
		$form = new \frontend\models\TestSessionForm();
		$form->attributes = $jsonData;
		
		if($form->validate() == false)
		{
			echo "something went wrong with scheduling\n";
			die;
		}
		$p = \common\models\Participant::findOne($participant->participant_db_id);
		$testData = $form->saveTestSessionData($p, $rawBody, $jsonData);
		
		if($testData == null)
		{
			echo "something went wrong with submitting test!\n";
			die;
		}
	}
	
	
	/*! utility things */
	
	// generates a realistic UUID for device ids (like '613762bc-bbd5-4584-8668-b086a8f7687a')
	private function uuid()
	{
	    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
	
	// a study schedule, defining how many of what tests participants are going to "take".
	// start is relative to the last day of the previous phase.
	// So if phase 1 runs for 2 days, and phase 2 starts 2 days after, 
	// then phase 2 begins on day 5 of the study.
	// If phase 2 starts 0 days after phase 1, then it would begin on day 3 of the study.
	
	private function getStudySection()
	{
		return Yii::$app->studyDefinitions->studySchedule();
	}
	
	private function printUsage($prefix = "")
	{
		$usage =  sprintf("%.2f", (memory_get_usage() * 0.001));
		$this->stdout("$prefix $usage kb used\n");
	}
	
}


class TestParticipant
{
	public $participant_id; 
	public $device_id;
	
	public $participant_db_id;
	public $device_db_id;
	
	public $adherence; 
}

class TestSession
{
	public $session_id;
	public $session_date;
	public $type;
}