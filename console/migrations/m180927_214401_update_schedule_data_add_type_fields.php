<?php

use yii\db\Migration;

/**
 * Class m180927_214401_update_schedule_data_add_type_fields
 */
class m180927_214401_update_schedule_data_add_type_fields extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn("schedule_data", "raw_type", $this->integer());
		$this->addColumn("schedule_data", "schedule_type", $this->string());
		$this->dropColumn("schedule_data", "test_type");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn("schedule_data", "schedule_type");
		$this->dropColumn("schedule_data", "raw_type");
		$this->addColumn("schedule_data", "test_type", $this->string());

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180927_214401_update_schedule_data_add_type_fields cannot be reverted.\n";

        return false;
    }
    */
}
