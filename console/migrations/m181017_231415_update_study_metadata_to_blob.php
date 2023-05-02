<?php

use yii\db\Migration;

/**
 * Class m181017_231415_update_study_metadata_to_blob
 */
class m181017_231415_update_study_metadata_to_blob extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->alterColumn('study_metadata', 'value', "MEDIUMBLOB");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->alterColumn('study_metadata', 'value', $this->string());

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181017_231415_update_study_metadata_to_blob cannot be reverted.\n";

        return false;
    }
    */
}
