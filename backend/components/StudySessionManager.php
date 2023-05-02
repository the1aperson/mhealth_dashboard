<?php

namespace backend\components;

use yii;
use yii\base\Component;

/*
	This Component manages the current user's selected Study.
	When a user logs in, they first have to select a Study to view, and its
	study_id gets stored as a session variable.
*/

class StudySessionManager extends Component
{
	private $_study_id = null;
	private $_study = null;
	
	public function init()
	{
		parent::init();
		$this->setStudyId(Yii::$app->session->get('study_id'));
	}
		
	public function getStudyId()
	{
		if($this->_study_id == null)
		{
			$this->_study_id = Yii::$app->session->get('study_id'); 
		}
		
		return $this->_study_id;
	}
	
	public function setStudyId($study_id)
	{
		$this->_study_id = $study_id;
	}
	
	public function getStudy()
	{
		if($this->_study == null)
		{
			$study_id = $this->getStudyId();
			if($study_id != null)
			{
				$this->_study = \common\models\Study::findOne($study_id);
			}
		}
		
		return $this->_study;	
	}
	
	public function isStudySet()
	{
		return $this->getStudyId() != null;
	}
}

	
?>