<?php

use yii\db\Migration;

/**
 * Class m181002_185752_update_participant_add_enabled
 */
class m181002_185752_update_participant_add_enabled extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn("participant", "enabled", $this->integer());
		$this->execute("UPDATE participant SET enabled = 1");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn("participant", "enabled");

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181002_185752_update_participant_add_enabled cannot be reverted.\n";

        return false;
    }
    */
}
