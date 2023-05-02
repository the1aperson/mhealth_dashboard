<?php

namespace common\studyDefinitions;


class TestDefinition extends \yii\base\Component
{
	public $type;
	public $frequency;
	public $frequency_label;
	
	// Any tests marked as contiguous will not be counted as separate tests when calling StudySection->testCount() or testCountPerDay()
	// So for instance if you have two separate test types, but they're actually part of the same session (and would then have the same
	// session id), mark them as contiguous to keep the counts correct.
	// NOTE that if their frequencies differ, the largest frequency will be selected.
	public $contiguous = false;	
}
	
?>