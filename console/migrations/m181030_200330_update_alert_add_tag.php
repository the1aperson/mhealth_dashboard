<?php

use yii\db\Migration;

/**
 * Class m181030_200330_update_alert_add_tag
 */
class m181030_200330_update_alert_add_tag extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn('alert', 'tag', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn('alert', 'tag');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181030_200330_update_alert_add_tag cannot be reverted.\n";

        return false;
    }
    */
}
