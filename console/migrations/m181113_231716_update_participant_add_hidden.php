<?php

use yii\db\Migration;

/**
 * Class m181113_231716_update_participant_add_removed
 */
class m181113_231716_update_participant_add_hidden extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn('participant', 'hidden', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn('participant', 'hidden');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181113_231716_update_participant_add_removed cannot be reverted.\n";

        return false;
    }
    */
}
