<?php

namespace common\dataFormatters;

use yii;
use yii\db\Query;
use common\models\TestSessionData;

class SessionScheduleFormatter extends BaseDataFormatter
{
		
	public function formatData()
	{	
		$session_data = [];
		$studySections = Yii::$app->studyDefinitions->studySchedule();
		$expiration_time = Yii::$app->studyDefinitions->expiration_time;
		foreach($this->data as $session)
		{
			$session["date"] = date("m/d/Y", $session["session_date"]);
			
			// We have 4 different status options:
			// - Completed (start_date is not null, and completed = true)
			// - Not Completed (completed != true, but we have a start_date)
			// - Not Yet Taken ((session_date + expiration) is in the future, so of course they haven't taken it yet)
			// - Missed (session_date has passed, and we don't even have a start_date)
			
			if(isset($session["start_date"]) && empty($session["start_date"]) == false && $session["completed"] == 1)
			{
				$session["status"] = "Completed";	
			}
			else if(isset($session["start_date"]) && empty($session["start_date"]) == false)
			{
				$session["status"] = "Not Completed";
			}
			else if(($session["session_date"] + $expiration_time) > time())
			{
				$session["status"] = "Not Yet Taken";
			}
			else
			{
				$session["status"] = "Missed";
			}

			if(isset($studySections[$session["study_section"]]))
			{
				$session["test_cycle"] = $studySections[$session["study_section"]]->name;
			}
			else
			{
				$session["test_cycle"] = "No Cycle";
			}
			$session["session_id"] = $session["session_identifier"];
			$session_data []= $session;
		}
		
		return $session_data;
	}
	
	public function orderedFields()
	{
		return ["participant_id", "test_cycle", "session_id", "type", "date", "status"];
	}
	
	public function filename()
	{
		$participant_id = $this->data[0]["participant_id"];
		return $participant_id . " " . $this->data_type . " " . date('d-m-y', $this->export_start_date);
	}
	
	public function shouldTranspose()
	{
		return true;
	}
	
	public function isMultipleRows()
	{
		return true;
	}
}