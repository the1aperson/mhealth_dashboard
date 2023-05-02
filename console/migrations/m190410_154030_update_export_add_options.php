<?php

use yii\db\Migration;

/**
 * Class m190410_154030_update_export_add_options
 */
class m190410_154030_update_export_add_options extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn('export_queue', 'options_json', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn('export_queue', 'options_json');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190410_154030_update_export_add_options cannot be reverted.\n";

        return false;
    }
    */
}
