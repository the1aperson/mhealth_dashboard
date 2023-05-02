<?php

namespace common\components;

use yii;

use yii\db\Query;

use common\models\TestSessionData;
use common\models\ParticipantDevice;
use common\models\ParticipantTestSession;
use common\models\Alert;


/**********************************************************

This component is intended to act as a convenience layer between the metadata stored in various
different tables and the rest of the application.

The tables used by this component are:
participant_adherence
participant_completed_study
participant_last_seen
participant_test_finished_count
participant_test_missed_count

You'll notice that participant_metadata isn't actually in that list! That's because that table isn't
actually used for anything right now. Everythign that WAS using it ended up getting their own tables,
to make querying data easier.

In the future, we may use it to store very random bits of information that don't need to be searched.

Notes about adherence/test counts:
These three tables (participant_adherence, participant_test_finished_count, participant_test_missed_count)
share pretty similar traits. Aside from a value, they also store a test_type, and a study_section.

The $test_type will either be a type defined in Yii::$app->studyDefinitions->test_types, or "all", which
is the overall value for all tests.

The $study_section will either be the name of a section defined in Yii::$app->studyDefinitions->study_schedule, or "all",
which is the overall value for all of the sections.


**********************************************************/


class ParticipantMetadataHandler extends yii\base\Component
{
	public const TBL_PARTICIPANT_ADHERENCE = 'participant_adherence';
	public const TBL_PARTICIPANT_COMPLETED_STUDY = 'participant_completed_study';
	public const TBL_PARTICIPANT_LAST_SEEN = 'participant_last_seen';
	public const TBL_PARTICIPANT_TEST_FINISHED_COUNT = 'participant_test_finished_count';
	public const TBL_PARTICIPANT_TEST_MISSED_COUNT = 'participant_test_missed_count';
	
	
	//! API-facing methods

	/*
		updateUserMetadata()
		updates the TBL_PARTICIPANT_LAST_SEEN table.
	*/
	
	public function updateUserMetadata($participant_id, $newInstall = false)
	{
		$now = time();
		$this->updateMetadata(self::TBL_PARTICIPANT_LAST_SEEN, ['participant' => $participant_id, 'last_seen' => $now]);
	}
	
	/*
		updateTestMetadata()
		Checks to see if the participant has completed the study (by seeing if their final test's date has come and gone).
		Calls $this->updateAdherence() to update adherence/test count data.
	*/
	
	public function updateTestMetadata($participant_id, $testData, $jsonData)
	{		

		$session_id = $jsonData["session_id"];
		$sessionDate = intval($jsonData["session_date"]);
		$didFinish = $this->didFinishTestSession($jsonData);
		
		$tests = $jsonData["tests"];
		
		foreach($tests as $test)
		{
			$type = $test["type"];
			
			if(TestSessionData::isValidTestType($type) == false)
			{
				continue;
			}			

			$testSession = ParticipantTestSession::findTestSession($participant_id, $session_id, $type);
			$testSession->test_data_id = $testData->id;
			$testSession->type = $type;
			
			// was this test finished?
			if($didFinish)
			{
				$testSession->completed = 1;
			}
			else
			{
				$testSession->completed = 0;
			}
			
			if(isset($jsonData["start_time"]))
			{
				$startTime = intval($jsonData["start_time"]);
				$testSession->start_date = $startTime;
			}
			
			if(isset($jsonData["session_date"]))
			{
				$testSession->session_date = intval($jsonData["session_date"]);	
			}

			if(!$testSession->save())
			{
				return $testSession->getErrors();
			}
		}
		
		$finalTest = ParticipantTestSession::getFinalTest($participant_id);
		if($finalTest->session_date <= $sessionDate)
		{
			$this->updateMetadata(self::TBL_PARTICIPANT_COMPLETED_STUDY, ['participant' => $participant_id, 'completed_study' => 1]);
			$tag = "study-completed";
			
			if(Alert::countAlertsByTag($participant_id, $tag, time()) == 0)
			{
				Alert::createAlert($participant_id, Alert::LEVEL_MESSAGE, "{{participant}} has completed study!", strtotime("+1 week"), $tag, false);
			}
		}
		
		$this->updateAdherence($participant_id);
	}
	
	/*
		updateScheduleMetadata()
		Updates ParticipantTestSession table with updated entries for the given user.
	*/
	
	public function updateScheduleMetadata($participant_id, $scheduleData, $jsonData)
	{
		
		if(isset($jsonData["sessions"]))
		{
			$sessions_to_update = [];
						
			foreach($jsonData["sessions"] as $session_info)
			{
				$week = $session_info["week"];
				$day = $session_info["day"];
				$session = $session_info["session"];
				$session_id = $session_info["session_id"];
				
				$abs_day = ($week * 7) + $day;
				$test_section = Yii::$app->studyDefinitions->getSectionIndexByDay($abs_day);
				
				if($test_section == -1)
				{
					$test_section = null;
				}
				
				foreach($session_info["types"] as $type)
				{
					if(TestSessionData::isValidTestType($type) == false)
					{
						continue;
					}
					
					$newSession = [];
					$newSession["session_id"] =  $session_id;
					$newSession["session_date"] = intval($session_info["session_date"]);
					$newSession["type"] = $type;
					$newSession["week"] = $week;
					$newSession["day"] = $day;
					$newSession["session"] = $session;
					$newSession["study_section"] = $test_section;
					$sessions_to_update []= $newSession;
				}
			}
			
			ParticipantTestSession::setTestSessions($participant_id, $sessions_to_update);

		}		
	}

	/*
		updateDeviceMetadata()
		Updates the device info for the given device_id.
	*/
	
	public function updateDeviceMetadata($participant_id, $device_id, $app_version, $device_info)
	{
		$device = ParticipantDevice::findOne($device_id);
		$device->setDeviceInfo($app_version, $device_info);
		if($device->save() == false)
		{
			return;
		}
	}
	
		
	
	public function updateAdherence($participant_id)
	{
		// Before we begin, let's grab the previous adherence values for all-tests and all-sections
		
		$previousAdherence = $this->getAdherence($participant_id, 'all', 'all')['all'] ?? 0;
		$previousMissedCount = $this->getMissedTestCount($participant_id, 'all', 'all')['all'] ?? 0;
		$previousCompletedCount = $this->getFinishedTestCount($participant_id, 'all', 'all')['all'] ?? 0;
		
		// Then compute adherence for each combination of test type and study section (including all tests, and all study sections)
		
		$testTypes = Yii::$app->studyDefinitions->testTypes();
		$testTypes []= null;		
		$studySections = Yii::$app->studyDefinitions->studySchedule();

		foreach($testTypes as $type)
		{
			// Update adherence for each test type and study section
			foreach($studySections as $study_index => $section)
			{
				$this->updateAdherenceForSettings($participant_id, $type, $study_index);
			}
			
			// then update each test type for all study sections
			$this->updateAdherenceForSettings($participant_id, $type, null);
		}
		
		// Now, let's get the newly updated values, and compare them to the previous.
		// If they changed, then we may need to check to see if we need to set new alerts.
		// If none of these values changed, then that means the participant hasn't actually had another test in the time since we last 
		// updated their adherence. In reality, we could probably just bail out early.
		// But, for now, let's set $valuesDidChange to false, so that we can ignore certain parts later (namely, setting alerts).

		$total_tests = ParticipantTestSession::countExpiredTests($participant_id);
		$newAdherence = $this->getAdherence($participant_id, 'all', 'all')['all'] ?? 0;
		$newMissedCount = $this->getMissedTestCount($participant_id, 'all', 'all')['all'] ?? 0;
		$newCompletedCount = $this->getFinishedTestCount($participant_id, 'all', 'all')['all'] ?? 0;
						
		$valuesDidChange = true;
		
		if($previousMissedCount == $newMissedCount && $previousCompletedCount == $newCompletedCount && $previousAdherence == $newAdherence)
		{
			$valuesDidChange = false;
		}


		// Let's check and see if we need to set some new alerts for this user
		// If $valuesDidChange is false, then we know that nothing has actually changed since the last time we
		// updated this participant's adherence. So we don't need to possibly generate redundant alerts.
		// Currently, there are two alerts, one for adherence < 75, and one for adherence < 60.
		// In either case, if one exists that still requires follow-up, don't bother creating a new one.
		
		if($total_tests > 0 && $valuesDidChange && (Alert::countAlertsByDay($participant_id) == 0))
		{
			$first_test = ParticipantTestSession::getFirstTest($participant_id);
			if($first_test != null)
			{					
    			$baseline = strtotime("+ 2 days", $first_test->session_date);
    			$alert_activate = strtotime('23:59:59', $baseline);

				if($newAdherence < 60)
				{
					$tag = "adherence-under-60";
    				if((Alert::countAlertsByTag($participant_id, $tag, time(), true) == 0) && (time() >= $alert_activate))
    				{										
    					Alert::createAlert($participant_id, Alert::LEVEL_DANGER, "{{participant}} has an Adherence below 60%", null, $tag, true);
    				}
				}
				else if($newAdherence < 75)
				{
					$tag = "adherence-under-75";
    				if((Alert::countAlertsByTag($participant_id, $tag, time(), true) == 0) && time() >= $alert_activate)
    				{
    					Alert::createAlert($participant_id, Alert::LEVEL_WARNING, "{{participant}} has an Adherence below 75%", null, $tag, false);
    				}
				}
			}
		}
	}
	private function updateAdherenceForSettings($participant_id, $type = null, $study_section = null)
	{
		$params = [];
		$type_name = $type ?? 'all';
		$section_name = 'all';
		if($study_section !== null)
		{
			$params = [["study_section" => $study_section]];
			$section_name = Yii::$app->studyDefinitions->studySchedule()[$study_section]->name;
		}
		
		$total_tests = ParticipantTestSession::countExpiredTests($participant_id, $type, $params);
		$missed_tests = ParticipantTestSession::countMissedTests($participant_id, $type, $params);
		$completed_tests = ParticipantTestSession::countCompletedTests($participant_id, $type, $params);
		
		if($total_tests == 0)
		{
			$adherence = 0;
		}
		else
		{
			$adherence = intval(($completed_tests / $total_tests) * 100);
		}
		
		$this->updateMetadata(self::TBL_PARTICIPANT_ADHERENCE, ['participant' => $participant_id, 'study_section' => $section_name, 'test_type' => $type_name, 'adherence' => $adherence]);
		$this->updateMetadata(self::TBL_PARTICIPANT_TEST_FINISHED_COUNT, ['participant' => $participant_id, 'study_section' => $section_name, 'test_type' => $type_name, 'count' => $completed_tests]);
		$this->updateMetadata(self::TBL_PARTICIPANT_TEST_MISSED_COUNT, ['participant' => $participant_id, 'study_section' => $section_name, 'test_type' => $type_name, 'count' => $missed_tests]);
	}
	
	
	//! Dashboard-facing methods
	
	/*
		maybeUpdateAdherenceMetadata()
		Since we don't actually need to update the participant's adherence rates and test counts every time
		a Staff member views their page, let's check to see if we've done it recently.
	*/
	
	public function maybeUpdateAdherenceMetadata($participant_id)
	{
		$updated_at = (new Query())->select('updated_at')->from(self::TBL_PARTICIPANT_ADHERENCE)->where(['participant' => $participant_id])->orderBy('updated_at desc')->limit(1)->scalar() ?? 0;
		$update_rate = Yii::$app->params["adherence_update_rate"] ?? 300;
		$now = time();
		if($now - $updated_at > $update_rate)
		{
			$this->updateAdherence($participant_id);
		}

	}
	
	//! Retrieving specific metadata
	
	/*
		getAdherence()
		returns an array of values, representing the adherence rate for the given $participant_id.
		
		$test_type (string or array of strings) restricts the returned values to only the given test types.
		Acceptable values are the test type keys, as defined in Yii::$app->studyDefinitions->test_types), or "all", which is the total sum of all test types.
		
		$study_section (string or array of strings) restricts the returned values to only the given study schedule name.
		Acceptable values are the study schedule names, defined in Yii::$app->studyDefinitions->study_schedule, or "all", which is the total sum of all study sections.
	*/
	
	public function getAdherence($participant_id, $test_type = null, $study_section = "all")
	{		
		$query = (new Query())->select('adherence')->from(self::TBL_PARTICIPANT_ADHERENCE)->where(['participant' => $participant_id])->indexBy('test_type');
		if($test_type != null)
		{
			$query->andWhere(['test_type' => $test_type]);
		}

		$query->andWhere(['study_section' => $study_section]);
		
		return $query->column();
	}
	
	/*
		getFinishedTestCount()
		returns an array of finished test counts for the given $participant_id.
		
		$test_type (string or array of strings) restricts the returned values to only the given test types.
		Acceptable values are the test type keys, as defined in Yii::$app->studyDefinitions->test_types), or "all", which is the total sum of all test types.
		
		$study_section (string or array of strings) restricts the returned values to only the given study schedule name.
		Acceptable values are the study schedule names, defined in Yii::$app->studyDefinitions->study_schedule, or "all", which is the total sum of all study sections.
		
	*/
	
	public function getFinishedTestCount($participant_id, $test_type = null, $study_section = "all")
	{
		$query = (new Query())->select('count')->from(self::TBL_PARTICIPANT_TEST_FINISHED_COUNT)->where(['participant' => $participant_id])->indexBy('test_type');
		if($test_type != null)
		{
			$query->andWhere(['test_type' => $test_type]);
		}

		$query->andWhere(['study_section' => $study_section]);
			
		
		return $query->column();
	}
	
	/*
		getMissedTestCount()
		returns an array of missed test counts for the given $participant_id.
		
		$test_type (string or array of strings) restricts the returned values to only the given test types.
		Acceptable values are the test type keys, as defined in Yii::$app->studyDefinitions->test_types), or "all", which is the total sum of all test types.
		
		$study_section (string or array of strings) restricts the returned values to only the given study schedule name.
		Acceptable values are the study schedule names, defined in Yii::$app->studyDefinitions->study_schedule, or "all", which is the total sum of all study sections.
		
	*/
	
	
	public function getMissedTestCount($participant_id, $test_type = null, $study_section = "all")
	{
		$query = (new Query())->select('count')->from(self::TBL_PARTICIPANT_TEST_MISSED_COUNT)->where(['participant' => $participant_id])->indexBy('test_type');
		if($test_type != null)
		{
			$query->andWhere(['test_type' => $test_type]);
		}

		$query->andWhere(['study_section' => $study_section]);
		
		return $query->column();
	}
	
	public function getLastTestSeen($participant_id)
	{
		return (new Query())->select('last_seen')->from(self::TBL_PARTICIPANT_LAST_SEEN)->where(['participant' => $participant_id])->scalar();
	}
	
	public function hasParticipantCompletedStudy($participant_id)
	{
		return (new Query())->select('completed_study')->from(self::TBL_PARTICIPANT_COMPLETED_STUDY)->where(['participant' => $participant_id])->scalar() == 1;
	}
	
	//! Private setter methods
	
	
	// Attempts to insert or update the given values into the given table, without having to first 
	// query for a record.
	
	protected function updateMetadata($table, $values)
	{
		$columns = array_keys($values);
		$columns []= 'created_at';
		$columns []= 'updated_at';
		$columns = "(" . implode(", ", $columns) . ")";
		
		$pdoValues = [];
		$pdoUpdateValues = [];
		$pdoUpdateClause = [];
		foreach($values as $key => $value)
		{
			$pdoValues[":" . $key] = $value;
			$pdoUpdateValues[":dupe" . $key] = $value;
			$pdoUpdateClause []= "$key = :dupe$key";
		}
		
		$pdoValues[":created_at"] = time();
		$pdoValues[":updated_at"] = time();
		$pdoUpdateValues[":dupe_updated_at"] = time();
		$pdoUpdateClause []= "updated_at = :dupe_updated_at";
		
		$pdoColumns = "(" . implode(", ", array_keys($pdoValues)) . ")";
		$pdoUpdateClause = implode(", ", $pdoUpdateClause);

		return Yii::$app->getDb()->createCommand("INSERT INTO " . $table . " $columns  VALUES $pdoColumns ON DUPLICATE KEY UPDATE $pdoUpdateClause", 
	    array_merge($pdoValues, $pdoUpdateValues))
	    ->execute();
	}
	
	protected function incrementMetadata($table, $column, $by, $whereColumns)
	{	
			
		$columns = array_keys($whereColumns);
		$columns []= $column;
		$columns []= 'created_at';
		$columns []= 'updated_at';
		$columns = "(" . implode(", ", $columns) . ")";
		
		$pdoValues = [];
		$pdoUpdateValues = [];
		$pdoUpdateClause = [];
		foreach($whereColumns as $key => $value)
		{
			$pdoValues[":" . $key] = $value;
			$pdoUpdateValues[":dupe" . $key] = $value;
			$pdoUpdateClause []= "$key = :dupe$key";
		}
		
		$pdoValues[":" . $column] = $by;
		$pdoValues[":created_at"] = time();
		$pdoValues[":updated_at"] = time();
		$pdoUpdateValues[":dupe_updated_at"] = time();
		
		$pdoUpdateClause []= "$column = $column + $by";
		$pdoUpdateClause []= "updated_at = :dupe_updated_at";
		
		$pdoColumns = "(" . implode(", ", array_keys($pdoValues)) . ")";
		$pdoUpdateClause = implode(", ", $pdoUpdateClause);

		return Yii::$app->getDb()->createCommand("INSERT INTO " . $table . " $columns  VALUES $pdoColumns ON DUPLICATE KEY UPDATE $pdoUpdateClause", 
	    array_merge($pdoValues, $pdoUpdateValues))
	    ->execute();
	}
	
	//! Private helper methods

	
	protected function didFinishTestSession($jsonData)
	{
		if(isset($jsonData["finished_session"]) && $jsonData["finished_session"] == 1)
		{
			return true;
		}
		
		// This is a workaround for a bug in version 1.0.1 of the Android app, that doesn't always mark finished_session correctly.
		// missed_session was only set to 0 if the participant completed the test.
		
		if(stristr($jsonData["device_info"], "Android") && isset($jsonData["app_version"]) && $jsonData["app_version"] == "1.0.1" && isset($jsonData["missed_session"]) && $jsonData["missed_session"] == 0)
		{
			return true;
		}
		
		return false;
	} 
	
}	
	
?>