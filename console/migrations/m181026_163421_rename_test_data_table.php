<?php

use yii\db\Migration;

/**
 * Class m181026_163421_rename_test_data_table
 */
class m181026_163421_rename_test_data_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->renameTable('test_data', 'test_session_data');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->renameTable('test_session_data', 'test_data');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181026_163421_rename_test_data_table cannot be reverted.\n";

        return false;
    }
    */
}
