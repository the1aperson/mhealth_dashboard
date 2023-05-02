<?php

use yii\db\Migration;

/**
 * Class m181001_202500_update_user_table_add_names
 */
class m181001_202500_update_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn("user", "first_name", $this->string());
		$this->addColumn("user", "last_name", $this->string());
		$this->addColumn("user", "requires_password_reset", $this->boolean());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn("user", "first_name");
		$this->dropColumn("user", "last_name");
		$this->dropColumn("user", "requires_password_reset");
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181001_202500_update_user_table_add_names cannot be reverted.\n";

        return false;
    }
    */
}
