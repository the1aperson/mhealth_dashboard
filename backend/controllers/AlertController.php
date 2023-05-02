<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

use common\models\Alert;
use common\models\Participant;

/**
 * Alert controller
 */
class AlertController extends AuditableController
{
	
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		
		$behaviors['study'] = [
            'class' => \backend\filters\StudyFilter::className(),
        ];
        
		$behaviors[] = [
            'class' => 'yii\filters\AjaxFilter',
            'only' => ['clear']
	    ];
	    
	    $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
	                'actions' => ['index', 'view-all', 'render-alert', 'clear'],
                    'allow' => true,
                    'roles' => ['viewAlerts', 'manageAlerts'],
                ],
                [
	                'actions' => ['log-followup', 'no-followup'],
	                'allow' => true,
	                'roles' => ['manageAlerts'],
                ]
            ]
        ];
        
        
        
	    return $behaviors;
	}
	

	public function actionIndex()
	{
		$type = Yii::$app->request->get('type', null);
		$all = Yii::$app->request->get('all', false);
		$participant_id = Yii::$app->request->get('participant_id', null);
		$user_id = Yii::$app->user->getIdentity()->id;
		$study_id = $this->getStudyId();
		
		$alertGroups = [];
		$options = ["study_id" => $study_id, "user_id" => $user_id, "showHidden" => $all, "participant_id" => $participant_id];
		
		$types = [];
		if($type != null)
		{
			$types []= $type;
		}
		else
		{
			$types = Alert::getAlertLevels();
			$options["limit"] = 5;
		}
		
		foreach($types as $current_type)
		{
			$options["type"] = $current_type;
			
			$alerts = Alert::getAlerts($options);
			$count = Alert::getTotalCount($user_id, $study_id, $current_type);
			$requireFollowUpCount = Alert::getRequireFollowUpCount($user_id, $study_id, $current_type);
			$followedupCount = Alert::getFollowedUpCount($user_id, $study_id, $current_type);
			$alertInfo = [
				"alerts" => $alerts,
				"total_count" => $count,
				"require_follow_up_count" => $requireFollowUpCount,
				"followed_up_count" => $followedupCount,
			];
			
			$alertGroups[$current_type] = $alertInfo;
		}			
						
		return $this->render("index", ['alertInfoGroups' => $alertGroups, 'showType' => $type]);
	}
	
	public function actionViewAll($type)
	{
		return $this->render("index");
	}
	
	public function actionClear($alert_id)
	{
		Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
				
		$success = Alert::clearAlert($alert_id, Yii::$app->user->getIdentity()->id);
		
		return (object) ["success" => $success];
	}
	
	public function actionLogFollowup($id)
	{
		$form = new \backend\models\FollowupForm();
				
		if($form->load(Yii::$app->request->post()) && $form->validate() && $form->save(Yii::$app->user->getId()))
		{	
			$alert = Alert::findOne($id);
			$participant = Participant::findOne($alert->participant);
			return $this->renderPartial('/fragments/alerts/alert_follow_up_success', ['alert' => $alert, 'participant' => $participant]);
		}
		else
		{
			$alert = Alert::findOne($id);
			if($alert == null)
			{
				throw new \yii\web\NotFoundHttpException;
			}
			return $this->renderPartial('/fragments/alerts/alert_follow_up_form', ['model' => $form, 'alert' => $alert]);
		}
	}
	
	public function actionNoFollowup($id)
	{
		$form = new \backend\models\FollowupForm();

		if($form->load(Yii::$app->request->post()) && $form->noFollowup(Yii::$app->user->getId(), $id))
		{
			return;
		}
	}

	public function actionRenderAlert($id)
	{
		$options = ["study_id" => $this->getStudyId(), "user_id" => Yii::$app->user->getIdentity()->id, "id" => $id];
		$alerts = Alert::getAlerts($options);
		
		if(count($alerts) == 1)
		{
			return $this->renderPartial('/fragments/alerts/alert_item', ['alert' => $alerts[0]]);
		}
		else
		{
			return null;
		}
	}
}