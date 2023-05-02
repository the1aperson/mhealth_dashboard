<?php
	
namespace common\dataWriters;

use yii;

use common\components\ArrayFlattener;

class CsvDataWriter extends BaseDataWriter
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
				
				// if we're at the head of the file, write the $fields as a header row.	
				if(ftell($fh) == 0)
				{
					if(fputcsv($fh, $fields) === false)
					{
						return false;
					}
				}
								
				// Then, compile each value in $fields into a single array of values,
				// and write that row.
				
				$row = [];
				foreach($fields as $field)
				{
					$row []= $flattenedData[$field] ?? null;
				}
				
				if(fputcsv($fh, $row) === false)
				{
					return false;
				}
			}
			else
			{	
				// Otherwise, write the key-value pairs as individual rows.
				
				foreach($fields as $key)
				{
					$val = $flattenedData[$key] ?? "";
					
					if(fputcsv($fh, [$key, $val]) === false)
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
	

}