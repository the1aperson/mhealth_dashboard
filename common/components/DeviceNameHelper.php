<?php

namespace common\components;

use yii;

class DeviceNameHelper
{
	public static function getIOSDevice($machineId)
	{
		$devicesJson = file_get_contents(__DIR__ . "/deviceModelLists/ios_devices.json");
		
		$deviceMap = json_decode($devicesJson, true);
		
		if(isset($deviceMap[$machineId]))
		{
			return $deviceMap[$machineId];
		}
		
		return $machineId;
	}
	
	
	// Each item in android_devices.json is in the format
	//  {
	//    "brand": "",
	//    "name": "",
	//    "device": "AD681H",
	//    "model": "Smartfren Andromax AD681H"
	//  },
	
	public static function getAndroidDevice($model)
	{
		$devicesJson = file_get_contents(__DIR__ . "/deviceModelLists/android_devices.json");
		
		$deviceMap = json_decode($devicesJson, true);
		
		foreach($deviceMap as $device)
		{
			if($device["model"] == $model || $device["device"] == $model)
			{
				if(isset($device["name"]) && $device["name"] != "")
				{
					return $device["name"];
				}
				else
				{
					return $model;
				}
			}
		}
		
		return $model;
	}
	
	
	// Parses a pipe-delimited string of device info.
	// A properly formatted device info string will have at least three parts:
	// os_type|device_type|os_version
	// ie "iOS|iPhone9,4|12.1"
	
	public static function parseDeviceInfoString($deviceInfo, $skipDeviceType = false)
	{
		$os_type = null;
		$os_version = null;
		$device_type = null;
		
		if($deviceInfo != null)
	    {
		    $infoParts = explode("|", $deviceInfo);
		    if(isset($infoParts[0]))
		    {
			    $os_type = trim($infoParts[0]);
		    }
		    
		    if($skipDeviceType == false)
		    {
			    if(isset($infoParts[1]))
			    {
				    if(strtolower($os_type) == "ios")
				    {
					    $device_type = DeviceNameHelper::getIOSDevice($infoParts[1]);
				    }
				    else if(strtolower($os_type) == "android")
				    {
					    $device_type = DeviceNameHelper::getAndroidDevice($infoParts[1]);				    
				    }
				    else
				    {
				    	$device_type = trim($infoParts[1]);
				    }
			    }
			}
		    
		    if(isset($infoParts[2]))
		    {
			    $os_version = trim($infoParts[2]);
		    }
	    }
	    
	    $info = [
		    'os_type' => $os_type,
		    'os_version' => $os_version,
		    'device_type' => $device_type,
	    ];
	    
	    return $info;
	}
}

?>