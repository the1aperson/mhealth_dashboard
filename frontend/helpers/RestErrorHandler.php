<?php
namespace frontend\helpers;

use Yii;
use yii\base\Exception;
use yii\base\ErrorException;
use yii\base\UserException;


class RestErrorHandler extends \yii\web\ErrorHandler	
{
	
	protected function convertExceptionToArray($exception)
    {
        $array = parent::convertExceptionToArray($exception);
        $errorName = $array["name"];
        $message = $array["message"];
		$error = [$errorName => [$message]];
        return ["response" => (object)["success" => false], "errors" => $error];
    }

}	
?>