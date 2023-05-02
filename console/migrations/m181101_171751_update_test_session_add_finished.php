<?php

use yii\db\Migration;

/**
 * Class m181101_171751_update_test_session_add_finished
 */
class m181101_171751_update_test_session_add_finished extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn("participant_test_session", "completed", $this->boolean());
		
		$this->execute("UPDATE participant_test_session SET completed=1 WHERE start_date IS NOT NULL");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn("participant_test_session", "completed");

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181101_171751_update_test_session_add_finished cannot be reverted.\n";

        return false;
    }
    */
}
