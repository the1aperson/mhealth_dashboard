<?php

use yii\db\Migration;

/**
 * Class m181025_011149_update_export_add_type
 */
class m181025_011149_update_export_add_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn('export_queue', 'export_type', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn('export_queue', 'export_type');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181025_011149_update_export_add_type cannot be reverted.\n";

        return false;
    }
    */
}
