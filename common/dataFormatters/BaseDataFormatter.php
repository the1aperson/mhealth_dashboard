<?php

namespace common\dataFormatters;

use yii;
use yii\db\Query;

use common\models\TestSessionData;

// This class is only a base class to provide the necessary methods to override.
// To make use of this, you'll need to sublcass it and override, at the very least, formatData()

class BaseDataFormatter extends \yii\base\Component
{
	public $data;
	public $export_type = "";
	public $data_type = "";
	public $export_start_date;

	public $options;
	
	public function init()
	{
		if(!isset($this->export_start_date))
		{
			$this->export_start_date = time();
		}
	}

	// formatData()
	// Given $this->data, apply any formatting or transformations needed, and
	// return the results.
	
	public function formatData()
	{
		return null;
	}
	
	// filename()
	// generate a filename based on whatever parameters you decide.
	// The default is just the $data_type and the given $export_start_date
	// ie: cognitive 09-01-19 10-00-01
	
	public function filename()
	{
		return $this->data_type . " " . date('d-m-y', $this->export_start_date);
	}
	
	// orderedFields()
	// returns a list of fields corresponding to keys on the data returned by formatData().
	// If the order of the fields is unimportant, you can just return null.
	
	public function orderedFields()
	{
		return null;
	}
	
	// alwaysStartNewFile()
	// return true if the data returned by this formatter should always be saved to a new file,
	// instead of appending to an existing file of the same filename.
	
	public function alwaysStartNewFile()
	{
		return false;
	}
	
	// shouldTranspose()
	// return true if the data returned by this formatter should be transposed
	// In other words, if this data should be written as a row of data with multiple columns,
	// instead of multiple rows, return true.
	
	public function shouldTranspose()
	{
		return false;
	}
	
	// isMultipleRows()
	// return true if the data returned by this formatter should be considered a collection of items,
	// as opposed to just one single item.
	
	public function isMultipleRows()
	{
		return false;
	}
	
	// getValue()
	// Get value of $name from an array, or return $default if $name is not set
	
	protected function getValue($array, $name, $default = null)
	{
		if(isset($array[$name]))
		{
			return $array[$name];
		}
		
		return $default;
	}
}
