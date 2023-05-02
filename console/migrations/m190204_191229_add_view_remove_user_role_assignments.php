<?php

use yii\db\Migration;


/**
 * Class m190204_191229_add_view_remove_user_role_assignments
 */
class m190204_191229_add_view_remove_user_role_assignments extends Migration
{
	
	public $rules_to_create = [
		["CanViewUsers", "Can View Users", "viewUsers"],
		["CanRemoveUsers", "Can Remove Users", "removeUsers"],
	]; 
	
    /**
     * {@inheritdoc}
     */
     
    /*
	    Automatically pre-add the new permissions to any Roles that already have the old modifyUsers permission.
	    This is done in two steps, first where we add the appropriate rule/role assignments in auth_role_rule_grant,
	    and then we make the Permissions as children to the Roles.
	    
	    We also want to make sure that the siteAdmin role has these permissions as well, and that it has the AUTH_ALL_ROLES
	    grant in auth_role_rule_grant.
	*/
	
    public function safeUp()
    {
	    $auth = Yii::$app->authManager;
	    
		$existing_roles = (new \yii\db\Query())->select('*')->from('auth_role_rule_grant')->where(['assigned_rule' => 'CanAssignRole'])->all();
		
		$roles = [];
		foreach($existing_roles as $r)
		{
			$assigned_role = $r["assigned_role"];
			$granted_role = $r["granted_role"];
			$roles []= $assigned_role;
			
			foreach($this->rules_to_create as $r)
			{
				$ruleName = $r[0];
				$this->execute("INSERT INTO auth_role_rule_grant (assigned_rule, assigned_role, granted_role, created_at, updated_at) VALUES (:assigned_rule, :assigned_role, :granted_role, :created_at, :updated_at)", [":assigned_rule" => $ruleName, ":assigned_role" => $assigned_role, ":granted_role" => $granted_role, ":created_at" => time(), ":updated_at" => time()]);
			}
		}
		
		// Let's also make sure the siteAdmin has these rules granted to them.
		foreach($this->rules_to_create as $r)
		{
			$ruleName = $r[0];
			$this->execute("INSERT IGNORE INTO auth_role_rule_grant (assigned_rule, assigned_role, granted_role, created_at, updated_at) VALUES (:assigned_rule, :assigned_role, :granted_role, :created_at, :updated_at)", [":assigned_rule" => $ruleName, ":assigned_role" => "siteAdmin", ":granted_role" => "AUTH_ALL_ROLES", ":created_at" => time(), ":updated_at" => time()]);
		}
		
		$roles []= "siteAdmin";
		
		$roles = array_unique($roles);
		
		foreach($roles as $roleName)
		{
			$role = $auth->getRole($roleName);
						
			foreach($this->rules_to_create as $r)
			{
				$permission_name = $r[2];
				$permission = $auth->getPermission($permission_name);
				
				echo "Adding $permission_name to $roleName\n";
				try
				{
					$auth->addChild($role, $permission);
				}
				catch (\Exception $e)
				{
					// Whoops, it's probably already assigned.
				}
			}
		}
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		foreach($this->rules_to_create as $r)
		{
			$ruleName = $r[0];
			$this->execute("DELETE FROM auth_role_rule_grant WHERE assigned_rule = :assigned_rule", [":assigned_rule" => $ruleName]);	
		}

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190204_191229_add_view_remove_user_role_assignments cannot be reverted.\n";

        return false;
    }
    */
}
