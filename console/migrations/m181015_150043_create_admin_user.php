<?php

use yii\db\Migration;

/**
 * Class m181015_150043_create_admin_user
 */
class m181015_150043_create_admin_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		echo "Creating admin user...\n";
		$password = Yii::$app->controller->prompt("password for admin:");
		$confirm = Yii::$app->controller->prompt("confirm password:");
		
		if($confirm != $password)
		{
			echo "Passwords do not match. Try again.\n";
			die;
		}
		$email = Yii::$app->controller->prompt("email for admin:"); 
        $user = new \common\models\User();
        $user->username = "admin";
        $user->email = $email;
        $user->setPassword($password);
        $user->generateAuthKey();
        $user->save();
        
        $auth = Yii::$app->authManager;
        
        $adminRole = $auth->getRole("siteAdmin");
		$auth->assign($adminRole, $user->getId());
        
        echo "Admin user created!\n";
		
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		
		$admin = User::find()->where(["username" => "admin"])->one();
		
		$admin->delete();
		
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181015_150043_create_admin_user cannot be reverted.\n";

        return false;
    }
    */
}


