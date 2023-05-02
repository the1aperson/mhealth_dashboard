<?php

namespace common\dataFormatters;

use yii;
use yii\db\Query;

use common\components\ArrayFlattener;

class StudyMetadataFormatter extends BaseDataFormatter
{
	
	public $metadata = null;
	
	private $keys_to_skip = ["upcoming_schedule"];
	
	private $json_keys = ["current_phase_count", "testing_count"];
	
	// grabs the metadata for the given study_id, cleans it up a bit, and returns the result.
	// We don't need to include some information, in $keys_to_skip,
	// and some keys in $json_keys need to be flattened to make them a little easier to read.
	
	public function formatData()
	{
		$study_id = $this->data["id"];
		
		$metadata_items = Yii::$app->studyMetadataHandler->getMetadataForStudy($study_id);

		$metadata = [];
		$metadata["study name"] = $this->data["name"];
		foreach($metadata_items as $item)
		{
			if(in_array($item->name, $this->keys_to_skip))
			{
				continue;
			}
			
			if(in_array($item->name, $this->json_keys))
			{
				$json = json_decode($item->value, true);
				$flattenedData = ArrayFlattener::flatten($json, $item->name);
				$metadata = array_merge($metadata, $flattenedData);	
			}
			else
			{
				$metadata[$item->name] = $item->value;	
			}
		}
				
		$this->metadata = $metadata;
		return $this->metadata;
	}
	
	public function orderedFields()
	{
		if(!isset($this->metadata))
		{
			$this->formatData();
		}
		
		$fields = array_keys($this->metadata);
		
		return $fields;
	}
	
	public function filename()
	{
		$study_name = $this->data["name"];

		return $study_name . " " . $this->data_type . " " . date('d-m-y', $this->export_start_date);
	}
}