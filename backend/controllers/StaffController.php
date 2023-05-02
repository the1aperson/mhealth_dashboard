<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use backend\models\UserSearch;
use backend\models\UserForm;
use backend\models\PasswordResetRequestForm;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;


/**
 * StaffController implements the CRUD actions for User model.
 */
class StaffController extends AuditableController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
	    $behaviors = parent::behaviors();
		$behaviors['verbs']= [
            'class' => VerbFilter::className(),
            'actions' => [
                'delete' => ['POST'],
            ],
        ];
        
        // Individual rules for the different actions of this controller:
        
        $behaviors[] = [
            'class' => \yii\filters\AccessControl::className(),
            'only' => ['index'],
            'rules' => [
                [
                    'allow' => true,
                    'permissions' => ['viewUsers'],
                ],            
            ]
        ];
        
         $behaviors[] = [
	        'class' => \yii\filters\AccessControl::className(),
            'only' => ['view'],
            'rules' => [
	            [
		            'permissions' => ['viewUsers'],
		            'allow' => true,
		            'roleParams' => ['user_id' => Yii::$app->request->get('id')],
				]
            ],
        ];
        
		$behaviors[] = [
            'class' => \yii\filters\AccessControl::className(),
            'only' => ['create'],
            'rules' => [
                [
                    'allow' => true,
                    'permissions' => ['modifyUsers'],
                ],            
            ]
        ];
                       
        $behaviors[] = [
	        'class' => \yii\filters\AccessControl::className(),
            'only' => ['update', 'reactivate', 'require-password-reset'],
            'rules' => [
	            [
		            'permissions' => ['modifyUsers'],
		            'allow' => true,
		            'roleParams' => ['user_id' => Yii::$app->request->get('id')],
				]
            ],
        ];
        
		$behaviors[] = [
            'class' => \yii\filters\AccessControl::className(),
            'only' => ['delete'],
            'rules' => [
                [
                    'allow' => true,
                    'permissions' => ['removeUsers'],
                ],            
            ]
        ];
        
        // We don't want to allow anyone to mess with siteAdmin accounts.
        
        $behaviors[]= [
            'class' => \yii\filters\AccessControl::className(),
            'only' => ['view', 'update', 'delete', 'reactivate', 'require-password-reset'],
            
            'rules' => [
                [
                    'allow' => true,
					'matchCallback' => function ($rule, $action) {
						$user_id = Yii::$app->request->get('id');
						if($user_id != null)
						{
							$admin_ids = Yii::$app->authManager->getUserIdsByRole('siteAdmin');
							if(in_array($user_id, $admin_ids))
							{
								return false;
							}
						}
						return true;
					}
                ],            
            ]
        ];
        
        return $behaviors;
    }
    
    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $search = new UserSearch();
		
		$dataProvider = $search->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $search,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {    
        return $this->render('view', [
            'model' => $this->findModel($id),
            'auth_roles' => \common\models\StudyUserAuth::getAssignmentsForUser($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new UserForm();
		$model->scenario = 'create';
		
        if ($model->load(Yii::$app->request->post()) && $model->validate() )
        {
	        $user = $model->save();
	        
	        if($user != null)
	        {
		       $model->sendWelcomeEmail($user);
		        Yii::$app->session->setFlash('success', 'User created! Password reset email has been sent to ' . $model->email);
	            return $this->redirect(['view', 'id' => $user->id]);
	        }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
	    $user = $this->findModel($id);
		$model = new UserForm();
		$model->scenario = 'update';
		$model->preload($user);
			
        if ($model->load(Yii::$app->request->post()) && $model->validate() ) 
        {
	        
	        // Before we actually save, let's just make a sanity check and make sure the user isn't accidentally deleting all of their own
	        // permissions
	        
	        if($user->id == Yii::$app->user->getId() && count($model->auth_roles) == 0)
	        {
		        Yii::$app->session->setFlash('warning', 'You cannot remove all of your own roles!');
				return $this->redirect(['update', 'id' => $user->id]);
	        }
	        
	        $user = $model->save($id);
	        if($user != null)
	        {
            	return $this->redirect(['view', 'id' => $user->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
	    $currentUserId = Yii::$app->user->getId();
	    if($currentUserId == $id)
	    {
	        Yii::$app->session->setFlash('warning', 'You cannot remove yourself from the dashboard.');
		    return $this->redirect(['view', 'id' => $id]);
	    }
	    $user = $this->findModel($id);
        $user->delete();
        Yii::$app->session->setFlash('success', 'User ' . $user->displayName() . ' removed from dashboard.');
        return $this->redirect(['index']);
    }
    
    public function actionReactivate($id)
    {
	    $user = $this->findModel($id);
	    $user->undelete();

        return $this->redirect(['index']);
    }
    
    public function actionRequirePasswordReset($id)
    {
		$user = $this->findModel($id);
	    $user->markRequiresPasswordReset();
	    $user->save();
        Yii::$app->session->setFlash('success', 'User ' . $user->displayName() . ' will be asked to reset their password on their next login.');
	    return $this->redirect(['view', 'id' => $user->id]);
    }


	/*! ajax actions */
	
	public function actionRenderRoleForm()
	{
		$index = Yii::$app->request->post("index", rand(10, 99));
		$role = new \common\models\StudyUserAuth();
		$model = new UserForm();
		$form = \yii\bootstrap\ActiveForm::begin(['id' => 'form-signup', 'action' => null]);
		$studies = $model->getStudies();
		$roles = $model->getRoles();
		
		return $this->renderPartial('/staff/auth_role_form', ['model' => $role, 'index' => $index, 'form' => $form, 'roles' => $roles, 'studies' => $studies]);
	}

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
