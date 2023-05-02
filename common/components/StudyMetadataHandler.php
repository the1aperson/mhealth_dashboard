<?php

namespace common\components;

use yii;
use yii\db\Query;
use common\models\StudyMetadata;

/*
	This component should be used to retrieve study metadata, instead of grabbing data directly
	from the StudyMetadata table.
	Calling Yii::$app->studyMetadataHandler->getMetadata() will check for 'expired' data, and 
	update it appropriately.
*/

class StudyMetadataHandler extends yii\base\Component
{
	
	public function getMetadataNames()
	{
		return [
			StudyMetadata::TOTAL_PARTICIPANTS,
			StudyMetadata::TOTAL_ACTIVE_PARTICIPANTS,
			StudyMetadata::ENABLED_DEVICES,
			StudyMetadata::RETENTION_PERCENT,
			StudyMetadata::ADHERENCE_PERCENT,
			StudyMetadata::NOT_SEEN_COUNT,
			StudyMetadata::MISSED_TEST_COUNT,
			StudyMetadata::UPCOMING_SCHEDULE,
			StudyMetadata::COMPLETED_STUDY_COUNT,
			StudyMetadata::RECENTLY_INSTALLED_COUNT,
			StudyMetadata::CURRENT_PHASE_COUNT,
			StudyMetadata::DROPPED_COUNT,
			StudyMetadata::TESTING_COUNT,
		];
	}
	
		
	public function getMetadataForStudy($study_id)
	{
		$metadata = [];
		$names = $this->getMetadataNames();
		
		foreach($names as $name)
		{
			$metadata[$name] = $this->getMetadata($study_id, $name);
		}
		
		return $metadata;
	}
	
	public function getMetadata($study_id, $name)
	{
		$metadata = StudyMetadata::getMetadata($study_id, $name);
		if($this->isMetadataExpired($metadata) == false)
		{
			return $metadata;
		}
		
		return $this->callGetter($study_id, $name);
	}	
	
	
	// updateStudyMetadata()
	// This method explicitly calls getter functions of each metadata name listed in
	// getMetadataNames, triggering each to update.
	// This isn't really meant to be called frequently, since it could be a long-running task.
		
	public function updateStudyMetadata($study_id)
	{
		$names = $this->getMetadataNames();
		
		foreach($names as $name)
		{
			$this->callGetter($study_id, $name);
		}
	}
	
	
	protected function callGetter($study_id, $name)
	{
		$getter = 'get_' . $name;
		if($this->hasMethod($getter))
		{
			return $this->$getter($study_id);
		}
		else
		{
			return null;
		}
	}
	
	// Generates a JSON list of the upcoming weeks of the given test, 
	// with the counts of each phase for each week.
	
	private function get_upcoming_schedule($study_id)
	{
		$study = \common\models\Study::findOne($study_id);
		
		// Get the participants for this study, and their start/end dates
		
		$query = new Query();
		$participantQuery = $this->participantQuery($study_id);
		
		$testDatesQuery = (new Query());
		$testDatesQuery->select('participant, MIN(session_date) AS first_test_date, MAX(session_date) AS final_test_date')
		->from('participant_test_session')
		->where(['participant' => $participantQuery])
		->groupBy('participant');
			
		$participant_test_dates = $testDatesQuery->all();
		
		$beginningOfWeek = strtotime("midnight this week", $study->start_date);	// "this week" gets the Monday of the current week.
		
		$scheduleList = [];
		
		while($beginningOfWeek < $study->end_date)
		{
			$endOfWeek = strtotime("next Monday", $beginningOfWeek);	// end of this week is midnight next Sunday night/Monday morning
			
			$thisWeeksTests = [];
			foreach($participant_test_dates as $p)
			{
				$first_test_date = intval($p["first_test_date"]);
				if($first_test_date > $endOfWeek)
				{
					continue;
				}
				
				
				$studySection = Yii::$app->studyDefinitions->getTodaysStudySection($first_test_date, max($beginningOfWeek, $first_test_date));
				if($studySection != null)
				{
					if(isset($thisWeeksTests[$studySection->name]) == false)
					{
						$thisWeeksTests[$studySection->name] = 0;
					}
					$thisWeeksTests[$studySection->name] += 1;
				}
			}
			
			
			$thisWeek = [
				"week_of" => $beginningOfWeek,
				"test_sections" => $thisWeeksTests,
			];
			
			$scheduleList []= $thisWeek;
			
			$beginningOfWeek = $endOfWeek;
		}
		
		return StudyMetadata::updateMetadata($study_id, StudyMetadata::UPCOMING_SCHEDULE, json_encode($scheduleList));
	}
	
	// Updates the count of missed tests in the past $days (or all time, if $days = null)
	
	private function get_missed_test_count($study_id, $days = null)
	{
		$beginningOfDay = strtotime("midnight", time());
		$expiration_time = Yii::$app->studyDefinitions->expiration_time;
		
		$participantQuery = $this->participantQuery($study_id);
		
		$testSessionQuery = new Query();
		$testSessionQuery->select("id")
		->from("participant_test_session")
		->where(["participant" => $participantQuery])
		->andWhere(["or", "completed IS NULL", "completed = 0"])
		->andWhere("session_date + $expiration_time < $beginningOfDay");
		
		if($days != null)
		{
			$daysAgo = strtotime("-" . $days . " days", $beginningOfDay);
			$testSessionQuery->andWhere([">=", "session_date", $daysAgo]);
		}

		
		$missedTestCount = $testSessionQuery->count();
		return StudyMetadata::updateMetadata($study_id, StudyMetadata::MISSED_TEST_COUNT, $missedTestCount);
	}
	
	// Updates the adherence rate of the past $days (or all time, if $days = null)
	// This is a percentage value, the number of completed tests / total number of tests
	
	private function get_adherence_percent($study_id, $days = null)
	{
		$beginningOfDay = strtotime("midnight", time());
		$expiration_time = Yii::$app->studyDefinitions->expiration_time;
		
		$participantQuery = $this->participantQuery($study_id);
		
		$completedQuery = new Query();
		$completedQuery->select("id")
		->from("participant_test_session")
		->where(["participant" => $participantQuery])
		->andWhere("completed = 1")
		->andWhere("session_date + $expiration_time < $beginningOfDay");
		
		$allQuery = new Query();
		$allQuery->select("id")
		->from("participant_test_session")
		->where(["participant" => $participantQuery])
		->andWhere("session_date + $expiration_time < $beginningOfDay");
		
		if($days != null)
		{
			$daysAgo = strtotime("-" . $days . " days", $beginningOfDay);
			$completedQuery->andWhere([">=", "session_date", $daysAgo]);
			$allQuery->andWhere([">=", "session_date", $daysAgo]);
		}
		
		$completedCount = $completedQuery->count();
		
		$allCount = $allQuery->count();
		
		$percent = 0;
		if($allCount != 0 && $completedCount != 0)
		{
			$percent = round(($completedCount / $allCount) * 100);
		}
		
		return StudyMetadata::updateMetadata($study_id, StudyMetadata::ADHERENCE_PERCENT, $percent);	
	}
	
	// Updates the retention rate of the given study.
	// The Retention rate is the number of still-active users / number of all ever-active users
	
	private function get_retention_percent($study_id)
	{
		
		// Get the count of ever-active participants (this is pretty much the same as TOTAL_PARTICIPANTS)
		
		$deviceSubQuery = (new Query())->select("participant")->distinct()->from("participant_device");
		
		$participantQuery = (new Query())->select("id")->from("participant")->where(["study_id"=> $study_id])->andWhere('hidden = 0');
		$participantQuery->andWhere(['id' => $deviceSubQuery]);
		$everEnrolledCount = $participantQuery->count();
		
		// Now get the number of still-enrolled participants
		$stillEnrolledQuery = $this->participantQuery($study_id);
		$stillEnrolledQuery->andWhere(['id' => $deviceSubQuery]);
		$stillEnrolledCount = $stillEnrolledQuery->count();

		$percent = 0;
		if($everEnrolledCount != 0 && $stillEnrolledCount != 0)
		{
			$percent = round(($stillEnrolledCount / $everEnrolledCount) * 100);
		}
			
		return StudyMetadata::updateMetadata($study_id, StudyMetadata::RETENTION_PERCENT, $percent);
	}
	
	
	// Updates the total count of participants in the study
	
	// total_participants is the number of participants that are enabled and have ever registered.
	
	private function get_total_participants($study_id)
	{
		$participantQuery = new Query();
		$participantQuery->select("id")
		->from("participant")
		->where(["study_id"=> $study_id])
		->andWhere('hidden = 0');
		
		$deviceSubQuery = (new Query())->select("participant")->distinct()
		->from("participant_device");

		$participantQuery->andWhere(['id' => $deviceSubQuery]);
		
		$participantCount = $participantQuery->count();
		return StudyMetadata::updateMetadata($study_id, StudyMetadata::TOTAL_PARTICIPANTS, $participantCount);
	}
	
	private function get_total_active_participants($study_id)
	{
		$participantQuery = $this->participantQuery($study_id);
		
		$activeDeviceSubquery = (new Query())->select("participant")->distinct()
		->from("participant_device");

		$completedQuery = (new Query())->select('participant')
		->from('participant_completed_study')
		->where('completed_study = 1');

		$participantQuery->andWhere(['id' => $activeDeviceSubquery]);
		$participantQuery->andWhere(['not in', 'id', $completedQuery]);
		
		$participantCount = $participantQuery->count();
		return StudyMetadata::updateMetadata($study_id, StudyMetadata::TOTAL_ACTIVE_PARTICIPANTS, $participantCount);
	}
	
	// Updates the total count of active devices
	
	private function get_enabled_devices($study_id)
	{
		$participantQuery = $this->participantQuery($study_id);
		
		$deviceQuery = new Query();
		$deviceQuery->select("id")
		->from("participant_device")
		->where(["participant" => $participantQuery])
		->andWhere(["active" => 1]);
		
		$deviceCount = $deviceQuery->count();
		return StudyMetadata::updateMetadata($study_id, StudyMetadata::ENABLED_DEVICES, $deviceCount);
	}
	
	// Updates the count of participants that haven't been 'seen' in the past $days
	
	private function get_not_seen_count($study_id, $days = 7)
	{
		$beginningOfDay = strtotime("midnight", time());
		$daysAgo = strtotime("-" . $days . " days", $beginningOfDay);
		$participantQuery = $this->participantQuery($study_id);
		
		$completedQuery = (new Query())->select('participant')
		->from('participant_completed_study')
		->where(['participant' => $participantQuery])
		->andWhere('completed_study = 1');
		
		$lastUpdatedQuery = new Query();
		$lastUpdatedQuery->select("id")
		->from("participant_last_seen")
		->where(['participant' => $participantQuery])
		->andWhere(['not in', 'participant', $completedQuery])
		->andWhere(['<', 'last_seen', $daysAgo]);
		
		$missingCount = $lastUpdatedQuery->count();
		return  StudyMetadata::updateMetadata($study_id, StudyMetadata::NOT_SEEN_COUNT, $missingCount);
	}
	
	// Updates the number of participants that have completed the study.
	
	private function get_completed_study_count($study_id)
	{
		$participantQuery = $this->participantQuery($study_id);

		// Get the count of participants who have been marked as completed
		
		$countQuery = (new Query())->select('participant, completed_study')
		->from('participant_completed_study')
		->where(['participant' => $participantQuery])
		->andWhere('completed_study = 1');
		
		$completedCount = $countQuery->count();
		return StudyMetadata::updateMetadata($study_id, StudyMetadata::COMPLETED_STUDY_COUNT, $completedCount);
	}
	
	// Updates the count of participants that newly enrolled in the past $days
	
	private function get_recently_installed_count($study_id, $days = 7)
	{
		$beginningOfDay = strtotime("midnight", time());
		$daysAgo = strtotime("-" . $days . " days", $beginningOfDay);
		$participantQuery = $this->participantQuery($study_id);
		
		// Get the created_at date for the first installation for each participant in the study
		$firstInstallQuery = (new Query())->select('participant, MIN(created_at) as first_install_date')
		->from('participant_device')
		->where(['participant' => $participantQuery])
		->groupBy('participant');
		
		// Now count the number of first_install_dates that were in the past week.
		$countQuery = new Query();
		$countQuery->select("first_install_date")
		->from(['tmp' => $firstInstallQuery])
		->where(['>', 'first_install_date', $daysAgo]);
		
		$installCount = $countQuery->count();
		return StudyMetadata::updateMetadata($study_id, StudyMetadata::RECENTLY_INSTALLED_COUNT, $installCount);
	}
	
	// Updates the counts of the number of participants in each phase of the study.
	
	private function get_current_phase_count($study_id)
	{	
		
		// Much like total_active_participants, we want participants who have not been dropped,
		// have active devices, and have not completed the study.
		
		$participantQuery = $this->participantQuery($study_id);
		
		$activeDeviceSubquery = (new Query())->select("participant")->distinct()
		->from("participant_device");

		$completedQuery = (new Query())->select('participant')
		->from('participant_completed_study')
		->where('completed_study = 1');

		$participantQuery->andWhere(['id' => $activeDeviceSubquery]);
		$participantQuery->andWhere(['not in', 'id', $completedQuery]);
		// The $maxDateQuery is essentially selecting the rows for each participant with their most recent test +/- 24 hours around now.
		// We don't just want their most recent test, because that could have been from weeks ago. We want to know what test they're taking/have taken
		// within the recent past.
		
		$maxDateQuery = (new Query());
		$maxDateQuery->select("participant, MAX(session_date) as session_date")
		->from("participant_test_session")
		->where(["<", "session_date", strtotime("+1 day")])
		->andWhere([">", "session_date", strtotime("-1 day")])
		->groupBy("participant");
		
		
		// $testDatesQuery is then selecting the rows based on matching $maxDateQuery. Something like:  
		/*
			select * from participant_test_session p1
			inner join (
			select participant, max(session_date) AS session_date FROM participant_test_session
			group by participant
			) p2 ON p1.participant = p2.participant AND p1.session_date = p2.session_date
		*/
		// But, we actually just want to count the values of study_section. So the select is more like
		// Select COUNT(case study_section when 0 then 1 else null end) AS "Test Cycle 1", etc
		
		$studySections = Yii::$app->studyDefinitions->studySchedule();
		$testDatesQuery = (new Query());
		
		foreach($studySections as $i => $section)
		{
			$testDatesQuery->addSelect("COUNT(CASE study_section WHEN $i THEN 1 ELSE null END) AS \"" . $section->name . "\"");
		}
		
		$testDatesQuery->from('participant_test_session p1')
		->where(['p1.participant' => $participantQuery])
		->innerJoin(['p2' => $maxDateQuery], 'p1.participant = p2.participant AND p1.session_date = p2.session_date');
					
		$sectionCounts = $testDatesQuery->one();
		
		
		return StudyMetadata::updateMetadata($study_id, StudyMetadata::CURRENT_PHASE_COUNT, json_encode($sectionCounts));
	}
	
	private function get_dropped_count($study_id)
	{
		$participantQuery = (new Query())->select("id")
		->from("participant")
		->where(["study_id"=> $study_id])
		->andWhere(["enabled" => 0])
		->andWhere('hidden = 0');
		
		$droppedCount = $participantQuery->count();
		
		return StudyMetadata::updateMetadata($study_id, StudyMetadata::DROPPED_COUNT, $droppedCount);
		
	}
		
	// Counts the number of participants currently in a test cycle, and those who are not.
	
	private function get_testing_count($study_id)
	{
		$phase_count = StudyMetadata::getMetadata($study_id, StudyMetadata::CURRENT_PHASE_COUNT);
		if($phase_count == null)
		{
			$phase_counts = [];
		}
		else
		{
			$phase_counts = json_decode($phase_count->value, true);
		}
		
		$total_participant_count = StudyMetadata::getMetadata($study_id, StudyMetadata::TOTAL_ACTIVE_PARTICIPANTS)->value ?? 0;
		
		$testing_count = 0;
		
		foreach($phase_counts as $phase => $count)
		{
			$testing_count += intval($count);
		}
		
		$not_testing_count = max(0, $total_participant_count - $testing_count);
		
		$countData = ["testing" => $testing_count, "not_testing" => $not_testing_count];
		return StudyMetadata::updateMetadata($study_id, StudyMetadata::TESTING_COUNT, json_encode($countData));
	}
	
		
	/*! reused queries */
	
	protected function participantQuery($study_id)
	{
		$participantQuery = new Query();
		$participantQuery->select("id")
		->from("participant")
		->where(["study_id"=> $study_id])
		->andWhere(["enabled" => 1])
		->andWhere('hidden = 0');
		return $participantQuery;
	}
	
	protected function isMetadataExpired($metadata)
	{
		$now = time();
        $expiration = Yii::$app->params["study_adherence_update_rate"] ?? 3600;
		
		if($metadata == null || ($metadata->updated_at + $expiration) < $now)
		{
			return true;
		}
		return false;
	}
	
}	
	
?>