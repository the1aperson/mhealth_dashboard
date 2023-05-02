<?php

use yii\db\Migration;

/**
 * Class m180926_171133_update_test_data_add_raw_type
 */
class m180926_171133_update_test_data_add_raw_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn('test_data', 'raw_type', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn('test_data', 'raw_type');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180926_171133_update_test_data_add_raw_type cannot be reverted.\n";

        return false;
    }
    */
}
