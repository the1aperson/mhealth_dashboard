<?php

namespace common\dataFormatters;

use yii;
use yii\db\Query;

use common\models\TestSessionData;

/* mHealth base test formatter. */

class MHTestFormatter extends BaseTestFormatter
{
	
	public $jsonData;

	public function formatData()
	{
		$jsonData = parent::formatData();		
		$jsonData = $this->stripNonMatchingTestData($jsonData, $this->data_type);
		$this->jsonData = $jsonData;
		
		if($this->export_type == "json")
		{
			return $jsonData;
		}
		
		$participant_app_id = $this->data["participant_id"];
		$first_test_start_date = $this->getFirstTestStartDate($this->data["participant_db_id"]);
		
		$formattedData = [];
		$formattedData["id"] = $participant_app_id;
		$formattedData["day"] = (intval($jsonData["week"]) * 7) + intval($jsonData["day"]);
		$formattedData["survey number"] = $jsonData["session"];
		if(isset($jsonData["start_time"]) && is_numeric($jsonData["start_time"]))
		{
			$formattedData["time"] = intval((intval($jsonData["start_time"]) - $first_test_start_date) / 60);	// minutes since their first test started			
		}
		else
		{
			$formattedData["time"] = "-99";
		}

		
		if(isset($jsonData["questions"]))
		{
			foreach($jsonData["questions"] as $question)
			{
				$question_data = $this->formatQuestion($question);
				$formattedData = array_merge($formattedData, $question_data);
			}
		}
		
		return $formattedData;
	}
	
	public function formatQuestion($question)
	{
		$formattedData = [];
		
		$question_id = $this->getValue($question, "question_id", "unknown_question");
		$response = $this->getValue($question, "value", "-99");
		$response_time = $this->getValue($question, "response_time");
		
		if($response_time != null)
		{
			$response_time = date("m/d/Y h:i:s a", intval($response_time));
		}
		
		if(is_array($response))
		{
			$response_options = $this->responseOptionsForQuestion($question_id);
			
			foreach($response_options as $i => $option_name)
			{
				if(in_array($i, $response))
				{
					$formattedData[$option_name] = 1;
				}
				else
				{
					$formattedData[$option_name] = 0;
				}
			}
		}
		else
		{
			$formattedData[$question_id] = $response;
		}
		
		$formattedData["timestamp_" . $question_id] = $response_time;
		
		return $formattedData;
	}
	
	
	// If the given question_id is a multiple-choice question, we need to define
	// what those options are.
	
	public function responseOptionsForQuestion($question_id)
	{
		return null;
	}
	
	public function shouldTranspose()
	{
		return true;
	}
	
	public function filename()
	{

		if($this->export_type == "json")
		{
			$participant_app_id = $this->data["participant_id"];
			$session_id = $this->jsonData["session_id"];
			
			$session_date = $this->data["session_date"];
			return $participant_app_id . " " . $this->data_type . " " . $session_id . " " . date('d-m-y', $session_date);

		}
		else
		{
			return $this->data_type . " " . date('d-m-y');
		}
	}
}