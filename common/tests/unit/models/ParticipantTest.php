<?php

namespace common\tests\unit\models;

use Yii;
use common\fixtures\ParticipantFixture;

use common\models\Participant;
use common\models\ParticipantTestSession;

class ParticipantTest extends \Codeception\Test\Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;


    /**
     * @return array
     */
    public function _fixtures()
    {
        return [
            'participant' => [
                'class' => ParticipantFixture::className(),
            ],
            'participant_device' => [
	            'class' => \common\fixtures\ParticipantDeviceFixture::className(),
            ]
        ];
    }

	public function testParticipantExists()
	{
		$participant = Participant::find()->where(['participant_id' => '22222'])->one();
		$this->assertFalse($participant == null, "Participant should not be null");
	}
	
	public function testParticipantDeviceExists()
	{
		$participant = Participant::find()->where(['participant_id' => '22222'])->one();
		$participant_device = $participant->getActiveDevice();
		$this->assertFalse($participant_device == null, "ParticipantDevice should not be null");
	}
	
	public function testSessionSchedule()
	{Yii::setLogger(Yii::createObject(\yii\log\Logger::class));
    Yii::$app->log->setLogger(Yii::getLogger());
		$this->doSessionSchedule();
		$this->doSubmitTests();	
		$this->doAdherenceCheck();
		Yii::getLogger()->flush();
	}
	
	private function doSessionSchedule()
	{
		$participant = Participant::find()->where(['participant_id' => '22222'])->one();
		$device = $participant->getActiveDevice();
		$test_sessions = [];
		$study_sections = Yii::$app->studyDefinitions->studySchedule();
		
		$app_version = "0.0.0";
		$model_version = "0";
		
		$studyLength = Yii::$app->studyDefinitions->getStudyLength();
		$expectedTestCount = Yii::$app->studyDefinitions->getTotalTestCount();
		
		// Let's generate some test sessions!
		
		$start_date = strtotime("- " . ($studyLength + 1) . " days", time());
		
		$start_date = strtotime("8:00 am", $start_date);
		$current_date = $start_date;
		$session_id = 0;
		
		
		foreach($study_sections as $section)
		{
			$current_date = strtotime("+ " . $section->start . " days", $current_date);
			
			for($i = 0; $i < $section->length; $i++)
			{
						
				foreach($section->tests as $test)
				{
					for($f = 0; $f < $test->frequency; $f++)
					{
						$session_date = $current_date + (2 * $f * 3600) + rand(60, 900);
						$type = $test->type;
						$daysSinceStart = Yii::$app->studyDefinitions->getDaysSinceStartDate($start_date, $session_date);
						
						$weeks = floor($daysSinceStart / 7);
						$days = $daysSinceStart % 7;
						$session_no = $f;
						
						$session = [];
						$session["session_id"] = $session_id;
						$session["session"] = $session_no;
						$session["day"] = $days;
						$session["week"] = $weeks;
						$session["types"] = [$type];
						$session["session_date"] = $session_date;
						
						$sessions []= $session;
						$session_id += 1;
					}
				}
				$current_date = strtotime("+ 1 day", $current_date);
			}
		}
		
		// Make sure that we've got the right number.
		
		$this->assertTrue($expectedTestCount == count($sessions), "Created sessions should equal expected count");
		
		// Now use the TestScheduleForm to submit them.
		
		$test_schedule = [];
		$test_schedule["app_version"] = $app_version;
		$test_schedule["model_version"] = $model_version;
		$test_schedule["device_id"] = $device->device_id;
		$test_schedule["device_info"] = $device->raw_device_info;
		$test_schedule["participant_id"] = $participant->participant_id;
		$test_schedule["sessions"] = $sessions;
		
		$test_schedule_json = json_encode($test_schedule);
		Yii::info($test_schedule_json, 'test');
		$testScheduleForm = new \frontend\models\TestScheduleForm();
		$testScheduleForm->attributes = $test_schedule;
		
		$this->assertTrue($testScheduleForm->validate(), "The Test Schedule Form should be valid");
		
		$scheduleData = $testScheduleForm->saveTestScheduleData($participant, $test_schedule_json, $test_schedule);
		
		$this->assertTrue($scheduleData !== null, "The Test Schedule Form should not return null");
		
		// Now let's see if it generated tests correctly
		
		$savedSessions = \common\models\ParticipantTestSession::find()->where(['participant' => $participant->id])->asArray()->all();
		Yii::info($savedSessions, 'test');
		$this->assertTrue($expectedTestCount == count($savedSessions), "Created sessions should equal expected count");
	}
	
	// Submits tests at about 80% adherence, and ensures that they submit correctly.
	
	private function doSubmitTests()
	{
		$blank_test_session = require codecept_data_dir() . 'test_session.php';
		
		$participant = Participant::find()->where(['participant_id' => '22222'])->one();
		$device = $participant->getActiveDevice();
		$savedSessions = \common\models\ParticipantTestSession::findAll(['participant' => $participant->id]);
		
		$this->assertTrue(count($savedSessions) > 0, "There are no saved sessions!");
		
		$blank_test_session["participant_id"] = $participant->participant_id;
		$blank_test_session["app_version"] = "0.0.0";
		$blank_test_session["model_version"] = "0";
		$blank_test_session["device_id"] = $device->device_id;
		$blank_test_session["device_info"] = $device->raw_device_info;
		
		// Let's shoot for an 80% adherence rate.
		// Pick 80% of the saved sessions, and submit a test for them.
		
		$desired_adherence = 80;
		
		$testCount = 0;
		foreach($savedSessions as $session)
		{
			if(rand(0,100) > $desired_adherence) 
			{
				continue;
			}
			$testCount += 1;
			Yii::info("submitting test for " . $session->session_identifier, 'test');
			$test_session = $blank_test_session;
			
			$test_session["session"] = $session->session;
			$test_session["session_id"] = $session->session_identifier;
			$test_session["week"] = $session->week;
			$test_session["day"] = $session->day;
			$test_session["session_date"] = $session->session_date;
			$test_session["start_time"] = $session->session_date + rand(60, 600);
			
			$form = new \frontend\models\TestSessionForm();
			$form->attributes = $test_session;
			
			$this->assertTrue($form->validate(), "The Test Session Form should validate");
			
			$testData = $form->saveTestSessionData($participant, json_encode($test_session), $test_session);
			
			$this->assertTrue($testData !== null, "testData should not be null!");

		}	
		
		Yii::info("total test count $testCount", 'test');
	}
	
	// Checks the adherence and missed/completed tests counts computed by ParticipantMetadataHandler
	// and compares them to the database values.
	
	private function doAdherenceCheck()
	{
		$participant = Participant::find()->where(['participant_id' => '22222'])->one();
		$savedSessions = \common\models\ParticipantTestSession::findAll(['participant' => $participant->id]);
		
		$totalTestCount = ParticipantTestSession::find()->where(['participant' => $participant->id])->count();
		$completedCount = ParticipantTestSession::find()->where(['participant' => $participant->id, "completed" => 1])->count();
		$missedCount = ParticipantTestSession::find()->where(['participant' => $participant->id])->andWhere(["or", ["completed" => 0], "completed IS NULL"])->count();
		
		$ourAdherence = floor(($completedCount / $totalTestCount) * 100);
		
		
		$computedAllFinishedCount = Yii::$app->participantMetadataHandler->getFinishedTestCount($participant->id)["all"] ?? null;
		$computedAllMissedCount = Yii::$app->participantMetadataHandler->getMissedTestCount($participant->id)["all"] ?? null;

		$overallAdherence = Yii::$app->participantMetadataHandler->getAdherence($participant->id);
		$overallAdherence = $overallAdherence["all"] ?? null;		
		$this->assertTrue($overallAdherence != null);
		
		$this->assertTrue($completedCount == $computedAllFinishedCount, "Total finished test count does not match! $completedCount vs $computedAllFinishedCount");
		$this->assertTrue($missedCount == $computedAllMissedCount, "Total missed test count does not match! $missedCount vs $computedAllMissedCount");
		$this->assertTrue($overallAdherence == $ourAdherence, "Overall adherence does not match! $ourAdherence should equal $overallAdherence !");
		
		$sections = Yii::$app->studyDefinitions->studySchedule();
		
		foreach($sections as $i => $section)
		{
			$totalTestCount = ParticipantTestSession::find()->where(['participant' => $participant->id, 'study_section' => $i])->count();
			$completedCount = ParticipantTestSession::find()->where(['participant' => $participant->id, 'study_section' => $i, "completed" => 1])->count();
			$missedCount = ParticipantTestSession::find()->where(['participant' => $participant->id, 'study_section' => $i])->andWhere(["or", ["completed" => 0], "completed IS NULL"])->count();
			
			$ourAdherence = floor(($completedCount / $totalTestCount) * 100);
			
			$computedFinishedCount = Yii::$app->participantMetadataHandler->getFinishedTestCount($participant->id, null, $section->name)["all"] ?? null;
			$computedMissedCount = Yii::$app->participantMetadataHandler->getMissedTestCount($participant->id, null, $section->name)["all"] ?? null;
			$computedAdherence = Yii::$app->participantMetadataHandler->getAdherence($participant->id, null, $section->name)["all"] ?? null;
			
			$this->assertTrue($computedFinishedCount !== null, "computedFinishedCount shouldn't be null!");
			$this->assertTrue($computedMissedCount !== null, "computedMissedCount shouldn't be null!");
			$this->assertTrue($computedAdherence !== null, "computedAdherence shouldn't be null!");
			
			$this->assertTrue($completedCount == $computedFinishedCount, "Completed counts for section $i don't match! $completedCount vs $computedFinishedCount ");
			$this->assertTrue($missedCount == $computedMissedCount, "Missed counts for section $i don't match! $missedCount vs $computedMissedCount");
			$this->assertTrue($computedAdherence == $ourAdherence, "Adherences for section $i don't match! $ourAdherence vs $computedAdherence");
		}
	}
	
}
