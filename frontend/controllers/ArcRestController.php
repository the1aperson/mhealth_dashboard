<?php

namespace frontend\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\helpers\Json;
use common\models\ParticipantAuditTrail;


/*
	This base Controller class adds basic REST formatting, audit tracking,
	and addError() and related methods.	
*/

class ArcRestController extends Controller
{
	
	public function behaviors()
	{
		$behaviors = parent::behaviors();
			
		$behaviors[]= [
			'class' => 'yii\filters\ContentNegotiator',
			'formats' => [
				'application/json' => Response::FORMAT_JSON,
			],
		];
		
		return $behaviors;
	}
	
	public function beforeAction($action)
	{
		if(parent::beforeAction($action))
		{
			$request = yii::$app->getRequest();			
			ParticipantAuditTrail::addAuditLog('view', $request->getUrl());
			return true;
		}
		
		return false;
	}
		
	//! Response formatting
	
	public $errors = [];
	
	public function afterAction($action, $result)
	{
		$result = parent::afterAction($action, $result);
		$result = $this->formatData($result, $this->errors);
		
		// If we have errors, let's go ahead and log them. Unless an Exception was thrown, these likely won't
		// be logged otherwise.
		if(count($this->errors) > 0)
		{
			Yii::error("Errors occurred while handling action {$action->id}", __METHOD__);
			Yii::error($this->errors, __METHOD__);
		}
		return $result;
	}
	
	// this method is called in afterAction(), after any API action returns.
	// It formats the return data in an expected manner to make data and error parsing more consistent.
	
	public function formatData($data, $errors = [])
	{
		return ["response" => $data, "errors" => (object) $errors];
	}
	
	
	// If an error occurs during processing of a request, Any subclassed controllers are expected to
	// call addError() or setErrors() to add to the list of errors reported in the response data.
	public function addError($error_field, $error_message)
	{
		if(!isset($this->errors[$error_field]))
		{
			$this->errors[$error_field] = [];
		}
		
		$this->errors[$error_field][] = $error_message;
	}
	
	public function addErrors($errors)
	{
		foreach($errors as $field => $msgs)
		{
			foreach($msgs as $msg)
			{
				$this->addError($field, $msg);
			}
		}
	}
	
	public function setErrors($errors)
	{
		$this->errors = $errors;
	}
}
	
