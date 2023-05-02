<?php

namespace common\rules;

use yii;
use yii\rbac\Rule;
use yii\db\Query;
use common\models\StudyUserAuth;

class StudyRule extends Rule
{
    public $name = 'CanViewStudy';

	
	/* 
	Checks to make sure the given user has permission for the given role/permission
	for current Study.	
	The way the default RBAC is setup makes it kind of complicated to incorporate this
	relationship more directly.
	*/
	
    public function execute($user, $item, $params)
    {
        if(isset($params['skip']) && $params['skip'] == true)
        {
            return true;
        }        

	    $study_id = isset($params["study_id"]) ? $params["study_id"] : Yii::$app->session->get('study_id');
	    if($study_id == null)
	    {
		    return false;
	    }
	    
        $query = (new Query())->select("id")->from("study_user_auth")->where(['user_id' => $user, 'auth_item_name' => $item->name]);
        $query->andWhere(['or', ['study_id' => StudyUserAuth::AUTH_ALL_STUDIES_ID], ['study_id' => $study_id] ]);
        $count = $query->count();
        return $count > 0;
    }
}

	
?>