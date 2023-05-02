<?php

namespace common\dataFormatters;

use yii;
use yii\db\Query;
use common\models\TestSessionData;

class WakeSleepScheduleFormatter extends BaseDataFormatter
{
	
	public $scheduleData = null;
	
	public function formatData()
	{
		$this->scheduleData = json_decode($this->data["blob_data"], true);
		
		if($this->export_type == "csv")
		{
			$availability_settings = [];
			
			$wakeSleepData = $this->scheduleData["wake_sleep_data"] ?? $this->scheduleData["wakeSleepData"] ?? [];
			
			foreach($wakeSleepData as $item)
			{
				$weekday = $item["weekday"];
				$index = date('w', strtotime($weekday));
				$setting = ["$weekday.wake" => $item["wake"], "$weekday.bed" => $item["bed"]];
				$availability_settings[$index] = $setting;
			}
			
			$availabilityData = [];
			$availabilityData["participant_id"] = $this->scheduleData["participant_id"];
			foreach($availability_settings as $availability)
			{
				foreach($availability as $key => $val)
				{
					$availabilityData[$key] = $val;
				}
			}
			
			$this->scheduleData = $availabilityData;
		}
		
		return $this->scheduleData;
	}
	
	public function orderedFields()
	{
		if(!isset($this->scheduleData))
		{
			$this->formatData();
		}
		
		$fields = array_keys($this->scheduleData);
		
		return $fields;
	}
	
	public function shouldTranspose()
	{
		return true;
	}
	
	public function filename()
	{
		$participant_id = $this->data["participant_id"];
		$schedule_date = $this->data["created_at"];
		return $participant_id . " " . $this->data_type . " " . date('d-m-y', $schedule_date);
	}
	
}