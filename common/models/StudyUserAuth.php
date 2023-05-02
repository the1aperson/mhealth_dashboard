<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Query;

/**
 * This is the model class for table "study_user_auth".
 *
 * @property int $id
 * @property int $user_id
 * @property int $study_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Study $study
 * @property User $user
 */
class StudyUserAuth extends AuditableModel
{
	public const AUTH_ALL_STUDIES_ID = null; // If this value is set as study_id, then the user has access to all studies.
	public const AUTH_ALL_STUDIES = -99; 	 // This value is used in place of null when we have to use it on views.
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'study_user_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['study_id', 'required'],
            ['auth_item_name', 'required', 'message' => 'Role cannot be blank'],
            [['user_id', 'study_id', 'created_at', 'updated_at'], 'integer'],
            ['auth_item_name', 'string'],
            ['study_id', 'filter', 'filter' => function($value){
	            if($value == StudyUserAuth::AUTH_ALL_STUDIES_ID)
	            {
		            return StudyUserAuth::AUTH_ALL_STUDIES;
	            }
	            
	            return $value;
            }],
            [['study_id'], 'exist', 'skipOnError' => true, 'targetClass' => Study::className(), 'targetAttribute' => ['study_id' => 'id'],
            	'filter' => function($query){
	            	if($this->study_id == StudyUserAuth::AUTH_ALL_STUDIES)
	            	{
		            	$query->orWhere('1');
	            	}
            	}],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }
    
    // we have to do some value swizzling to account for AUTH_ALL_STUDIES,
    // since we can't actually store an invalid study_id in the database.
    // So before we save a record, we have to convert it to null,
    // and after we retrieve a record, we should convert it to AUTH_ALL_STUDIES
    
	public function beforeSave($insert)
	{
		if($this->study_id == StudyUserAuth::AUTH_ALL_STUDIES)
		{
			$this->study_id = StudyUserAuth::AUTH_ALL_STUDIES_ID;
		}
		
	    return parent::beforeSave($insert);
	}
	
	public function afterFind()
	{
		if($this->study_id == StudyUserAuth::AUTH_ALL_STUDIES_ID)
		{
			$this->study_id = StudyUserAuth::AUTH_ALL_STUDIES;
		}
		
		parent::afterFind();
	}
	
	public function afterRefresh()
	{
		if($this->study_id == StudyUserAuth::AUTH_ALL_STUDIES_ID)
		{
			$this->study_id = StudyUserAuth::AUTH_ALL_STUDIES;
		}
		
		parent::afterFind();
	}
	
	
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'study_id' => 'Study ID',
            'auth_item_name' => 'Auth Item Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudy()
    {
        return $this->hasOne(Study::className(), ['id' => 'study_id']);
    }
    
    public function getStudyName()
    {
	    if($this->study_id == StudyUserAuth::AUTH_ALL_STUDIES_ID || $this->study_id == StudyUserAuth::AUTH_ALL_STUDIES)
	    {
		    return "All Studies";
	    }
	    
	    return $this->getStudy()->one()->name;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    public static function makeAssignment($study_id, $user_id, $auth_item_name)
    {
	    $existingSua = StudyUserAuth::find()->where(['study_id' => $study_id, 'user_id' => $user_id, 'auth_item_name' => $auth_item_name])->one();
	    
	    if($existingSua != null)
	    {
		    return $existingSua;
	    }
	    
	    $sua = new StudyUserAuth();
	    
	    $sua->study_id = $study_id;
	    $sua->user_id = $user_id;
	    $sua->auth_item_name = $auth_item_name;
	    
	    if($sua->save())
	    {
		    return $sua;
	    }
	    
	    return false;   
    }
    
    public static function removeAssignmentsForUser($user_id)
    {
	    return StudyUserAuth::deleteAll(['user_id' => $user_id]);
    }
    
    public static function getAssignmentsForUser($user_id)
    {
	    return StudyUserAuth::find()->where(['user_id' => $user_id])->all();
    }
    
    public static function getAssignmentsForStudy($study_id)
    {
	    return StudyUserAuth::find()->where(['study_id' => $study_id])->all();
    }
    
    // This method checks the user's permissions, and finds what studies the user has been assigned to.
    // If they've been assigned to AUTH_ALL_STUDIES_ID for the given $auth_item_name, then it returns all of the Studies.
    
    public static function getAvailableStudiesForUserAndAuth($user_id, $auth_item_name, $include_all_studies_placeholder = false)
    {
	    // This query gets the roles that are available to the current user that have $auth_item_name as a permission,
	    // And then gets the studies assigned to that user under the given role.
	    
	    // SELECT `study_id` FROM `study_user_auth` WHERE (`user_id`= $user_id) AND (`auth_item_name` in (SELECT parent FROM auth_item_child WHERE child = $auth_item_name AND parent IN (select item_name FROM auth_assignment WHERE user_id = $user_id )))
	    
	    $authParentQuery = (new Query())->select('item_name')->from('auth_assignment')->where(['user_id' => $user_id]);
	    $authQuery = (new Query())->select('parent')->from('auth_item_child')->where(['child' => $auth_item_name])->andWhere(['parent' => $authParentQuery]);
	    $studyIds = (new Query())->select('study_id')->from(StudyUserAuth::tableName())->where(['user_id' => $user_id, 'auth_item_name' => $authQuery])->distinct()->column();
	    
	    // If the user has AUTH_ALL_STUDIES set as a study id, then they have access to all of the studies.
	    
	    if(in_array(StudyUserAuth::AUTH_ALL_STUDIES_ID, $studyIds))
	    {
		    $studiesList = Study::find()->all();
		    
		    if($include_all_studies_placeholder)
		    {
			    $allStudies = new Study();
			    $allStudies->id = StudyUserAuth::AUTH_ALL_STUDIES;
			    $allStudies->name = "All Studies";
			    array_unshift($studiesList, $allStudies);
			}
		    return $studiesList;
	    }
	    else
	    {
		    return Study::find()->where(['id' => $studyIds])->all();
	    }
    }
}
