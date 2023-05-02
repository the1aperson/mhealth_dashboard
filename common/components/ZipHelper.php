<?php

namespace common\components;

use yii;

class ZipHelper
{
		
	public static function getJsonFiles($rawBody)
	{
		return self::getZippedFiles($rawBody, "json");
	}
	
	// returns associative array of file data from raw zip data
	
	public static function getZippedFiles($rawBody, $type = null)
	{
		$filepath = self::writeZipToFile($rawBody);
		if(self::verifyZip($filepath))
		{
			$files = self::getFiles($filepath, $type);
			return $files;
		}
		
		return null;
	}
	
	// writes raw body to file, returns filepath
	// Resulting filepath should be sufficiently random, to prevent
	// collisions if multiple uploads happen simultaneously.
	
	public static function writeZipToFile($rawBody)
	{ 
		$filename = uniqid(rand(), true) . ".zip";
		$filepath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
		$file_handle = fopen($filepath, 'w');
		fwrite($file_handle, $rawBody);
		fclose($file_handle);
		
		return $filepath;
	}
	
	// verify that the zip file is a valid zip file, and can be decompressed
	// successfully. If unzip -t returns an exit code of anything but 0, 
	// it will be considered failed.
	public static function verifyZip($filepath)
	{
		$output = null;
		$return_var = null;
		$command = "unzip -t '$filepath'";
		exec($command, $output, $return_var);
		
		return $return_var == 0;
	}
	
	// Returns an associative array of filenames and data,
	// or null if it's not able to open the file.
	// Pass in a $type to only get files with a certain file extension
	// (ie "json", or "jpg")
	
	public static function getFiles($filepath, $type = null)
	{
		$fh = zip_open($filepath);
		
		if(is_resource($fh) == false)
		{
			return null;
		}
		
		$files = [];
		
		// open the zip, and read the data from each file into an 
		// associative array.
		
		while(($zh = zip_read($fh)) !== false)
		{
			if(is_resource($zh) == false)
			{
				break;
			}
			
			if(zip_entry_open($fh, $zh))
			{
				$filename = basename(zip_entry_name($zh));
				if($type != null)
				{
					$ext = pathinfo($filename, PATHINFO_EXTENSION);
					if(strtolower($ext) != strtolower($type))
					{
						continue;
					}
				}
				$filelength = zip_entry_filesize($zh);
				$filedata = zip_entry_read($zh, $filelength);
				zip_entry_close($zh);
				
				$files[$filename] = $filedata;
			}
		}
		
		zip_close($fh);
		return $files;
	}
	
	public static function archiveFiles($filepaths, $outputPath)
	{
		$pathinfo = pathinfo($outputPath);
		
		if(!file_exists($pathinfo["dirname"]))
		{
			mkdir($pathinfo["dirname"], 0755, true);
		}
		
		$zip = new \ZipArchive(); 
		
	    $zip->open($outputPath, \ZipArchive::CREATE); 
		foreach($filepaths as $filepath)
		{
			if(is_file($filepath))
			{
				Yii::info("adding $filepath", "export-queue");
				$filename = pathinfo($filepath, PATHINFO_BASENAME);
				$zip->addFile($filepath, $filename);
			}
		}
	    return $zip->close();
	}
	
}
	
	
?>