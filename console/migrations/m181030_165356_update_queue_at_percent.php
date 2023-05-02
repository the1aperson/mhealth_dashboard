<?php

use yii\db\Migration;

/**
 * Class m181030_165356_update_queue_at_percent
 */
class m181030_165356_update_queue_at_percent extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn('export_queue', 'progress_msg', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn('export_queue', 'progress_msg');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181030_165356_update_queue_at_percent cannot be reverted.\n";

        return false;
    }
    */
}
