<?php
	
namespace common\dataWriters;

use yii;

class JsonDataWriter extends BaseDataWriter
{
	private $itemCount = 0;
	public function writeData($formatter)
	{
		$data = $formatter->formatData();
		$fh = $this->getFileHandleForFormatter($formatter);
		
		if($this->itemCount == 0)
		{
			fputs($fh, "[\n");
		}
		else
		{
			fputs($fh, ",\n");
		}
		$jsonData = json_encode($data, JSON_PRETTY_PRINT);
		fputs($fh, $jsonData);
		$this->itemCount += 1;
	}
	
	public function finalizeWritingData()
	{
		// For each file we've opened, make sure we add a closing end bracket ']'.
		$this->closeFileHandles();
		foreach($this->file_paths as $filepath)
		{
			$fh = $this->getFileHandleForFilepath($filepath);
			fputs($fh, "\n]\n");
			fclose($fh);
		}
	}
	
	public function extension()
	{
		return "json";
	}
}