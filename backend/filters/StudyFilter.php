<?php

namespace backend\filters;

use yii;
use yii\helpers\Url;
use yii\base\ActionFilter;

class StudyFilter extends ActionFilter
{

	public function beforeAction($action)
	{
		$study_id = Yii::$app->study->getStudyId();

		if($study_id == null)
		{
			// Since the siteAdmin account is really just meant to be used to setup other accounts,
			// let's just redirect them to the staff page.
			if(!Yii::$app->user->isGuest && Yii::$app->user->getIdentity()->isSiteAdmin())
			{
				Yii::$app->getResponse()->redirect("/staff");
			}
			else
			{
				Yii::$app->session->set('select-study-return-url', Url::current([], true));
				Yii::$app->getResponse()->redirect("/select-study");
			}
			return false;
		}
		
		return true;
	}
	
	public function getStudyId()
	{
		return Yii::$app->study->getStudyId();
	}
}

	
?>