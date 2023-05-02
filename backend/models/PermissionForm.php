<?php
namespace backend\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use common\models\AuthAvailablePermissions;

class PermissionForm extends Model
{
    public $permission_names = [];
    

    /**
     * {@inheritdoc}
     */
    public function rules()
    {	   
	    $available_permissions = Yii::$app->params['staff_permission_settings']['all_permissions'] ?? [];
        return [
            ['permission_names', 'each', 'rule' => ['string']],
            ['permission_names', 'each', 'rule' => ['in', 'range' => $available_permissions]],
            ['permission_names', 'required', 'message' => ' Please select at least one permission'],
        ];
    }
        
    public function preload()
    {
	    $this->permission_names = AuthAvailablePermissions::getAvailablePermissionNames();
    }

	public function save()
	{
		$auth = Yii::$app->authManager;
				
		$existingNames = AuthAvailablePermissions::getAvailablePermissionNames();

		$roles = $auth->getRoles();
		// delete permissions that have been de-selected
		foreach($existingNames as $p)
		{
			if(in_array($p, $this->permission_names) == false)
			{
				AuthAvailablePermissions::removePermission($p);
				$permission = $auth->getPermission($p);
				foreach($roles as $role)
				{
					$auth->removeChild($role, $permission);
				}
			}
		}
		
		// And now let's check to see if we need to add any new permissions.
		// If a permission was added to the permissionSettings.php file, but not added to 
		// the database through a migration, we might end up with a situation where it's not actually
		// available.
		
		foreach($this->permission_names as $newPermission)
		{
			if(in_array($newPermission, $existingNames) == false)
			{
				$permission = $auth->getPermission($newPermission);
				if($permission == null)
				{
					$permission = $auth->createPermission($newPermission);
					$permission->description = Yii::$app->params['staff_permission_settings']['permission_descriptions'][$newPermission] ?? "";
					$auth->add($permission);
				}
				AuthAvailablePermissions::addPermission($newPermission);
			}
		}
	}
	
	public function getPermissionList()
	{
		$auth = Yii::$app->authManager;
		$roles = $auth->getRoles();
		
		$users = [];
		
		foreach($roles as $r => $role)
		{
			$users[$r] = count($auth->getUserIdsByRole($r));
		}
		
		$permission_descriptions = Yii::$app->params['staff_permission_settings']["permission_descriptions"];
		
		foreach($permission_descriptions as $name => $desc)
		{
			$permission = $auth->getPermission($name);
			if($permission == null)
			{
				continue;
			}
			$roleCount = 0;
			$userCount = 0;
			
			foreach($roles as $r => $role)
			{
				if($auth->hasChild($role, $permission))
				{
					$roleCount += 1;
					$userCount += $users[$r];
				}
			}
			
			$permission_descriptions[$name] = $desc . "&nbsp;&nbsp;(Roles: $roleCount, Users: $userCount)";
		}
		
		return $permission_descriptions;
	}
    
}
