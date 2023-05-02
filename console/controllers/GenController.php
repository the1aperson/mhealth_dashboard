<?php

namespace console\controllers;

use yii;

class GenController extends \yii\console\Controller
{

	public function actionGenerateFixtures()
	{
		$dir = Yii::getAlias("@top/common/models");
		$fixtureDir = Yii::getAlias("@top/common/fixtures");
		
		$files = scandir($dir);
		
		foreach($files as $file)
		{
			if(stristr($file, ".php") == false)
			{
				continue;
			}
			
			$filename = basename($file, ".php");
			
			$modelObject = Yii::createObject(['class' => 'common\\models\\' . $filename]);
			
			$depends = [];
			
			foreach($modelObject->rules() as $rule)
			{
				if(isset($rule["targetClass"]))
				{
					$depends []= str_ireplace("models", "fixtures", $rule["targetClass"]) . "Fixture";
				}
			}
			
			$fixtureName = $filename . "Fixture";
			$mc = "'common\models\\$filename'";
			
			$dependsString = "";
			if(count($depends) > 0)
			{
				$dependsString .= "public \$depends = [ '" . implode("', '", $depends) . "' ];";
			}
			
			$str = <<< "EOT"
<?php
namespace common\\fixtures;

use yii\\test\\ActiveFixture;

class $fixtureName extends ActiveFixture
{
    public \$modelClass = $mc;
    $dependsString
}		
EOT;
		
		file_put_contents($fixtureDir . "/" . $fixtureName . ".php", $str);
		}
	}

}

?>