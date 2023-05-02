<?php
namespace backend\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\models\LoginForm;
use backend\models\PasswordResetRequestForm;
use backend\models\ResetPasswordForm;
use backend\models\PermissionForm;
use backend\components\ExportCreator;

use common\models\StudyMetadata;
use common\models\ExportQueue;


/**
 * Site controller
 */
class SiteController extends AuditableController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
	        'study' => [
	            'class' => \backend\filters\StudyFilter::className(),
	            'except' => ['login', 'logout', 'error','reset-password','request-password-reset', 'select-study', 'update-available-permissions', 'privacy-policy'],
            ],
            
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error','reset-password','request-password-reset', 'privacy-policy'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'select-study', 'export-data', 'download-export', 'view-export'],
                        'allow' => true,
                        'roles' => ['@',],
                    ],
                    [
	                    'actions' => ['index'],
	                    'allow' => true,
	                    'permissions' => ['viewParticipants'],
                    ],
					[
						'roles' => ['siteAdmin'],
						'actions' => ['update-available-permissions'],
						'allow' => true,
					]
                ],
            ],
            
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {	    
	    $study_id = $this->getStudyId();
	    $user = Yii::$app->user->getIdentity();
	    
	    $studyMetadata = Yii::$app->studyMetadataHandler->getMetadataForStudy($study_id);
	    
	    
	    // Get counts for all of the alerts
	    $alertCounts = \common\models\Alert::getAlertCounts($user->id, $study_id);
	    
	    
	    $total_participants = $studyMetadata[StudyMetadata::TOTAL_PARTICIPANTS]->value ?? 0;
	    
	    // Setup the metadata for the Study Health section
	    $med_metadata_items = [
		    ['metadata' => $studyMetadata[StudyMetadata::RETENTION_PERCENT],
	    	'tooltip_message' => "<b>Retention</b> is the percentage of participants who have enrolled in the study and either maintained involvement or completed the study."],
	    	['metadata' => $studyMetadata[StudyMetadata::ADHERENCE_PERCENT],
	    	"tooltip_message" => "<b>Adherence</b> is the rate at which participants complete the study tasks."], 
	    	
	    ];
	    
	    $small_metadata_items = [
	    	['metadata' => $studyMetadata[StudyMetadata::ENABLED_DEVICES]], 
	    	['metadata' => $studyMetadata[StudyMetadata::MISSED_TEST_COUNT]], 
	    	['metadata' => $studyMetadata[StudyMetadata::NOT_SEEN_COUNT],
	    	'labelClass' => 'text-error-red']
	    ];
	    	    
	    // Setup metadata for Enrollment Snapshot
	    
	    $newly_enrolled = $studyMetadata[StudyMetadata::RECENTLY_INSTALLED_COUNT]->value ?? 0;
	    $total_completed = $studyMetadata[StudyMetadata::COMPLETED_STUDY_COUNT]->value ?? 0;
	    $dropped_count = $studyMetadata[StudyMetadata::DROPPED_COUNT]->value ?? 0;
	    $phase_counts = json_decode($studyMetadata[StudyMetadata::CURRENT_PHASE_COUNT]->value ?? "[]", true);
	    $testing_counts = json_decode($studyMetadata[StudyMetadata::TESTING_COUNT]->value ?? "[]", true); 
	    
	    $data = [];
	    $data['alertCounts'] = $alertCounts;
	    $data['studyMetadata'] = $studyMetadata;
	    
	    $data['study_health'] = [
		    'total_participants' => $total_participants,
		    'med_metadata_items' => $med_metadata_items,
		    'small_metadata_items' => $small_metadata_items,
	    ];
	    	    
	    $data['enrollment_snapshot'] = [
		    'newly_enrolled' => $newly_enrolled,
		    'total_completed' => $total_completed,
		    'phase_counts' => $phase_counts,
		    'dropped_count' => $dropped_count,
		    'testing_counts' => $testing_counts,
		    'total_active' => $studyMetadata[StudyMetadata::TOTAL_ACTIVE_PARTICIPANTS]->value ?? 0,
	    ];
	    	    
        return $this->render('index', $data);
    }
    
    public function actionSelectStudy()
    {
	    $user = Yii::$app->user->getIdentity();
	    
		if($user->isSiteAdmin())
		{
			return $this->redirect("/staff");
		}
		
	    $study_id = Yii::$app->request->get("study_id");
	    
	    $studies = $user->getAccessibleStudies();
	    
	    if($study_id != null && array_key_exists($study_id, $studies))
	    {
		    Yii::$app->session->set('study_id', $study_id);
		    
		    $goTo = Yii::$app->session->get('select-study-return-url', Url::home(true));
		     Yii::$app->session->remove('select-study-return-url');
		    return $this->redirect($goTo);
	    }
	    	    
	    return $this->render("select_study", ["studies" => $studies]);
    }

    /**
     * Privacy Policy
     */
    public function actionPrivacyPolicy()
    {
        if (!Yii::$app->user->isGuest) {
            $this->layout = "main";
        } else {
            $this->layout = "sparse";
        }

        return $this->render("privacy_policy");
    }


    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
	    $this->layout = "sparse";
	    
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        $model->accept_privacy = isset($_COOKIE['arc-cookies']);
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
	        
	        $user = Yii::$app->user->getIdentity();
	        if($user->requires_password_reset)
	        {
		        $user->generatePasswordResetToken();
		        $user->save();
		        return $this->redirect(["/reset-password", "token" => $user->password_reset_token]);
	        }
	        else
	        {
	            return $this->goBack();		        
	        }
	        
        }
        else
        {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
    
    
        /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
	    $this->layout = "sparse";
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {

	    $this->layout = "sparse";
        try {
            $model = new ResetPasswordForm($token);
        } catch (yii\base\InvalidParamException $e) {
			return $this->render('reset_password_error');
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
    
    public function actionViewExport($id)
    {
	    $export = ExportQueue::findOne($id);
		if($export == null || $export->created_by != Yii::$app->user->getId())
		{
			return $this->redirect("/");
		}
		
		return $this->render('download_export', ['export' => $export]);
    }
    
    public function actionExportData($format = null, $scope = "all_tests")
    {
	    $response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
	    
	    $result = [];
	    $result["success"] = false;
	    $result["url"] = null;
	    
		$exportCreator = new ExportCreator();
		$exportQueue = $exportCreator->createExport($format, $scope, Yii::$app->request->queryParams);
		if($exportQueue !== false)
		{
			$result["success"] = true;
			$result["url"] = Url::to(["/view-export", "id" => $exportQueue->id], true);
		}
		else
		{
			if(empty($exportCreator->error_msg) == false)
			{
				$result["error_msg"] = $exportCreator->error_msg;
			}
			else
			{
				$result["error_msg"] = "An error has occurred while creating your export.";
			}
		}
		
		return $result;
    }
    
    public function actionDownloadExport($id)
    {
	   $this->layout = false;
	   
	   $export = ExportQueue::findOne($id);
	   
	   if($export == null || $export->filepath == null || file_exists($export->filepath) == false)
	   {
		   $this->$app->response->statusCode = 404;
		   return;
	   }
	   
	   
	   $filename = pathinfo($export->filepath, PATHINFO_BASENAME);
	   
	   return Yii::$app->response->sendFile($export->filepath, $filename);
    }
    
    // This is a siteAdmin-only action, that allows us to more easily add or remove permissions
    // from the system. 
    
    public function actionUpdateAvailablePermissions()
    {
    
	    $model = new PermissionForm();
	    $model->preload();
	    if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) 
	    {
            Yii::$app->session->setFlash('success', 'Permissions updated.');
		    return $this->redirect("/update-available-permissions");
		}
		
		return $this->render("update_permissions", ["model" => $model]);
	    
    }
}
