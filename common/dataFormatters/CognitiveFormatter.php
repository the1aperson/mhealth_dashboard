<?php

namespace common\dataFormatters;

use yii;
use yii\db\Query;

use common\models\TestSessionData;


class CognitiveFormatter extends BaseTestFormatter
{
	
	protected $survey_names = ["context_survey", "chronotype_survey", "wake_survey"];
	protected $test_names = ["grid_test", "price_test", "symbol_test"];
	
	// given the $jsonData returned from parent::formatData(),
	// strips non-matching test types, and returns the resulting data.
	
	public function formatData()
	{
		$jsonData = parent::formatData();
		$jsonData = $this->stripNonMatchingTestData($jsonData, $this->data_type);
		
		$this->jsonData = $jsonData;
		
		// If the export type isn't json, then we need to make a point to condense all of the answer values for questions
		// down to one field (some of them are multi-select, and we just want a comma-separated list of values instead)
		
		if($this->export_type != "json")
		{
			foreach($this->survey_names as $survey_name)
			{
				if(isset($jsonData[$survey_name]) && isset($jsonData[$survey_name]["questions"]))
				{
					for($i = 0; $i < count($jsonData[$survey_name]["questions"]); $i++)
					{
						if(isset($jsonData[$survey_name]["questions"][$i]["value"]) && is_array($jsonData[$survey_name]["questions"][$i]["value"]))
						{
							$jsonData[$survey_name]["questions"][$i]["value"] = implode(",", $jsonData[$survey_name]["questions"][$i]["value"]);
						}
					}
				}
			}
		}
		
		return $jsonData;	
	}
	
	public function orderedFields()
	{
		// Because the data in the Cognitive test is pretty complicated, and there are a LOT of optional fields, 
		// let's just generate the field list ourselves.
		
		$survey_question_structure = [
			"display_time",
			"question",
			"question_id",
			"response_time",
			"text_value",
			"type",
			"value"
		];
		
		$grid_test_structure = [
			"display_distraction",
			"display_symbols",
			"display_test_grid",
			"e_count",
			"f_count",
			"choices.0.selection_time",
			"choices.0.x",
			"choices.0.y",
			"choices.1.selection_time",
			"choices.1.x",
			"choices.1.y",
			"choices.2.selection_time",
			"choices.2.x",
			"choices.2.y",
			"images.0.image",
			"images.0.x",
			"images.0.y",
			"images.1.image",
			"images.1.x",
			"images.1.y",
			"images.2.image",
			"images.2.x",
			"images.2.y",
		];
		
		$symbol_test_structure = [
			"appearance_time",
			"correct",
			"selected",
			"selection_time",
			"choices.0.0",
			"choices.0.1",
			"choices.1.0",
			"choices.1.1",
			"options.0.0",
			"options.0.1",
			"options.1.0",
			"options.1.1",
			"options.2.0",
			"options.2.1",
		];
		
		$price_test_structure = [
			"alt_price",
			"correct_index",
			"good_price",
			"item",
			"price",
			"question_display_time",
			"selected_index",
			"selection_time",
			"stimulus_display_time",
		];
		
		$surveys = ["context_survey" => 5, "chronotype_survey" => 6, "wake_survey" => 6, ];
		$tests = [
		"grid_test" => ["count" => 2, "structure" => $grid_test_structure],
		"symbol_test" => ["count" => 12, "structure" => $symbol_test_structure],
		"price_test" => ["count" => 10, "structure" => $price_test_structure],
		];
		
		
		// Set the basic info fields first
		
		$orderedFields = [
		"participant_id",
		"session_date",
		"device_id",
		"os_type",
		"os_version",
		"app_version",
		"session_id",
		"week",
		"day",
		"session",
		"start_time",
		"finished_session",
		"missed_session",
		"interrupted",
		"model_version",
		"type",
		];
		
		// Then, add the surveys
		
		foreach($surveys as $survey_name => $question_count)
		{
			$fields = [];
			$fields []= $survey_name . ".start_date";
			for($i = 0; $i < $question_count; $i++)
			{
				foreach($survey_question_structure as $question_item)
				{
					$fieldName = [$survey_name, "questions", $i, $question_item];
					$fields []= implode(".", $fieldName);
				}
			}
			$orderedFields = array_merge($orderedFields, $fields);
		}
		
		// Then, add the tests
		
		foreach($tests as $test_name => $test_info)
		{
			$fields = [];
			$fields []= $test_name . ".date";
			for($i = 0; $i < $test_info["count"]; $i++)
			{
				foreach($test_info["structure"] as $section_item)
				{
					$fieldName = [$test_name, "sections", $i, $section_item];
					$fields []= implode(".", $fieldName);
				}
			}
			$orderedFields = array_merge($orderedFields, $fields);
		}
		
		return $orderedFields;
	}
	
	public function alwaysStartNewFile()
	{
		return false;
	}
	
	
	public function shouldTranspose()
	{
		return true;
	}

}