<?php

namespace common\studyDefinitions;

use yii;

class StudySection extends \yii\base\Component
{
	public $name;
	public $start;
	public $length;
	public $interval = "days";
	public $tests;
	public $day;
	public $week;
	
	public $abs_days_from_beginning;	// As opposed to $start, this is the absolute number of days since the beginning of the participant's study
	
	public $testConfig = ['class' => '\common\studyDefinitions\TestDefinition'];
	
	public function init()
	{
		parent::init();
		
		if(!empty($this->tests))
		{
			$this->tests = $this->buildTests($this->tests);
		}
	}
	
	public function testCountPerDay($type = null)
	{
		$count = 0;
		$contiguousCount = 0;
		foreach($this->tests as $test)
		{
			if($type == null || $type == $test->type)
			{
				// Contiguous tests are all actually part of the same test session, so let's just get the highest value 
				// and then add it to $count after we loop through all of the tests.
				if($test->contiguous)
				{
					$contiguousCount = max($contiguousCount, $test->frequency);
				}
				else
				{
					$count += $test->frequency;
				}
			}
		}
		
		$count += $contiguousCount;
		
		return $count;
	}

	// Counts the number of tests taken over the entire interval of this section.
	// This is calculated by the frequency of each test times the length of the section.

	public function testCount($type = null)
	{
		$count = $this->testCountPerDay($type);
		$count = $count * $this->length;
		return $count;
	}
	
	
	protected function buildTests($testDelcarations)
	{
		$builtTests = [];
		
		foreach($testDelcarations as $test)
		{
			$builtTests []= Yii::createObject(array_merge($this->testConfig, $test));
		}
		
		return $builtTests;
	}
}
	
?>