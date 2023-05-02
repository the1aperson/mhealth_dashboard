<?php

use yii\db\Migration;

/**
 * Class m181109_230820_update_alert_add_follow_up_date
 */
class m181109_230820_update_alert_add_follow_up_date extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn('alert', 'follow_up_date', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn('alert', 'follow_up_date');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181109_230820_update_alert_add_follow_up_date cannot be reverted.\n";

        return false;
    }
    */
}
