<?php

use yii\db\Migration;
use common\models\StudyUserAuth;
/**
 * Class m181008_213239_update_roles_add_study_rule
 */
class m181008_213239_update_roles_add_study_rule extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$auth = Yii::$app->authManager;
		
		$rule = new \common\rules\StudyRule;
		$auth->add($rule);
		
		$roles = $auth->getRoles();
		
		foreach($roles as $role)
		{
			if($role->name == "siteAdmin")
			{
				continue;
			}
			
			$role->ruleName = $rule->name;
			$auth->update($role->name, $role);
		}

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        
        $thingsToRemove = [];
		$thingsToRemove []= $auth->getRule('CanViewStudy');
				
		foreach($thingsToRemove as $t)
		{
			$auth->remove($t);			
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
        echo "m181008_213239_update_roles_add_study_rule cannot be reverted.\n";

        return false;
    }
    */
}
