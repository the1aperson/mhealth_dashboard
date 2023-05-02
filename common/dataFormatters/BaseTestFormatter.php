<?php

namespace common\dataFormatters;

use yii;
use yii\db\Query;

use common\models\TestSessionData;

// A base formatter for test data.

class BaseTestFormatter extends BaseDataFormatter
{
	
	public $testData = null;
	public $jsonData = null;	
	
	public $combineParticipants = false;
	public $combineSessions = false;
	
	public function init()
	{
		parent::init();
		
		
		if(isset($this->options) && isset($this->options['combineParticipants']))
		{
			$this->combineParticipants = $this->options['combineParticipants'];
		}
		
		if(isset($this->options) && isset($this->options['combineSessions']))
		{
			$this->combineSessions = $this->options['combineSessions'];
		}
	}
	
	// Retrieves the necessary TestSessionData row, and parses the blob_data.
	// These are stored as member variables $testData and $jsonData, respectively.
	// Returns $jsonData.
	
	public function formatData()
	{

		$test_data_id = $this->data["test_data_id"];
		
		$this->testData = TestSessionData::findOne($test_data_id);
		$this->jsonData = $this->parseRawTestSessionData($this->testData);
		
		$deviceInfo = \common\components\DeviceNameHelper::parseDeviceInfoString($this->jsonData['device_info'], true);
		$this->jsonData['os_type'] = $deviceInfo['os_type'];
		$this->jsonData['os_version']= $deviceInfo['os_version'];
		
		if(isset($this->jsonData['device_info']))
		{
			unset($this->jsonData['device_info']);
		}
		
		return $this->jsonData;
	}
	
	// Simply returns the array_keys from $jsonData.
	
	public function orderedFields()
	{
		if(!isset($this->jsonData))
		{
			$this->formatData();
		}
		
		$fields = array_keys($this->jsonData);
		
		return $fields;
	}
			
	protected function parseRawTestSessionData($testData)
	{
		if($testData->raw_type == TestSessionData::RAW_TYPE_JSON)
		{
			return json_decode($testData->blob_data, true);
		}
		//! TODO: handle zip type?
		return null;
	}
	
	// stripNonMatchingTestData()
	// Searches $jsonData["test"] for tests with type $test_type,
	// and strips any that do not match.
	// Then, it merges the matching $test into $jsonData, producing a slightly different data structure.
	// The test's properties aren't nested with  a "tests" property on $jsonData.
	// Instead of $jsonData looking like { "tests" : [ { "type" : "test_type 1", "questions" : [], }, { "type" : "test_type 2", "questions": [] }, ...] }
	// It would look more like { "type": "test_type 2", "questions" : [], }
	
	protected function stripNonMatchingTestData($jsonData, $test_type)
	{
		$test = null;
		foreach($jsonData["tests"] as $i => $t)
		{
			if($t["type"] == $test_type)
			{
				$test = $t;
				break;
			}
		}
		
		unset($jsonData["tests"]);
		if($test != null)
		{	
			$jsonData = array_merge($jsonData, $test);
		}
		
		return $jsonData;
	}
	
	protected function getFirstTestStartDate($participant_id)
	{
		return (new Query())->select('start_date')->from('participant_test_session')
		->where(['participant' => $participant_id])
		->andWhere('start_date IS NOT NULL')
		->andWhere('start_date > 0')
		->orderBy('start_date asc')
		->scalar();
	}
	
	// The default filename format is 
	// participant_id data_type dd-mm-yy
	// If the export type is json, we want to make sure every test is written to a separate file,
	// so it includes the session_id as well.
	
	public function filename()
	{
		$participant_app_id = $this->data["participant_id"];
		$session_id = $this->jsonData["session_id"];		
		$session_date = $this->data["session_date"];
		
		$filenameParts = [];
		
		if(!$this->combineParticipants)
		{
			$filenameParts []= $participant_app_id;
		}
		
		$filenameParts []= $this->data_type;
		
		if(!$this->combineSessions)
		{
			$filenameParts []= $session_id;
			$filenameParts []= date('d-m-y', $session_date);
		}
		else
		{
			$filenameParts []= date('d-m-y', $this->export_start_date);
		}
		
		
		
		$filename = implode(" ", $filenameParts);
		return $filename;
		
	}

}