<?php

namespace common\components;

use Yii;

use yii\base\Component;

// This class creates a short hash for the current remote IP address.
// It utlizes a semi-persistent salt that is updated every 24 hours.
// The resulting hashed value is the first 8 characters of the md5 sum of the salt and the current remote IP address.

class IpAnonymizer extends Component
{
	private $salt;
	
	public function init()
	{
		$salt = Yii::$app->cache->getOrSet($this->className(), function(){
			return random_bytes(32);
		}, 86400);
		
		$this->salt = $salt;
	}
	
	public function hashedIp()
	{
		$request = Yii::$app->request;
		
		if($request->getIsConsoleRequest())
		{
			return "console";
		}
		
		$ip = Yii::$app->request->getRemoteIp();
		return substr(md5($this->salt . $ip),0,8);
	}
}