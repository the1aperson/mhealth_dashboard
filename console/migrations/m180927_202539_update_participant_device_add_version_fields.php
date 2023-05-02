<?php

use yii\db\Migration;

/**
 * Class m180927_202539_update_participant_device_add_version_fields
 */
class m180927_202539_update_participant_device_add_version_fields extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn("participant_device", "app_version", $this->string());
		$this->addColumn("participant_device", "os_version", $this->string());
		$this->addColumn("participant_device", "os_type", $this->string());		
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn("participant_device", "app_version");
		$this->dropColumn("participant_device", "os_version");
		$this->dropColumn("participant_device", "os_type");
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180927_202539_update_participant_device_add_version_fields cannot be reverted.\n";

        return false;
    }
    */
}
