<?php

use yii\db\Migration;

/**
 * Class m190130_170930_update_export_queue_add_site
 */
class m190130_170930_update_export_queue_add_site extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn("export_queue", "study_id", $this->integer());
		$this->addForeignKey(
			"fk-export_queue-study",
			"export_queue",
			"study_id",
			"study",
			"id",
			"NO ACTION"
		);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
			"fk-export_queue-study",
			"export_queue"
		);
		
		$this->dropColumn("export_queue", "study_id");

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190130_170930_update_export_queue_add_site cannot be reverted.\n";

        return false;
    }
    */
}
