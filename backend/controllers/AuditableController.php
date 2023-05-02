<?php

namespace backend\controllers;

use Yii;

use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\web\Request;

use common\models\UserAuditTrail;

class AuditableController extends Controller
{

	public function beforeAction($action)
	{

		$request = yii::$app->getRequest();
		
		UserAuditTrail::addAuditLog('view', $request->getUrl());
		
		return parent::beforeAction($action);
	}
		
}