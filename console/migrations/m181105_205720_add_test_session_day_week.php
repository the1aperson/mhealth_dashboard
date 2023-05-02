<?php

use yii\db\Migration;

/**
 * Class m181105_205720_add_test_session_day_week
 */
class m181105_205720_add_test_session_day_week extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn('participant_test_session', 'day', $this->integer());
		$this->addColumn('participant_test_session', 'week', $this->integer());
		$this->addColumn('participant_test_session', 'session', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn('participant_test_session', 'day');
		$this->dropColumn('participant_test_session', 'week');
		$this->dropColumn('participant_test_session', 'session');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181105_205720_add_test_session_day_week cannot be reverted.\n";

        return false;
    }
    */
}
