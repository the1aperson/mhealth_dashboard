<?php
	
namespace common\dataWriters;

use yii;

use common\components\ArrayFlattener;

class TabularDataWriter extends BaseDataWriter
{
	
	public function writeData($formatter)
	{
		$data = $formatter->formatData();
		$fh = $this->getFileHandleForFormatter($formatter);
		
		$fields = $formatter->orderedFields();
		
		// If the data is only one row, let's wrap it in an array,
		// so we don't have to treat them differently.
		
		if($formatter->isMultipleRows() == false)
		{
			$data = [$data];
		}
		
		// For each item in $data, handle writing it to the csv.
				
		foreach($data as $dataItem)
		{
			
			// make sure the data is flattened to a single dimension.
			
			$flattenedData = ArrayFlattener::flatten($dataItem, "");
			
			// if shouldTranspose() is true, that means we should write this data
			// as one row with multiple columns.
			
			if($formatter->shouldTranspose())
			{
				if($fields == null)
				{
					$fields = array_keys($flattenedData);
				}
				$flattenedData = $this->replaceEmptyFields($flattenedData, $fields);
				// if we're at the head of the file, write the $fields as a header row.	
				if($this->writeHeaders() && ftell($fh) == 0)
				{
					if(fputcsv($fh, $fields, $this->delimiter(), $this->enclosure(), $this->escape_char()) === false)
					{
						return false;
					}
				}
								
				// Then, compile each value in $fields into a single array of values,
				// and write that row.
				
				$row = [];
				foreach($fields as $field)
				{
					$row []= $flattenedData[$field];
				}
				
				if(fputcsv($fh, $row, $this->delimiter(), $this->enclosure(), $this->escape_char()) === false)
				{
					return false;
				}
			}
			else
			{	
				// Otherwise, write the key-value pairs as individual rows.
				$flattenedData = $this->replaceEmptyFields($flattenedData, $fields);
				foreach($fields as $key)
				{
					$val = $flattenedData[$key];
					
					if(fputcsv($fh, [$key, $val], $this->delimiter(), $this->enclosure(), $this->escape_char()) === false)
					{
						return false;
					}
				}
			}

		}
		
		return true;
		
	}
	
	public function extension()
	{
		return "csv";
	}
	
	public function delimiter()
	{
		return ",";
	}
	
	public function enclosure()
	{
		return '"';
	}
	
	public function escape_char()
	{
		return "\\";
	}
	
	public function writeHeaders()
	{
		return true;
	}
	
	
	public function emptyValue()
	{
		return "";
	}
	
	public function replaceEmptyFields($data, $fields)
	{
		$emptyValue = $this->emptyValue();
		foreach($fields as $key)
		{
			if(!isset($data[$key]) || $data[$key] === "" || $data[$key] === null)
			{
				$data[$key] = $emptyValue;
			}
		}
		
		return $data;
	}
	

}