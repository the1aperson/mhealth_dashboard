<?php

namespace common\components;

use yii;

class ArrayFlattener
{
	// recursively flattens an array to a single-dimension array.
	
	public static function flatten($dataArray, $prefix = "", $orderedKeys = null)
	{
		$rows = [];
		$keys = $orderedKeys;
		
		$is_object = false;
		$is_array = false;
		
		$objRows = [];
		
		if(is_array($dataArray))
		{
			$is_array = true;
			if($keys == null)
			{
				$keys = array_keys($dataArray);
			}
		}
		else if(is_object($dataArray))
		{
			$is_object = true;
			if($keys == null)
			{
				$keys = array_keys(get_object_vars($dataArray));
			}
		}
		else
		{
			return $dataArray;
		}
	
		foreach($keys as $key)
		{
			$value = null;
			$k = $prefix == "" ? $key : $prefix . "." . $key;
			
			if($is_array)
			{
				$value = $dataArray[$key] ?? null;
			}
			else if($is_object)
			{
				$value = $dataArray->$key ?? null;
			}
			else
			{
				$value = null;
			}
			
			if(is_array($value) || is_object($value))
			{
				$rowsToAdd = ArrayFlattener::flatten($value, $k);
				$objRows = array_merge($objRows, $rowsToAdd);
			}
			else
			{

				$rows[$k] = $value;
			}
		}
		
		$rows = array_merge($rows, $objRows);
		
		return $rows;
	}
}	
?>