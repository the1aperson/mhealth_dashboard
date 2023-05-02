<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Class m181205_001021_update_sua_add_siteAdmin
 */
class m181205_001021_update_sua_add_siteAdmin extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$siteAdminIds = (new Query())->select('user_id')->from('auth_assignment')->where(['item_name' => 'siteAdmin'])->column();
		
		foreach($siteAdminIds as $id)
		{
			$this->execute('INSERT INTO study_user_auth (user_id, study_id, auth_item_name, created_at, updated_at) VALUES (:user_id, :study_id, :auth_item_name, :created_at, :updated_at)', [":user_id" => $id, "study_id" => null, "auth_item_name" => 'siteAdmin', ":created_at" => time(), ":updated_at" => time() ]);
		}
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181205_001021_update_sua_add_siteAdmin cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181205_001021_update_sua_add_siteAdmin cannot be reverted.\n";

        return false;
    }
    */
}
