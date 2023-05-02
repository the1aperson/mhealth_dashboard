<?php

use yii\db\Migration;

/**
 * Class m180913_171930_add_user_roles
 */
class m180913_171930_add_user_roles extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$auth = Yii::$app->authManager;
		
		if(isset(Yii::$app->params['staff_permission_settings']) == false)
		{
			throw new \Exception("Staff Permissions have not been setup in common/config/permissionSettings.php");
		}
		$permission_settings = Yii::$app->params['staff_permission_settings'];
		$available_permissions = $permission_settings["all_permissions"];
		$permission_descriptions = $permission_settings["permission_descriptions"];
		$admin_permissions = $permission_settings["admin_permissions"];
		
		// Check each item in available_permissions to make sure we have permissions created.
		
		foreach($available_permissions as $permission_name)
		{
			$permission = $auth->getPermission($permission_name);
			if($permission == null)
			{
				
				$permission = $auth->createPermission($permission_name);
				$permission->description = $permission_descriptions[$permission_name];
				$auth->add($permission);
			}
			else
			{
				$permission->description = $permission_descriptions[$permission_name];
				$auth->update($permission_name, $permission);
			}
		}
		
		
		// Then, let's check to make sure there aren't any existing permissions that should no longer be available.
		
		$existingPermissions = $auth->getPermissions();

		foreach($existingPermissions as $name => $existingPermission)
		{			
			if(in_array($name, $available_permissions) == false)
			{
				$auth->remove($existingPermission);
			}
		}
			
		$adminRole = $auth->getRole('siteAdmin');
		if($adminRole == null)
		{
			$adminRole = $auth->createRole('siteAdmin');
			$auth->add($adminRole);
		}
		
		$auth->removeChildren($adminRole);
		
		foreach($admin_permissions as $permission_name)
		{
			$permission = $auth->getPermission($permission_name);
			if($permission != null)
			{
				$auth->addChild($adminRole, $permission);
			}
			else
			{
				echo "Warning: permission $permission_name not found for siteAdmin role.\n";
			}
		}

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$auth = Yii::$app->authManager;
		$auth->removeAll();

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180913_171930_add_user_roles cannot be reverted.\n";

        return false;
    }
    */
}
