<?php
	
namespace common\studyDefinitions;

use yii;

/*
	This Component is meant to be a way to access information specific to the study.
	It is configured by \common\config\studyDefinitions.php, and accessible by
	Yii::$app->studyDefinitions.
*/


class StudyDefinitions extends \yii\base\Component
{
	public $test_types;
	public $study_schedule;
	public $participant_id_rules;
	public $participant_password_rules;
	public $scheduleConfig = ['class' => '\common\studyDefinitions\StudySection'];
	public $expiration_time;
	
	public function init()
	{
		parent::init();
		
		if(!empty($this->study_schedule))
		{
			$this->study_schedule = $this->buildStudySection($this->study_schedule);
		}
	}
	
	/*
		Builds StudySection objects from the given study schedule definition.	
	*/
	
	protected function buildStudySection($studySectionDeclaration)
	{
		$builtSchedules = [];
		
		$previous_end = 0;
		foreach($studySectionDeclaration as $schedule)
		{
			$section = Yii::createObject(array_merge($this->scheduleConfig, $schedule));
			$builtSchedules []=  $section;
			if(isset($section->start) && $section->start !== null)
			{
				$section->abs_days_from_beginning = $previous_end + $section->start;				
			}
			else if(isset($section->abs_days_from_beginning) && $section->abs_days_from_beginning != null )
			{
				$section->start = $section->abs_days_from_beginning - $previous_end;
			}

			$previous_end += $section->start + $section->length;
		}	
		
		return $builtSchedules;
	}
	
	/*! Test Types */
	
	/*	testTypes()
		Should return an array of strings */
	
	public function testTypes()
	{
		return array_keys($this->test_types);
	}
	
	
	/*	testTypeLabel($type)
		returns the label for the given $type */
	public function testTypeLabel($type)
	{		
		if(isset($this->test_types[$type]))
		{
			return $this->test_types[$type];
		}
		return $type;
	}
		
		
	/*	studySchedule()
		returns an array of \common\studyDefinitions\StudySection objects */
	
	public function studySchedule()
	{
		return $this->study_schedule;
	}
	
	public function getStudySectionByName($name)
	{
		foreach($this->study_schedule as $section)
		{
			if($section->name == $name)
			{
				return $section;
			}
		}
		
		return null;
	}
	
	/* getSectionIndexByTestSessionNo()
	   Retrieves the study section index by counting the number of tests in each section, and 
	   figuring out where the given $session_number lands.
	   Returns an integer, corresponding to the index of the Study Section, or -1 if not found.
	*/
	
	public function getSectionIndexByTestSessionNo($session_number)
	{
		$ranges = $this->getTestSessionNumbersByStudySection();
				
		foreach($ranges as $s => $range)
		{		
			if($session_number >= $range["begin"] && $session_number <= $range["end"])
			{
				return $s;
			}
		}
		
		return -1;
	}
	
	public function getSectionIndexByDay($day)
	{
		$week = intval($day / 7);
		$previous_end = 0;
		
		foreach($this->study_schedule as $i => $section)
		{
			$start = $previous_end + $section->start;
			$length = $section->length;
			// Like a 0-based array, the length here would put the end past the end of this section. Instead of subtracting 1, though,
			// we need to make our comparison non-inclusive (< instead of <=). We can't subtract one because it will mess up other assumptions
			// made about the resulting value of $end.
			$end = $start + $length; 

			if($day >= $start && $day < $end)
			{
				$section->day = $day;
				$section->week = $week + 1;
				return $i;
			}
			$previous_end = $end;
		}
		
		return -1;
	}
	
	// returns an array of ranges of session numbers for each Study Section.
	// The result looks like [0 => ['begin' => 0, 'end' => 0], 1 => ['begin' => 1, 'end' => 28], 2 => ['begin' => 29, 'end' => 56] ... ]
	// begin and end are inclusive, so the range of session id's for the given section go from begin through end.
	
	public function getTestSessionNumbersByStudySection()
	{
		$ranges = [];
		$sections = $this->study_schedule;
		$section_begin = 0;
		$section_end = 0;
		
		foreach($sections as $s => $section)
		{
			$section_end = $section_begin + ($section->testCount() - 1);
			
			$ranges[$s] = ["begin" => $section_begin, "end" => $section_end];			
			$section_begin = $section_end + 1;
		}
		
		return $ranges;
	}
	
	/*	getTodaysStudySection($first_test_date, $today = null)
		Determines the $day value to call getStudySectionForDay(), based on the days between the given $first_test_date
		and today's date.
		If you want to change the reference point of "today", pass in a timestamp of the day you want it to be.
		Returns the \common\studyDefinitions\StudySection object corresponding */
	
	public function getTodaysStudySection($first_test_date, $today = null)
	{
		$days = $this->getDaysSinceStartDate($first_test_date, $today);
		$schedule = $this->getStudySectionForDay($days);
		return $schedule;
	}
	
	
	/*	getStudySectionForDay($day)
		Returns the \common\studyDefinitions\StudySection object corresponding to the given $day */
	
	public function getStudySectionForDay($day)
	{
		
		$week = intval($day / 7);
		$previous_end = 0;
		
		foreach($this->study_schedule as $section)
		{
			$start = $previous_end + $section->start;
			$length = $section->length;
			// Like a 0-based array, the length here would put the end past the end of this section. Instead of subtracting 1, though,
			// we need to make our comparison non-inclusive (< instead of <=). We can't subtract one because it will mess up other assumptions
			// made about the resulting value of $end.
			$end = $start + $length;
			
			if($day >= $start && $day < $end)
			{
				$section->day = $day;
				$section->week = $week + 1;
				return $section;
			}
			$previous_end = $end;
		}
		
		return null;
	}
	
	public function noTestingSection($first_test_date, $today = null)
	{
		
		// If no StudySection corresponds to today, let's just return a default "No Testing" studySection
		$days = $this->getDaysSinceStartDate($first_test_date, $today);
		$week = intval($days / 7);
		
		$noTestingSection = Yii::createObject($this->scheduleConfig);
		
		$noTestingSection->name = "No Testing";
		$noTestingSection->start = 0;
		$noTestingSection->length = 0;
		$noTestingSection->tests = [];
		$noTestingSection->day = $days;
		$noTestingSection->week = $week + 1;
		
		return $noTestingSection;
	}
	
	/*
		getStudySectionRanges($first_test_date)
		Returns the date ranges of the study sections, based on the given $first_test_date.
		returns an array of date ranges, like
		[
			[
				"name" =>
				"start_date" =>
				"end_date" => 
			],
			[
				"name" =>
				"start_date" =>
				"end_date" =>
			]
		...
		]
	*/
	
	public function getStudySectionRanges($first_test_date)
	{
		$previous_end = 0;
		$sections = [];
		foreach($this->study_schedule as $section)
		{
			$interval = $section->interval;
			$start = $previous_end + $section->start;
			$end = $start + $section->length;
			
			$sectionRange = [];
			$sectionRange['name'] = $section->name;
			$sectionRange['start_date'] = strtotime("+ $start $interval", $first_test_date);
			$sectionRange['end_date'] = strtotime("+ $end $interval", $first_test_date);
			$sections []= $sectionRange;
			
			$previous_end = $end;
		}
		
		return $sections;
	}
	
	// Gets the number of days between $first_test_date and $today, which are both unix timestamps
	
	public function getDaysSinceStartDate($first_test_date, $today = null)
	{
		if($today == null)
		{
			$today = time();
		}
		
		$firstDate = new \DateTime();
		$firstDate->setTimestamp($first_test_date);
		$todayDate = new \DateTime();
		$todayDate->setTimestamp($today);
		
		$interval = $todayDate->diff($firstDate);
		return $interval->days;
	}
	
	
	public function getStudyLength()
	{
		$total = 0;
		foreach($this->study_schedule as $section)
		{
			$total += $section->start + $section->length;
		}
		
		return $total;
	}
	
	public function getTotalTestCount($type = null)
	{
		$total = 0;
		foreach($this->study_schedule as $section)
		{
			$total += $section->testCount($type);
		}
		
		return $total;
	}
	
}