<?php
	
namespace common\dataWriters;

use yii;

use common\components\ArrayFlattener;

class DatDataWriter extends TabularDataWriter
{
	
	public function delimiter()
	{
		return "\t";
	}
	
	
	public function extension()
	{
		return "dat";
	}
	
	public function emptyValue()
	{
		return "-99";
	}
}