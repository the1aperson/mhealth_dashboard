<?php

namespace common\components;
use yii;
use yii\base\Component;

use yii\db\Query;

/*

This class is meant to house some often re-used methods for modifying or querying data from RBAC.
	
*/

class RBACHelper extends Component
{
	
	// Finds all roles with $oldPermissionName permission, and grants them $newPermissionName.
	// Optionally creates a new permission for $newPermissionName if none exists
	
	public static function extendNewPermission($oldPermissionName, $newPermissionName, $newDescription = null)
	{
		$auth = Yii::$app->authManager;
		$newPermission = $auth->getPermission($newPermissionName);
		
		if($newPermission == null)
		{
			$newPermission = $auth->createPermission($newPermissionName);
			$newPermission->description = $newDescription ?? $newPermissionName;
			$auth->add($newPermission);
		}
		
		$roles = RBACHelper::getRolesWithPermission($oldPermissionName);
		
		foreach($roles as $roleName => $role)
		{
			if($auth->hasChild($role, $newPermission) == false)
			{
				$auth->addchild($role, $newPermission);						
			}
		}
	}
	
	public static function addToAvailablePermissions($permissionName)
	{
		
	}
	
	
	//! Querying RBAC
	
	// Returns an associative array of all roles that have been assigned the given permission name
	
	public static function getRolesWithPermission($permissionName)
	{
		$auth = Yii::$app->authManager;
		
		$parentSubQuery = (new Query())->select('parent')->from('auth_item_child')->where(['child' => $permissionName]);
		
		$roleNames = (new Query())->select('name')->from('auth_item')->where(['type' => 1])->andWhere(['name' => $parentSubQuery])->column();		
		
		$roles = [];
		
		foreach($roleNames as $name)
		{
			$role = $auth->getRole($name);
			if($role != null)
			{
				$roles[$name] = $role;
			}
		}
		
		return $roles;
	}
}
	
?>