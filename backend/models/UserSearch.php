<?php
	
namespace backend\models;

use yii;
use yii\base\Model;

use yii\data\ActiveDataProvider;

use yii\db\Query;

use common\models\User;
use common\models\StudyUserAuth;
use common\models\AuthItemRuleGrant;

class UserSearch extends Model
{
	public $name;	
	public $study;
	public $email;
	public $permissions;
	public $showAll;
	public function rules()
	{
		return [
			[['name', 'study', 'email', 'permissions', 'showAll'], 'safe'],
		];
	}
	
	public function search($params)
	{
		$this->attributes = $params;
		
		$query = new Query();
		$columns = [
			"user.id AS user_id",
			"CONCAT(user.first_name, ' ', user.last_name) AS name",
			"user.email AS email",
			"user.status AS status",
		];
		
		$query->select($columns)->from(User::tableName());
		
		// we don't want to show the default siteAdmin account, so hide any users that have the siteAdmin assignment.
		
		$adminQuery = (new Query())->select('user_id')->from('auth_assignment')->where(['item_name' => 'siteAdmin']);
		
		$query->andWhere(['not in', 'user.id', $adminQuery]);
		
		if(!isset($this->showAll) || $this->showAll != 1)
		{
			$query->andWhere(['status' => User::STATUS_ACTIVE]);
		}
		
		$query = $this->addAuthRoleCheck($query);
		
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => 10,
			],
		]);
		
		return $dataProvider;
	}
	
	public function getFilterOptions($attribute)
	{
		$values = null;
		switch($attribute)
		{
			case 'enabled':
				return [0 => "No", 1 => "Yes"];
			case 'os_type':
			case 'os_version':
			case 'app_version':
			    $values = (new yii\db\Query())->select($attribute)->distinct()->from(ParticipantDevice::tableName())->orderBy("$attribute ASC")->column();
			break;
		}
		
		if($values != null)
		{
			$filters = [];        
	        foreach($values as $value)
	        {
		        $filters[$value] = $value;
	        }
	        return $filters;
		}
		
		return [];
	}
	
	
	
	private function addAuthRoleCheck($query)
	{
		

		// We also need to limit the current user to only see users that they have access to.
		// This is done by checking what roles the current user is allowed to view
		// and what sites they're allowed to assign them in.	
		// In other words, you check study_user_auth to find what Roles have been assigned to the current user, and to which
		// Studies they've been assigned.
		// Then, for each assigned Role, check and see what roles are granted in auth_role_rule_grant
		// And THEN, we look up what other users have been assigned those Roles back in the study_user_auth table.
		// The current user is then only able to view those users.
		
		// This relationship is a little complicated, but it basically boils down to three possibilities:
		// Current User has StudyUserAuth.AUTH_ALL_STUDIES.Role => [granted roles] => visible user has StudyUserAuth.some study.Role
		// Current User has StudyUserAuth.study id.Role => [granted roles] => visible user has StudyUserAuth.study id.Role
		// Current User has StudyUserAuth.study id.Role => [granted roles] => visible user has StudyUserAuth.AUTH_ALL_ROLES.Role
		
		if(Yii::$app->user->getIdentity()->isSiteAdmin() == false)
		{
			$studyAuths = StudyUserAuth::getAssignmentsForUser(Yii::$app->user->getId());
			$studyAuthQueries = [];
			
			foreach($studyAuths as $studyAuth)
			{
				$assignRules = AuthItemRuleGrant::getGrants('CanViewUsers', $studyAuth->auth_item_name);
				
				if(count($assignRules) == 0)
				{
					continue;
				}
				
				$roles = [];
				foreach($assignRules as $rule)
				{
					$roles []= $rule->granted_role;
				}
				
				$assignQuery = (new Query())->select('study_user_auth.user_id')->from('study_user_auth');
				
				// If the user has been granted AUTH_ALL_ROLES, then we don't need to limit by the roles they've been granted
				if(in_array(AuthItemRuleGrant::AUTH_ALL_ROLES, $roles) == false)
				{
					$assignQuery->where(['study_user_auth.auth_item_name' => $roles]);
				}
				
				// If the user has been granted AUTH_ALL_STUDIES, then we don't need to limit by the study_id 
				if($studyAuth->study_id != StudyUserAuth::AUTH_ALL_STUDIES)
				{
					$assignQuery->andWhere(['or', ['study_user_auth.study_id' => $studyAuth->study_id], ['study_user_auth.study_id' => StudyUserAuth::AUTH_ALL_STUDIES]]);
				}
				$studyAuthQueries []= ['user.id' => $assignQuery];
			}
			
			
			$condition = new yii\db\conditions\OrCondition($studyAuthQueries);
			$query->andWhere($condition);
		}
		
		return $query;
	}
	
	
}