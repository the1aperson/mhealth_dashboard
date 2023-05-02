<?php

use yii\db\Migration;

/**
 * Class m181025_213816_update_test_data_remove_type
 */
class m181025_213816_update_test_data_remove_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->dropColumn('test_data', 'test_type');
		$this->renameColumn('export_queue', 'test_data_ids', 'test_session_ids');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('test_data', 'test_type', $this->string());
		$this->renameColumn('export_queue', 'test_session_ids', 'test_data_ids');
		return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181025_213816_update_test_data_remove_type cannot be reverted.\n";

        return false;
    }
    */
}
