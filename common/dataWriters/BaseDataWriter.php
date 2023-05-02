<?php
	
namespace common\dataWriters;

use yii;

// To make minimum use of this class, extend it, and override writeData() and extension().


class BaseDataWriter extends yii\base\Component
{
	
	protected $file_handles = [];
	protected $file_paths = [];
	protected $max_file_handles = 10;
	protected $temp_path = null;
	
	public $verbose = false;
	
	// writeData()
	// get the data and filename from $formatter,
	// and do whatever you need to do to write the data.
	// Return true on success, otherwise return false.
	
	public function writeData($formatter)
	{
		return false;
	}
	
	// return the file extension your writer uses.
	
	public function extension()
	{
		return "";
	}
	
	public function getFilepaths()
	{
		return $this->file_paths;
	}

	
	public function closeFileHandles()
	{
		foreach($this->file_handles as $fh)
		{
			fclose($fh);
		}
		$this->file_handles = [];
	}
	
	// finalizeWritingData()
	// This is called by ExportHandler, after all of the data has been processed. This gives the DataWriter
	// one last chance to write any remaining data to file.
	// Ideally, all data will be written to file more or less immediately in writeData(), but in some situations
	// it may be necessary to cache the data before writing it.
	
	public function finalizeWritingData()
	{
		
	}


	
	// minor convenience method for returning a file handle for the given formatter.
	
	protected function getFileHandleForFormatter($formatter)
	{
		return $this->getFileHandle($formatter->filename(), $formatter->alwaysStartNewFile());
	}
	
	// getFileHandle()
	// Creates a file handle for the given type, or returns an already existing one.
	// if $forceNewFile is true, it will search for conflicting filenames, and increment the name suffix
	// until it finds an unused one.
	// ie "Same Named File", "Same Named File 1", "Same Named File 2", etc
	
	protected function getFileHandle($filename, $forceNewFile = false)
	{
		// we have to check file_paths, not file_handles, because we could potentially have closed a handle.
		if(isset($this->file_paths[$filename]) && $forceNewFile)
		{
			$i = 1;
			$newFilename = $filename . " ($i)";
			while(isset($this->file_paths[$newFilename]))
			{
				$i += 1;
			}
			
			$filename = $newFilename;
		}
		
		if(!isset($this->file_handles[$filename]))
		{
			// If we have too many opened file handles, close the oldest ones first
			// (the ones at the beginning of the file_handles array)
			
			if(count($this->file_handles) >= $this->max_file_handles)
			{
				$oldFh = array_shift($this->file_handles);
				fclose($oldFh);
			}
			
			$filepath = $this->getFilepath($filename);
			$fh = $this->getFileHandleForFilepath($filepath);
			$this->file_handles[$filename] = $fh;
			if($this->verbose)
			{
				Yii::info($this->className() . ": opening file handle for path $filepath", 'export-queue');					
			}
		}
		
		return $this->file_handles[$filename];
	}
	
	protected function getFileHandleForFilepath($filepath)
	{
		$fh = fopen($filepath, "c");
		fseek($fh, 0, SEEK_END);
		return $fh;
	}
	
	// getFilepath()
	// Creates a filepath for the given $type, or returns an already existing one.
	
	protected function getFilepath($filename)
	{
		if(!isset($this->file_paths[$filename]))
		{
			$filepath = $this->getTempDirectory() . "/" . $filename . "." . $this->extension();
			$this->file_paths[$filename] = $filepath;
			if($this->verbose)
			{
				Yii::info($this->className() . ": creating filepath $filepath", 'export-queue');					
			}
		}
		
		return $this->file_paths[$filename];
	}
		
	
	public function getTempDirectory()
	{
		if($this->temp_path == null)
		{
			$tempFolderPath = \Yii::getAlias('@exports/export-' . time() . "-" . uniqid());
			
			if(file_exists($tempFolderPath) == false && mkdir($tempFolderPath, 0755, true))
			{
				if($this->verbose)
				{
					Yii::info($this->className() . ": creating temp path $tempFolderPath", 'export-queue');					
				}
				$this->temp_path = $tempFolderPath;
			}
		}
		
		return $this->temp_path;
	}
	
	protected function arrayHasStringKeys($array)
	{
		$keys = array_keys($array);
		
		return $this->arrayHasStrings($keys);
	}
	
	protected function arrayHasStrings($array)
	{
		foreach($array as $key)
		{
			if(is_string($key))
			{
				return true;
			}
		}
		
		return false;
	}
	
	protected function arrayHasObjects($array)
	{
		foreach($array as $value)
		{
			if(is_array($value) || is_object($value))
			{
				return true;
			}
		}
		return false;
	}
}
?>