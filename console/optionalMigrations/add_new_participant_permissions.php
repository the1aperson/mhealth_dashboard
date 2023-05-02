<?php
namespace console\optionalMigrations;

use Yii;
use yii\db\Migration;
use yii\db\Query;

class add_new_participant_permissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$auth = Yii::$app->authManager;
		
		// First, check and see if the new permission have been added
		
		$newPermissionNames = ['dropParticipants', 'flagParticipants'];
		$newPermissions = [];
		foreach($newPermissionNames as $name)
		{
			$permission = $auth->getPermission($name);
			if($permission == null)
			{
				$permission = $auth->createPermission($name);
				$permission->description = Yii::$app->params['staff_permission_settings']['permission_descriptions'][$name];
				$auth->add($permission);
			}
			
			$newPermissions[$name] = $permission;
		}
		
		// And since the AuthManager has no easy way of getting roles by permission, we have to do it ourselves
		
		$roles = (new Query())->select('parent')->from('auth_item_child')->where(['child' => 'updateParticipants'])->column();
		
		foreach($roles as $roleName)
		{
			$role = $auth->getRole($roleName);
			if($role != null)
			{
				foreach($newPermissions as $permissionName => $permission)
				{
					if($auth->hasChild($role, $permission) == false)
					{
						echo "Adding $permissionName to $roleName\n";
						$auth->addchild($role, $permission);						
					}
					else
					{
						echo "Role $roleName already has permission $permissionName\n";
					}

				}
			}
		}
		
		
		
		
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		echo "There's nothing to undo for this migration.\n";
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190221_173655_temp_add_new_participant_permissions cannot be reverted.\n";

        return false;
    }
    */
}
