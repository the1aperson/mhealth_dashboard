<?php

use yii\db\Migration;

/**
 * Class m181024_233549_update_test_session_add_data_fk
 */
class m181024_233549_update_test_session_add_data_fk extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn('participant_test_session', 'test_data_id', $this->integer());
		$this->addForeignKey('fk-participant_test_session-test_data',
		'participant_test_session',
		'test_data_id',
		'test_data',
		'id',
		'NO ACTION');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropForeignKey('fk-participant_test_session-test_data', 'participant_test_session');
		$this->dropColumn('participant_test_session', 'test_data_id');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181024_233549_update_test_session_add_data_fk cannot be reverted.\n";

        return false;
    }
    */
}
