<?php

namespace common\rules;

use yii;
use yii\rbac\Rule;
use yii\db\Query;
use common\models\AuthItemRuleGrant;

/*
	This is a base rule for providing the ability to grant certain Roles permissions for specific items.
	
	For instance, if a Role should be allowed to create new users, but should only be allowed to give those
	users certain roles, you would attach a new Rule to that permission, and add AuthItemRuleGrant objects
	that pair the given Role with the roles they're allowed to assign.
	
*/

/*

	Accepted params:
	role_name: a role (or array of roles) to check access to
	OR
	user_id: The id for a user account to check access to (this will retrieve the roles assigned to that user, as use that as role_name)
	
	If neither of these are passed to the rule, we make the assumption that you're not trying to check for a particular role or user_id,
	so we just return true by default.
*/

class BaseGrantRule extends Rule
{
	public $name = null;
	public $description = null;
	
	// Returns a list of all existing Roles (excluding siteAdmin), and an option for AUTH_ALL_ROLES.
	
	public function grantOptions()
	{
		$roles = Yii::$app->authManager->getRoles();
		if(isset($roles["siteAdmin"]))
		{
			unset($roles["siteAdmin"]);
		}
		$role_names = array_keys($roles);
		$role_names []= AuthItemRuleGrant::AUTH_ALL_ROLES;
		return $role_names;
	}
 	
    public function execute($user, $item, $params)
    {
	    // If we have a user_id, retrieve the roles associated with that user, and use those in place of role_name.
	    
	    if(isset($params["user_id"]))
	    {
    		$user_to_check = $params["user_id"];
			$user_roles = (new Query())->select('auth_item_name')->distinct()->from('study_user_auth')->where(['user_id' => $user_to_check])->column();
			$params['role_name'] = $user_roles;
	    }
	    
	    // If we don't have a role_name, then obviously we can't check if $user has permission for it.
	    
	    if(!isset($params["role_name"]))
	    {
		    return true;
	    }
	    
	    // Get the roles assigned to the current $user.
	    
	    $userRoles = Yii::$app->authManager->getRolesByUser($user);
	    $userRoles = array_keys($userRoles);
	    
	    // If the current user is the siteAdmin, they get to do pretty much whatever they want.
	    if(in_array("siteAdmin", $userRoles))
	    {
		    return true;
	    }
	    
	    $role_to_check = $params["role_name"];
	    
		return AuthItemRuleGrant::checkGrantForRule($this->name, $userRoles, $role_to_check);
    }
    
}

	
?>