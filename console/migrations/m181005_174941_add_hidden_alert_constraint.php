<?php

use yii\db\Migration;

/**
 * Class m181005_174941_add_hidden_alert_constraint
 */
class m181005_174941_add_hidden_alert_constraint extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->createIndex('alert_id_user_id', 'hidden_alert', 'alert_id, user_id', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('alert_id_user_id', 'hidden_alert');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181005_174941_add_hidden_alert_constraint cannot be reverted.\n";

        return false;
    }
    */
}
