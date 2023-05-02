<?php

use yii\db\Migration;

/**
 * Class m191219_210115_update_study_add_status
 */
class m191219_210115_update_study_add_status extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn("study", "status", $this->smallInteger()->notNull()->defaultValue(10));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn("study", "status");

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191219_210115_update_study_add_status cannot be reverted.\n";

        return false;
    }
    */
}
