<?php

namespace common\components;

use yii;

use yii\db\Query;

use common\models\TestSessionData;
use common\models\ParticipantTestSession;
use common\models\Alert;

class MHParticipantMetadataHandler extends ParticipantMetadataHandler
{
	public function updateTestMetadata($participant_id, $testData, $jsonData)
	{
		parent::updateTestMetadata($participant_id, $testData, $jsonData);
		
		$tests = $jsonData["tests"];
		
		foreach($tests as $test)
		{
			if($test["type"] == "ema")
			{
				$this->updateEMAMetadata($participant_id, $test);
			}
		}
	}
	
	private function updateEMAMetadata($participant_id, $testData)
	{
		if(!isset($testData["questions"]) || !is_array($testData["questions"]))
		{
			return;
		}
		
		foreach($testData["questions"] as $question)
		{
			if($question["question_id"] == "ema_8" && isset($question["value"]))
			{
				$response = intval($question["value"] * 100);
				if($response >= 0)
				{
					$this->updateMetadata('participant_thoughts_of_death', ['participant' => $participant_id, 'value' => $response]);
					
					if($response >= 50)
					{
						$tag = "thoughts-of-death-over-75";
						if(Alert::countAlertsByTag($participant_id, $tag, time()) == 0)
						{
							Alert::createAlert($participant_id, Alert::LEVEL_DANGER, "{{participant}} indicated a high value for thoughts of death.", strtotime("+1 week"), $tag, true);
						}
					}
				}
				
				return;
			}
		}
	}
}

?>