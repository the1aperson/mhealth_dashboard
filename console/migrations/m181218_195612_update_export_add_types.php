<?php

use yii\db\Migration;

/**
 * Class m181218_195612_update_export_add_types
 */
class m181218_195612_update_export_add_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->renameColumn("export_queue", "test_session_ids", "item_ids");
		$this->addColumn("export_queue", "item_type", $this->string());
		$this->execute("UPDATE export_queue SET item_type = :item_type", [":item_type" => "participant_test_session"]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181218_195612_update_export_add_types cannot be reverted.\n";
		$this->dropColumn("export_queue", "item_type");
		$this->renameColumn("export_queue", "item_ids", "test_session_ids");
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181218_195612_update_export_add_types cannot be reverted.\n";

        return false;
    }
    */
}
