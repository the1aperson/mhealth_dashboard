<?php

use yii\db\Migration;

/**
 * Class m190204_175923_add_more_role_rules
 */
class m190204_175923_add_more_role_rules extends Migration
{
    /**
     * {@inheritdoc}
     */
     
	public $rules_to_create = [
		["CanViewUsers", "Can View Users", "viewUsers"],
		["CanRemoveUsers", "Can Remove Users", "removeUsers"],
	]; 
     
    /*
	    New permissions have been added, and we want to be able to limit what a user can do
	    based on what roles they've been granted. So we're creating a couple new Rules, based
	    on common\rules\BaseGrantRule.
	*/
	
    public function safeUp()
    {
	    $auth = Yii::$app->authManager;
	    
	    // First, let's create the new rules, and attach them to their relevant permissions
	    
		foreach($this->rules_to_create as $r)
		{
			$name = $r[0];
			$desc = $r[1];
			$perm = $r[2];
			
			$rule = new \common\rules\BaseGrantRule;
			$rule->name = $name;
			$rule->description = $desc;
			$auth->add($rule);
			$permission = $auth->getPermission($perm);
			$permission->ruleName = $rule->name;
			$auth->update($permission->name, $permission);
		}
		
		// Then, we need to make sure we have a "assignRole" child permission for modifyUser
		// Since this is an update to a previous migration, and since the foreign key constraints on the auth_item table are a little wonky,
		// we need to be careful in how we update these.
		
		$assignRule = $auth->getRule('CanAssignRole');
		$assignRole = $auth->getPermission('assignRole');
		
		if($assignRole != null)
		{
			$assignRole->ruleName = null;
			$auth->update($assignRole->name, $assignRole);
		}
		
		if($assignRule != null)
		{
			$assignRule = new \common\rules\BaseGrantRule;
			$assignRule->name = "CanAssignRole";
			$assignRule->description = "Can Assign Role";
			$auth->update($assignRule->name, $assignRule);
		}
		else
		{
			$assignRule = new \common\rules\BaseGrantRule;
			$assignRule->name = "CanAssignRole";
			$assignRule->description = "Can Assign Role";
			$auth->add($assignRule);
		}
		
		if($assignRole == null)
		{
			$assignRole = $auth->createPermission("assignRole");
			$auth->add($assignRole);
		}
		
		$assignRole->ruleName = $assignRule->name;
		$auth->update($assignRole->name, $assignRole);
		
		$modifyUsers = $auth->getPermission("modifyUsers");
		if($auth->canAddChild($modifyUsers, $assignRole))
		{
			$existingChildren = $auth->getChildren("modifyUsers");
			$addChild = true;
			foreach($existingChildren as $child)
			{
				if($child->name == $assignRole->name)
				{
					$addChild = false;
					break;
				}
			}
			if($addChild)
			{
				$auth->addChild($modifyUsers, $assignRole);
			}
		}
		
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
	    
	    $auth = Yii::$app->authManager;
	    
		foreach($this->rules_to_create as $r)
		{
			$rule = $auth->getRule($r[0]);
			if($rule != null)
			{
				$auth->remove($rule);
			}
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
        echo "m190204_175923_add_more_role_rules cannot be reverted.\n";

        return false;
    }
    */
}
