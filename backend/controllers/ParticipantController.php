<?php

namespace backend\controllers;

use Yii;

use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\db\Query;

use common\models\Alert;
use common\models\Participant;
use common\models\ParticipantDevice;
use common\models\ParticipantNote;
use common\models\ParticipantTestSession;
use common\models\ParticipantUserFlag;

use backend\models\ParticipantForm;
use backend\models\ParticipantSearch;

use backend\models\NoteForm;
use backend\models\DeviceToggleForm;
use backend\models\ParticipantDropForm;

/**
 * ParticipantController implements the CRUD actions for Participant model.
 */
class ParticipantController extends AuditableController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
	    $behaviors = parent::behaviors();
	    
        $behaviors['study'] = [
            'class' => \backend\filters\StudyFilter::className(),
        ];
        
		$behaviors['access'] = [
            'class' => \yii\filters\AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'permissions' => ['viewParticipants'],
                    'actions' => ['index', 'view', 'device-history'],
                ],
                [
	                'allow' => true,
	                'permissions' => ['createParticipants'],
	                'actions' => ['create'],
                ],
                [
	                'allow' => true,
	                'permissions' => ['updateParticipants'],
	                'actions' => ['update', 'disable-device', 'enable-device'],
                ],
                
                [
	                'allow' => true,
	                'permissions' => ['dropParticipants'],
	                'actions' => ['drop'],
                ],
                
                [
	                'allow' => true,
	                'permissions' => ['flagParticipants'],
	                'actions' => ['flag', 'unflag'],
                ],
                
                [
	                'allow' => true,
	                'permissions' => ['hideParticipants'],
	                'actions' => ['hide'],
                ]
            ]
        ];
        
        
        
        return $behaviors;
    }

    /**
     * Lists all Participant models.
     * @return mixed
     */
    public function actionIndex()
    {
		$search = new ParticipantSearch();
		$search->study_id = $this->getStudyId();
		$dataProvider = $search->search(Yii::$app->request->queryParams, Yii::$app->user->getId());
		
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $search,
        ]);
    }

    /**
     * Displays a single Participant model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
		$participant = $this->findModel($id);
		
		// Check and see if the staff member just added a Note. Process that and reload the page.
		
		$noteModel = new NoteForm();
		$noteModel->participant_id = $id;
		
		if(Yii::$app->user->can('noteParticipants') && Yii::$app->request->isPost && $noteModel->load(Yii::$app->request->post()) && $noteModel->validate())
		{
			$note = $noteModel->addNote(Yii::$app->user->getIdentity()->id);
			if(!$note->hasErrors())
			{
				return $this->redirect(["/participant/view", "id" => $id]);
			}
		}
		else if($noteModel->hasErrors('updated_at'))
		{
			$error = implode(" ", $noteModel->getErrors('updated_at'));
			$noteModel->clearErrors('updated_at');
	        Yii::$app->session->setFlash('warning', $error);
		}
		
		// We need to make sure that we've got relatively recent adherence data for the user, so 
		// update if we need to.
		Yii::$app->participantMetadataHandler->maybeUpdateAdherenceMetadata($participant->id);
		
		// Retrieve notes, alerts, adherence rates, test session info.
		
		$notes = ParticipantNote::getNotes($participant->id);

		$alerts = Alert::getAlerts(["study_id" => $this->getStudyId(), "user_id" => Yii::$app->user->getIdentity()->id, "participant_id" => $participant->id, "limit" => 5]);
		
		$first_test = \common\models\ParticipantTestSession::getFirstTest($participant->id);
		$final_test = \common\models\ParticipantTestSession::getFinalTest($participant->id);
		$latest_finished_test = \common\models\ParticipantTestSession::getLatestCompletedTest($participant->id);

		$first_test_date = $first_test->session_date ?? time();
		
		$scheduleInfo = Yii::$app->studyDefinitions->getTodaysStudySection($first_test_date);
		if($scheduleInfo == null)
		{
			$scheduleInfo = Yii::$app->studyDefinitions->noTestingSection($first_test_date);
		}		
		
		$adherenceRates = Yii::$app->participantMetadataHandler->getAdherence($participant->id);
		$overallAdherence = isset($adherenceRates["all"]) ? intval($adherenceRates["all"]) : 0;
		unset($adherenceRates["all"]);
		
		$device = $participant->getActiveDevice() ?? $participant->getMostRecentDevice();
		
		$query = new Query();
        $columns = [
            "participant_device.updated_at",
            "participant_device.device_id",
            "participant_device.os_version",
			"participant_device.app_version"
        ];

		$query->select($columns)->from('participant_device')
		->where(['participant' => $id]);
		
        $dataProvider = new ActiveDataProvider([
			'query' => $query
        ]);

		$data = [
            'participant' => $participant,
            'first_test' => $first_test,
            'final_test' => $final_test,
			'latest_finished_test' => $latest_finished_test,
            'alerts' => $alerts,
            'noteModel' => $noteModel,
            'notes' => $notes,
            'scheduleInfo' => $scheduleInfo,
            'overallAdherence' => $overallAdherence,
            'adherenceRates' => $adherenceRates,
			'device' => $device,
			'dataProvider' => $dataProvider
        ];
        
        // If the participant has been dropped, then we need to get the ParticipantDroppedBy value
        if($participant->enabled == 0)
		{
			$droppedRecord = \common\models\ParticipantDroppedBy::getDroppedRecord($participant->id);
			$data['droppedRecord'] = $droppedRecord;
		}
        $data["completed"] = Yii::$app->participantMetadataHandler->hasParticipantCompletedStudy($participant->id);
        
        return $this->render('view', $data);
    }

    /**
     * Creates a new Participant model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ParticipantForm();
		$model->study = $this->getStudyId();
		
        if ($model->load(Yii::$app->request->post())) {
	        $participant = $model->createParticipant();
	        
	        if($participant != null)
	        {
            	return $this->redirect(['view', 'id' => $participant->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    
    public function actionFlag($id)
    {
	    $user_id = Yii::$app->user->getId();
	    ParticipantUserFlag::flag($id, $user_id);
	    
	    return $this->redirect(Yii::$app->request->referrer ?? ['view', 'id' => $id]);
    }
    
    public function actionUnflag($id)
    {
	    $user_id = Yii::$app->user->getId();	    
	    ParticipantUserFlag::unflag($id, $user_id);
	    return $this->redirect(Yii::$app->request->referrer ?? ['view', 'id' => $id]);
    }
    
    public function actionDrop($id)
    {
	    $participant = $this->findModel($id);
	    $model = new ParticipantDropForm();		
		if($model->load(Yii::$app->request->post()) && $model->validate())
		{
			$model->drop();
			return $this->renderPartial('sections/study_drop_success', ['participant' => $participant]);
		}
		
		return $this->renderPartial('sections/study_drop_form', ['participant' => $participant, 'model' => $model]);
	    
    }
    
    public function actionHide($id)
    {
	    $participant = $this->findModel($id);
	    $model = new ParticipantDropForm();		
		if($model->load(Yii::$app->request->post()) && $model->validate())
		{
			$model->hide();
			return $this->renderPartial('sections/study_remove_success', ['participant' => $participant]);
		}
		
		return $this->renderPartial('sections/study_remove_form', ['participant' => $participant, 'model' => $model]);
    }
    
    public function actionDisableDevice($id)
    {
	    $model = new DeviceToggleForm();

	    if($model->load(Yii::$app->request->post()) && $model->validate())
	    {
		    $model->disable();
	    }
	    else
	    {
		    $error = implode(" ", $model->getErrorSummary(false));
            Yii::$app->session->setFlash('warning', $error);    
            return $this->renderPartial('sections/device_disable_modal');
        }
        return $this->redirect(Yii::$app->request->referrer ?? ['view', 'id' => $id]);
    }
    
    public function actionEnableDevice($id)
    {
	     $model = new DeviceToggleForm();

	    if($model->load(Yii::$app->request->post()) && $model->validate())
	    {
		    $model->enable();
	    }
	    else
	    {
		    $error = implode(" ", $model->getErrorSummary(false));
            Yii::$app->session->setFlash('warning', $error); 
            return $this->renderPartial('sections/device_disable_modal');
        }
        return $this->redirect(Yii::$app->request->referrer ?? ['view', 'id' => $id]);
    }

    /**
     * Finds the Participant model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Participant the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
	    $model = Participant::find()
	    ->where(['id' => $id])
	    ->andWhere(['study_id' => $this->getStudyId()])
	    ->andWhere('hidden = 0')
	    ->one();
	    
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
