<?php

use yii\db\Migration;

use common\models\AuthItemRuleGrant;
/**
 * Class m190124_181912_add_rule_to_modify_user
 */
class m190124_181912_add_rule_to_modify_user extends Migration
{
    /**
     * {@inheritdoc}
     */
   
	public function safeUp()
    {
		$auth = Yii::$app->authManager;
		
		// Create the RestrictRolesRule
		
		$rule = new \common\rules\BaseGrantRule;
		$rule->name = "CanAssignRole";
		$rule->description = "Can Assign Role";
		$auth->add($rule);
		
		// Create a child permission that can use it
		
		$assignRole = $auth->createPermission('assignRole');
		$assignRole->description = 'Can assign certain roles';
		$assignRole->ruleName = $rule->name;
		$auth->add($assignRole);
		
		// And add that child to modifyUsers
		
		$modifyUsers = $auth->getPermission('modifyUsers');
		$auth->addChild($modifyUsers, $assignRole);
		
		$roles = $auth->getRoles();
		
		foreach($roles as $role)
		{
			if($auth->hasChild($role, $modifyUsers))
			{
				foreach($roles as $grantedRole)
				{
					if($grantedRole->name == "siteAdmin")
					{
						continue;
					}
					AuthItemRuleGrant::createGrant($rule->name, $role->name, $grantedRole->name);
				}
			}
		}
		
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
	    $this->execute("SET FOREIGN_KEY_CHECKS=0; UPDATE auth_item SET rule_name = NULL WHERE rule_name = 'CanAssignRole';");
	    
        $auth = Yii::$app->authManager;
        $rule = new \common\rules\AssignRoleRule;
		$auth->remove($rule);
		
		$assignRole = $auth->getPermission('assignRole');
		if($assignRole != null)
		{
			$auth->remove($assignRole);
		}
		$this->execute("DELETE FROM auth_item_child WHERE child = 'assignRole';");
		$this->execute("DELETE FROM auth_role_rule_grant");

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190124_181912_add_rule_to_modify_user cannot be reverted.\n";

        return false;
    }
    */
}
