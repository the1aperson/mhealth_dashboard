<?php

namespace backend\controllers;

use Yii;
use common\models\Study;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * StudyController implements the CRUD actions for Study model.
 */
class StudyController extends Controller
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
                'reactivate' => ['POST']
            ],
        ];
        
		$behaviors['access'] = [
            'class' => \yii\filters\AccessControl::className(),
            'except' => ['create'],
            'rules' => [
                [
                    'allow' => true,
                    'permissions' => ['modifyStudies'],
                ],
            ]
        ];

        $behaviors[''] = [
            'class' => \yii\filters\AccessControl::className(),
            'only' => ['create'],
            'rules' => [
                [
                    'allow' => true,
                    'permissions' => ['modifyStudies'],
                    'roleParams' => ['skip' => true],
                ],
            ]
        ];
        
        return $behaviors;
    }

    /**
     * Lists all Study models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Study::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Study model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $study = $this->findModel($id);
        if($study->status == Study::STATUS_ACTIVE){
            Yii::$app->session->set('study_id', $id);
        }
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Study model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Study();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Study model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Study model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $study = $this->findModel($id);
        $study->delete();
        Yii::$app->session->setFlash('success', 'Study ' . $study->name . ' is made inactive.');

        return $this->redirect(['index']);
    }

    public function actionReactivate($id)
    {
	    $study = $this->findModel($id);
	    $study->undelete();
        Yii::$app->session->set('study_id', $id);

        return $this->redirect(['index']);
    }
    /**
     * Finds the Study model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Study the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Study::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
