<?php

use yii\db\Migration;

/**
 * Class m181203_205420_update_metadata_table_indexes
 */
class m181203_205420_update_metadata_table_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->execute("ALTER TABLE participant_adherence DROP INDEX `participant`, ADD UNIQUE KEY `participant` (`participant`, `test_type`, `study_section`)");
		
		$this->execute("ALTER TABLE participant_test_finished_count DROP INDEX `participant`, ADD UNIQUE KEY `participant` (`participant`, `test_type`, `study_section`)");
		
		$this->execute("ALTER TABLE participant_test_missed_count DROP INDEX `participant`, ADD UNIQUE KEY `participant` (`participant`, `test_type`, `study_section`)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->execute("ALTER TABLE participant_adherence DROP INDEX `participant`, ADD UNIQUE KEY `participant` (`participant`, `test_type`)");
		
		$this->execute("ALTER TABLE participant_test_finished_count DROP INDEX `participant`, ADD UNIQUE KEY `participant` (`participant`, `test_type`)");
		
		$this->execute("ALTER TABLE participant_test_missed_count DROP INDEX `participant`, ADD UNIQUE KEY `participant` (`participant`, `test_type`)");

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181203_205420_update_metadata_table_indexes cannot be reverted.\n";

        return false;
    }
    */
}
