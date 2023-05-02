<?php

use yii\db\Migration;

/**
 * Class m181001_164941_update_participant_device_add_raw_device_info_field
 */
class m181001_164941_update_participant_device_add_raw_device_info_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn("participant_device", "raw_device_info", $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn("participant_device", "raw_device_info");

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181001_164941_update_participant_device_add_raw_device_info_field cannot be reverted.\n";

        return false;
    }
    */
}
